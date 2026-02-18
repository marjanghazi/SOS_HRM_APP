<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1️⃣ Authorization Header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    die(json_encode([
        "success" => false,
        "error" => "Authorization header missing or invalid"
    ]));
}

$token = $matches[1];

// 2️⃣ Validate JWT
$jwt_secret = $_ENV['JWT_SECRET'] ?? 'my_super_strong_jwt_secret_!123';

try {
    $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
} catch (Exception $e) {
    http_response_code(401);
    die(json_encode([
        "success" => false,
        "error" => "Invalid or expired token"
    ]));
}

$erp_number = $decoded->erp_number ?? null;

if (!$erp_number) {
    http_response_code(401);
    die(json_encode([
        "success" => false,
        "error" => "Invalid token payload"
    ]));
}

try {

    // 3️⃣ Fetch Leave History
    $leaves = DB::query(
        "SELECT 
            id,
            erp_number,
            reason,
            leave_type,
            leave_nature,
            start_date,
            end_date,
            manager_status,
            hr_status,
            segment_head_status,
            attendance_status,
            status,
            manager_approval_date,
            hr_approval_date,
            segment_head_approval_date,
            attendance_approval_date,
            created_at
         FROM apply_leaves
         WHERE erp_number = %s
         ORDER BY id DESC",
        $erp_number
    );

    echo json_encode([
        "success" => true,
        "count" => count($leaves),
        "data" => $leaves
    ]);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}
