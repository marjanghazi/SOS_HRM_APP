<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed"]));
}

$conn->set_charset("utf8mb4");
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

