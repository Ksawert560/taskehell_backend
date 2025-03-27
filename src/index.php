<?php
require_once __DIR__.'/utils/http_response.php';
require_once __DIR__.'/db.php';

try {
    $db = new Database(
        'mysql',
        $_SERVER['MYSQL_USER'],
        $_SERVER['MYSQL_PASSWORD'],
        $_SERVER['MYSQL_DATABASE'],
    );

    $db -> connect();

    $response = require __DIR__.'/endpoint_router.php';

    $response -> respond();
} catch (HttpResponse $response) {
    $response -> respond();
} finally {
    try {
        $db -> disconnect();
    } catch(HttpResponse $response) {
        $response -> respond();
    }
}






// declare(strict_types=1);
// require_once __DIR__.'/cors.php';
// require_once __DIR__.'/http_response.php';
// require_once __DIR__.'/config.php';
// require_once __DIR__.'/db.php';

// $db = new Database(
//     'mysql',
//     $env['DB_USER'],
//     $env['DB_PASS'],
//     $env['DB_NAME']
// );

// try {
//     $db->connect();
    
//     // Get the response from routing
//     $response = require __DIR__.'/endpoint_router.php';
    
//     // Handle returned responses
//     if ($response instanceof HttpResponse) {
//         $response->send();
//     }
    
//     // If we get here, no valid response was returned
//     throw new HttpResponse(500, ['error' => 'Invalid endpoint implementation']);
// } catch (HttpResponse $e) {
//     // Handle thrown responses
//     $e->send();
// } catch (Throwable $e) {
//     // Handle unexpected errors
//     error_log("System error: " . $e->getMessage());
//     (new HttpResponse(500, ['error' => 'Internal server error']))->send();
// } finally {
//     try {
//         $db->disconnect();
//     } catch (Exception $e) {
//         error_log("Disconnection error: " . $e->getMessage());
//     }
// }
?>