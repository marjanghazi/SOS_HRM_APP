<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

// Base URL of your project (CHANGE THIS)
$baseUrl = "https://unthrust-arillate-retha.ngrok-free.dev/project-root/backend/"; 

// 1️⃣ Get Authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    die(json_encode([
        "success" => false,
        "error" => "Authorization header not found or invalid"
    ]));
}

$token = $matches[1];

// 2️⃣ Validate JWT
$jwt_secret = $_ENV['JWT_SECRET'];

try {
    $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
} catch (Exception $e) {
    http_response_code(401);
    die(json_encode([
        "success" => false,
        "error" => "Invalid or expired token"
    ]));
}

try {

    // 3️⃣ Fetch active modules
    $modules = DB::query(
        "SELECT * FROM modules WHERE status = %i",
        1
    );

    // 4️⃣ Append full icon URL
    foreach ($modules as &$module) {
        if (!empty($module['icon'])) {
            $module['icon'] = $baseUrl . $module['icon'];
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Modules fetched successfully",
        "data" => $modules
    ]);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error"
    ]);
}