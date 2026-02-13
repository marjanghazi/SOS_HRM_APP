<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

// 1️⃣ Get Authorization Header
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

// 3️⃣ Extract ERP from token
$erp_number = $decoded->erp_number;

try {

    // 4️⃣ Fetch Leave Ledger for this ERP
    $ledger = DB::query(
        "SELECT id, year, leave_type, total_leaves, availed_leaves, balance
         FROM leave_ledger
         WHERE erp_number = %s
         ORDER BY year DESC",
        $erp_number
    );

    if (!$ledger) {
        echo json_encode([
            "success" => true,
            "message" => "No leave record found",
            "data" => []
        ]);
        exit;
    }

    // 5️⃣ Return Response
    echo json_encode([
        "success" => true,
        "message" => "Leave ledger fetched successfully",
        "data" => $ledger
    ]);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error"
    ]);
}
