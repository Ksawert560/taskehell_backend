<?php
function sendResponse($codeOrResponse, $data = null, $exit=true) {
    if (is_int($codeOrResponse) && $data !== null) {
        http_response_code($codeOrResponse);
        echo json_encode($data);
    } elseif (is_string($codeOrResponse)) {
        // Shortcut response
        switch($codeOrResponse) {
            case 'Invalid endpoint':
                http_response_code(404);
                break;
            case 'Invalid credentials':
                http_response_code(401);
                break;
            case 'Method not allowed':
                http_response_code(405);
                break;
            case 'Resource not found':
                http_response_code(404);
            default:
                http_response_code(400);
                break;
        }
        echo json_encode(['error' => $codeOrResponse]);
    } elseif (is_array($codeOrResponse)) {
        http_response_code(400);
        echo json_encode(['errors' => $codeOrResponse]);
    }
    if($exit) {
        exit;
    }
}

function checkRequiredFields($required_fields) {
    $missingFields = [];

    foreach($required_fields as $field) {
        if (empty($field['value']) && $field['value'] !== '0') {
            $missingFields[] = "{$field['key']} is required";
        }
    }

    return $missingFields;
}

function return_segments() {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = rtrim($uri, '/');

    return explode('/', $uri);
}
?>