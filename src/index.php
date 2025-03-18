<?php
require "vendor/autoload.php";
require "db.php";
require "endpoints.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$db = new Database(
    'mysql',
    $_ENV['MYSQL_USER'] ?? 'root',
    $_ENV['MYSQL_PASSWORD'] ?? 'rootpassword',
    $_ENV['MYSQL_DATABASE'] ?? 'taskhell'
);


$db -> connect();

$segments = return_segments();

resolve_endpoints($segments, $db);

$db -> disconnect();

?>