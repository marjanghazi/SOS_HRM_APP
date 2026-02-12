<?php
// auth.php
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    die(json_encode(["success" => false, "error" => "Unauthorized"]));
}

$token = $matches[1];

try {
    $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
} catch (Exception $e) {
    http_response_code(401);
    die(json_encode(["success" => false, "error" => "Invalid or Expired Token"]));
}

// $decoded now has id and erp_number
