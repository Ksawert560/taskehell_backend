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
?>