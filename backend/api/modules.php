<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

// 1️⃣ Get Authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    die(json_encode(["success" => false, "error" => "Authorization header not found or invalid"]));
}

$token = $matches[1];

// 2️⃣ Validate JWT
$jwt_secret = $_ENV['JWT_SECRET'] ?? 'my_super_strong_jwt_secret_!123';
try {
    $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
} catch (Exception $e) {
    http_response_code(401);
    die(json_encode(["success" => false, "error" => "Invalid or expired token"]));
}

// 3️⃣ Fetch modules from database
$stmt = $conn->prepare("SELECT * FROM modules WHERE status = 1"); // Only active modules
$stmt->execute();
$result = $stmt->get_result();

$modules = [];
while ($row = $result->fetch_assoc()) {
    $modules[] = $row;
}

// 4️⃣ Return JSON
echo json_encode([
    "success" => true,
    "message" => "Modules fetched successfully",
    "data" => $modules
]);
