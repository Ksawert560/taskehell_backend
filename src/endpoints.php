<?php
require_once 'validation_rules.php'; 
require "validator.php";
require "security.php";


function return_segments() {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = rtrim($uri, '/');

    return explode('/', $uri);
}

function resolve_endpoints($segments, $db, $pepper) {

    $VALIDATION_RULES = $GLOBALS['VALIDATION_RULES'];
    $method = $_SERVER['REQUEST_METHOD'];

    if (!isset($segments[1])) {
        http_response_code(404);
        echo json_encode(["error" => "Invalid endpoint"]);
        exit;
    }

    switch($segments[1]) {
        case 'users':
            user_resources(
                $segments,
                $db,
                $pepper,
                $VALIDATION_RULES,
                $method
            );
            break;
        case 'login':
            login($db, $pepper, $VALIDATION_RULES, $method);
            break;
        default:
            http_response_code(404);
            echo json_encode(["error" => "Invalid endpoint"]);
            exit;
            break;
    }

    
}

function login($db, $pepper, $VALIDATION_RULES, $method)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(404);
        echo json_encode(["error" => "Invalid endpoint"]);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $username = $data['username'] ?? null;
    $password = $data['password'] ?? null;

    if (!$username || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password required']);
        exit;
    }

    $user = $db->get_user_by_username($username);

    if (!$user || !isset($user['password']) || !is_string($user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    // Pepper and verify
    $pepperedPassword = hash_hmac("sha256", $password, $pepper);

    if (password_verify($pepperedPassword, $user['password'])) {
        http_response_code(200);
        echo json_encode(['message' => 'Login successful']);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
    exit;
}

function user_resources($segments, $db, $pepper, $VALIDATION_RULES, $method) {
    $userID = isset($segments[2]) ? intval($segments[2]) : null;

    switch ($method) {
        case 'PUT':
            if ($userID) break;

            $data = json_decode(file_get_contents('php://input'), true);

            $username = $data['username'];
            $password = $data['password'];

            $usernameErrors = validate("Username", $username, $VALIDATION_RULES['username']);
            $passwordErrors = validate("Password", $password, $VALIDATION_RULES['password']);

            $errors = array_merge($usernameErrors, $passwordErrors);

            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['errors' => $errors]);
                exit;
            } else {
                $hashedPassword = hashPassword($password, $pepper); 
                $db -> register_user($username, $hashedPassword);
            }

            break;

        case 'DELETE':
            if (!$userID) break;

            if (!isset($segments[3])) {
                $db -> remove_user($userID);
            } else if ($segments[3] === 'tasks' && isset($segments[4])) {
                $taskID = intval($segments[4]);
                $db -> remove_task($userID, $taskID);
            }
            break;

        case 'POST':
            if (!$userID || !isset($segments[3]) || $segments[3] !== 'tasks') break;

            if (!isset($segments[4])) {
                $data = json_decode(file_get_contents('php://input'), true);

                $task = $data['task'];
                $date = $data['due'] ?? null;

                $taskErrors = validate("Task", $task, $VALIDATION_RULES['task']);
                
                if(isset($date)) {
                    $dateErrors = validate("Date", $date, $VALIDATION_RULES['datetime']);
                    $errors = array_merge($taskErrors, $dateErrors);
                } else {
                    $errors = $taskErrors;
                }


                if (!empty($errors)) {
                    http_response_code(400);
                    echo json_encode(['errors' => $errors]);
                    exit;
                } else {
                    $db -> register_task($userID, $task, $date ?? null);
                }

            } elseif ($segments[4] === 'random') {
                $db -> register_random_task($userID);
            }
            break;

        case 'PATCH':
            if(!$userID) break;

            if(!isset($segments[3])) {
                $data = json_decode(file_get_contents('php://input'), true);

                $username = $data['username'] ?? null;
                $password = $data['password'] ?? null;

                if(isset($username)) {
                    $errors = validate("Username", $username, $VALIDATION_RULES['username']);
                
                    if (!empty($errors)) {
                        http_response_code(400);
                        echo json_encode(['errors' => $errors]);
                        exit;
                    } else {
                        $db -> update_user_username($userID, $username);
                    }
                } elseif (isset($password)) {
                    $errors = validate("Password", $password, $VALIDATION_RULES['password']);
                
                    if (!empty($errors)) {
                        http_response_code(400);
                        echo json_encode(['errors' => $errors]);
                        exit;
                    } else {
                        $hashedPassword = hashPassword($password, $pepper); 
                        $db -> update_user_password($userID, $hashedPassword);
                    }
                }
            } else if ($segments[3] === 'tasks' && isset($segments[4])) {
                $taskID = intval($segments[4]);

                $data = json_decode(file_get_contents('php://input'), true);
                $finished = $data['finished'] ?? null;

                if(isset($finished)) {
                    $errors = validate("Finished", $finished, $VALIDATION_RULES['boolean']);
                
                    if (!empty($errors)) {
                        http_response_code(400);
                        echo json_encode(['errors' => $errors]);
                        exit;
                    } else {
                        $db -> update_task($taskID, $finished);
                    }
                }
            }

            break;

        case 'GET':
            if (!$userID || !isset($segments[3]) || $segments[3] !== 'tasks') break;

            $stateFilter = null;

            $finished = $_GET['finished'] ?? null;
            if(isset($finished)) {
                $errors = validate("Finished", $finished, $VALIDATION_RULES['boolean']);
            
                if (!empty($errors)) {
                    http_response_code(400);
                    echo json_encode(['errors' => $errors]);
                    exit;
                } else {
                    $stateFilter = $finished === 'true' ? 1 : 0;
                }
            }
            $db -> return_tasks($userID, $stateFilter);

            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
    }
}
?>
