<?php
require_once __DIR__ . '/security/validator.php';
require_once __DIR__ . '/security/password_handling.php';
require_once __DIR__ . '/security/jwt_handling.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
$segments = explode('/', $uri);

$method = $_SERVER['REQUEST_METHOD'];
$routeSegments = [
    $method,
    $segments[1] ?? '',
    $segments[2] ?? '',
    $segments[3] ?? ''
];

$route = rtrim(implode('|', $routeSegments), '|');

$response = null;

switch ($route) {
    case 'POST|login':
        $response = require __DIR__ . '/endpoints/session/login.php';
        break;

    case 'POST|logout':
        $user_id = authenticate_jwt($db, true);
        $response = require __DIR__ . '/endpoints/session/logout.php';
        break;
    
    case 'POST|refresh':
        $user_id = authenticate_jwt($db, true);
        $response = require __DIR__ . '/endpoints/session/refresh.php';
        break;

    case 'PUT|users':
        $response = require __DIR__ . '/endpoints/users/register.php';
        break;

    case 'PATCH|users':
        $user_id = authenticate_jwt($db, false);
        $response = require __DIR__ . '/endpoints/users/update.php';
        break;

    case 'DELETE|users':
        $user_id = authenticate_jwt($db, false);
        $response = require __DIR__ . '/endpoints/users/remove.php';
        break;

    case 'PUT|lists':
        $user_id = authenticate_jwt($db, false);
        $response = require __DIR__ . '/endpoints/lists/register.php';
        break;
    case 'GET|lists':
        $user_id = authenticate_jwt($db, false);
        $response = require __DIR__ . '/endpoints/lists/return.php';
        break;

    case 'DELETE|lists':
        $user_id = authenticate_jwt($db, false);
        $response = require __DIR__ . '/endpoints/lists/remove.php';
        break;

    case 'PUT|tasks':
        $user_id = authenticate_jwt($db, false);
        $response = require __DIR__ . '/endpoints/tasks/register.php';
        break;

    case 'PATCH|tasks':
        $user_id = authenticate_jwt($db, false);
        $response = require __DIR__ . '/endpoints/tasks/update.php';
        break;

    case 'GET|tasks':
        $user_id = authenticate_jwt($db, false);
        $response = require __DIR__ . '/endpoints/tasks/return.php';
        break;

    case 'DELETE|tasks':
        $user_id = authenticate_jwt($db, false);
        $response = require __DIR__ . '/endpoints/tasks/remove.php';
        break;

    case 'PUT|tasks|random':
        $user_id = authenticate_jwt($db, false);
        $response = require __DIR__ . '/endpoints/tasks/register_random.php';
        break;

    default:
        $response = HttpResponse::fromStatus([
            'error' => 'Endpoint not found'
        ], 404);
        break;
}

return $response;
?>