<?php

function return_segments() {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = rtrim($uri, '/');

    return explode('/', $uri);
}

function resolve_endpoints($segments, $db) {
    $method = $_SERVER['REQUEST_METHOD'];

    if (!isset($segments[1]) || $segments[1] !== 'users') {
        http_response_code(404);
        echo json_encode(["error" => "Invalid endpoint"]);
        exit;
    }

    $userID = isset($segments[2]) ? intval($segments[2]) : null;

    switch ($method) {
        case 'PUT':
            if ($userID) break;

            $data = json_decode(file_get_contents('php://input'), true);
            $db->register_user($data['username'], $data['password']);
            break;

        case 'DELETE':
            if (!$userID) break;

            if (!isset($segments[3])) {
                $db->remove_user($userID);
            } else if ($segments[3] === 'tasks' && isset($segments[4])) {
                $taskID = intval($segments[4]);
                $db->remove_task($userID, $taskID);
            }
            break;

        case 'POST':
            if (!$userID || !isset($segments[3]) || $segments[3] !== 'tasks') break;

            if (!isset($segments[4])) {
                $data = json_decode(file_get_contents('php://input'), true);
                $db->register_task($userID, $data['task'], $data['due'] ?? null);
            } elseif ($segments[4] === 'random') {
                $db->register_random_task($userID);
            }
            break;

        case 'PATCH':
            if (!$userID || $segments[3] !== 'tasks' || !isset($segments[4])) break;

            $taskID = intval($segments[4]);
            $data = json_decode(file_get_contents('php://input'), true);
            $db->update_task($taskID, $data['finished']);
            break;

        case 'GET':
            if (!$userID || !isset($segments[3]) || $segments[3] !== 'tasks') break;

            $stateFilter = null;
            if (isset($_GET['finished'])) {
                $stateFilter = $_GET['finished'] === 'true' ? 1 : 0;
            }

            $db->return_tasks($userID, $stateFilter);
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
    }
}
?>
