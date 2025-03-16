<?php
require "vendor/autoload.php";
require "db.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$db = new Database(
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_USER'] ?? 'root',
    $_ENV['DB_PASS'] ?? '',
    $_ENV['DB_NAME'] ?? 'taskhell'
);

$db -> connect();
$db -> add_user("Jan", "haslo");
$jan_id = $db -> get_user_id("Jan");
$db -> add_task($jan_id, "mayo");
$db -> remove_user("Jan");
$db -> disconnect();
?>