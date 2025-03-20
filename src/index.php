<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

$pepper = $ENV['PEPPER'] ?? 'basicpepper';

$db -> connect();

$segments = return_segments();

resolve_endpoints($segments, $db, $pepper);

$db -> disconnect();

?>