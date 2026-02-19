<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    die(json_encode([
        "success" => false,
        "error" => "Only POST requests are allowed"
    ]));
}

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

    // 3️⃣ Fetch leave data with leave type name via JOIN
    $leaves = DB::query(
        "SELECT 
            lt.name AS leave_type,
            al.leave_nature,
            al.id,
            al.start_date,
            al.end_date,
            al.reason,
            al.status
         FROM apply_leaves al
         LEFT JOIN leave_types lt ON al.leave_type = lt.id
         WHERE al.erp_number = %s
         ORDER BY al.id DESC",
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
