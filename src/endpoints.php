<?php
require_once 'validation_rules.php'; 
require "validator.php";
require "security.php";

function resolve_endpoints($segments, $db, $pepper) {
    
    $method = $_SERVER['REQUEST_METHOD'];
    $VALIDATION_RULES = $GLOBALS['VALIDATION_RULES'];

    $userID = isset($segments[2]) && is_numeric($segments[2]) ? intval($segments[2]) : null;
    $taskID = isset($segments[4]) && is_numeric($segments[4]) ? intval($segments[4]) : null;

    $taskIDorSlug = isset($segments[4]) 
    ? (is_numeric($segments[4])
        ? '{taskID}' 
        : $segments[4]) 
    : '';

    $routeSegments = [
        $method,
        $segments[1] ?? '',
        ($userID !== null) ? '{userID}' : '',
        $segments[3] ?? '',
        $taskIDorSlug,
    ];

    $route = rtrim(implode('|', $routeSegments), '|');

    switch($route) {
        case 'PUT|users':
            user_registration($db, $pepper, $VALIDATION_RULES);
            break;
        case 'DELETE|users|{userID}':
            user_removal($db, $userID);
            break;
        case 'DELETE|users|{userID}|tasks|{taskID}':
            task_removal($db, $userID, $taskID);
            break;
        case 'POST|users|{userID}|tasks':
            task_registration($db, $userID, $VALIDATION_RULES);
            break;
        case 'POST|users|{userID}|tasks|random':
            random_task_registration($db, $userID);
            break;
        case 'POST|login':
            login($db, $pepper, $VALIDATION_RULES);
            break;
        case 'PATCH|users|{userID}':
            user_update($db, $userID, $pepper, $VALIDATION_RULES);
            break;
        case 'PATCH|users|{userID}|tasks|{taskID}':
            task_update($db, $taskID, $VALIDATION_RULES);
            break;
        case 'GET|users|{userID}|tasks':
            tasks_get($db, $userID, $VALIDATION_RULES);
            break;
        default:
            sendResponse('Invalid endpoint');
            break;
    }
}

function login($db, $pepper, $VALIDATION_RULES) {
    $data = json_decode(file_get_contents('php://input'), true);

    $username = $data['username'] ?? null;
    $password = $data['password'] ?? null;

    $emptyRequiredFields = checkRequiredFields([
        ['value' => $username, 'key' => 'Username'],
        ['value' => $password, 'key' => 'Password']
    ]);
    if(!empty($emptyRequiredFields)) sendResponse(400, ['error' => $emptyRequiredFields]);

    $user = $db->get_user_by_username($username);

    if (!$user || !isset($user['password']) || !is_string($user['password'])) 
        sendResponse('Invalid credentials');

    $pepperedPassword = hash_hmac("sha256", $password, $pepper);

    if (password_verify($pepperedPassword, $user['password']))
        sendResponse(200, ['message' => 'Login successful']);
    sendResponse('Invalid credentials');
}

function user_registration($db, $pepper, $VALIDATION_RULES) {
    $data = json_decode(file_get_contents('php://input'), true);

    $username = $data['username'] ?? null;
    $password = $data['password'] ?? null;

    $emptyRequiredFields = checkRequiredFields([
        ['value' => $username, 'key' => 'Username'],
        ['value' => $password, 'key' => 'Password']
    ]);
    if(!empty($emptyRequiredFields)) sendResponse($emptyRequiredFields);

    $usernameErrors = validate("Username", $username, $VALIDATION_RULES['username']);
    $passwordErrors = validate("Password", $password, $VALIDATION_RULES['password']);

    $errors = array_merge($usernameErrors, $passwordErrors);

    if (!empty($errors)) sendResponse($errors);
    else {
        $hashedPassword = hashPassword($password, $pepper); 
        $db -> register_user($username, $hashedPassword);
    }
}

function user_removal($db, $userID) {
    $db -> remove_user($userID);
}

function task_removal($db, $userID, $taskID) {
    $db -> remove_task($userID, $taskID);
}

function task_registration($db, $userID, $VALIDATION_RULES) {
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


    if (!empty($errors)) sendResponse($errors);
    else {
        $db -> register_task($userID, $task, $date ?? null);
    }
}

function random_task_registration($db, $userID) {
    $db -> register_random_task($userID);
}

function user_update($db, $userID, $pepper, $VALIDATION_RULES) {
    $data = json_decode(file_get_contents('php://input'), true);

    $username = $data['username'] ?? null;
    $password = $data['password'] ?? null;

    if(isset($username)) {
        $errors = validate("Username", $username, $VALIDATION_RULES['username']);
    
        if (!empty($errors)) sendResponse($errors);
        else {
            $db -> update_user_username($userID, $username);
        }
    } elseif (isset($password)) {
        $errors = validate("Password", $password, $VALIDATION_RULES['password']);
    
        if (!empty($errors)) sendResponse($errors); 
        else {
            $hashedPassword = hashPassword($password, $pepper); 
            $db -> update_user_password($userID, $hashedPassword);
        }
    }
}

function task_update($db, $taskID, $VALIDATION_RULES) {
    $data = json_decode(file_get_contents('php://input'), true);
    $finished = $data['finished'] ?? null;
    $task = $data['task'] ?? null;

    if(!isset($finished) && !isset($task))
        sendResponse('Either "finished", or "task" is required');

    $errors = [];

    if(isset($task)) {
        $taskErrors = validate("Task", $task, $VALIDATION_RULES['task']);
        $errors = array_merge($errors, $taskErrors);
    }

    if(isset($finished)) {
        $finishedErrors = validate("Finished", $finished, $VALIDATION_RULES['boolean']);
        $errors = array_merge($errors, $finishedErrors);
    }
        
    if (!empty($errors)) sendResponse($errors);
    else {
        if(isset($task)) {
            $db -> update_task_content($taskID, $task);
        }

        if(isset($finished)) {
            $db -> update_task_state($taskID, $finished);
        }
    }
}

function tasks_get($db, $userID, $VALIDATION_RULES) {
    $stateFilter = null;

    $finished = $_GET['finished'] ?? null;
    if(isset($finished)) {
        $errors = validate("Finished", $finished, $VALIDATION_RULES['boolean']);
    
        if (!empty($errors)) sendResponse($errors);
        else {
            $stateFilter = $finished === 'true' ? 1 : 0;
        }
    }
    $db -> return_tasks($userID, $stateFilter);
}
?>