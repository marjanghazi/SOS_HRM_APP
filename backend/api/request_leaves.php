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
    $user_id = $decoded->id; // ID of the logged-in user
} catch (Exception $e) {
    http_response_code(401);
    die(json_encode(["success" => false, "error" => "Invalid or expired token"]));
}

// 3️⃣ Get POST data
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->leave_type, $data->start_date, $data->end_date, $data->reason)) {
    http_response_code(400);
    die(json_encode(["success" => false, "error" => "leave_type, start_date, end_date, and reason are required"]));
}

$leave_type = trim($data->leave_type);
$start_date = trim($data->start_date);
$end_date   = trim($data->end_date);
$reason     = trim($data->reason);

// 4️⃣ Optional: validate dates
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
    http_response_code(400);
    die(json_encode(["success" => false, "error" => "Dates must be in YYYY-MM-DD format"]));
}

// 5️⃣ Insert leave request into database
$stmt = $conn->prepare("INSERT INTO leaves (user_id, leave_type, start_date, end_date, reason, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
$stmt->bind_param("issss", $user_id, $leave_type, $start_date, $end_date, $reason);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Leave request submitted successfully"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database error: " . $stmt->error
    ]);
}
