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

    // 4️⃣ Fetch Leave Ledger with Leave Type name
    $ledger = DB::query(
        "SELECT ll.id, ll.year, ll.leave_type, ll.total_leaves, ll.availed_leaves, ll.balance,
                lt.name AS leave_type_name
         FROM leaves_ledger ll
         INNER JOIN leave_types lt ON ll.leave_type = lt.id
         WHERE ll.erp_number = %s
         ORDER BY ll.year DESC",
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

    // 5️⃣ Format data to nest leave_type
    $formatted = [];
    foreach ($ledger as $row) {
        $formatted[] = [
            "id" => $row['id'],
            "year" => $row['year'],
            "leave_type" => [
                "name" => $row['leave_type_name']
            ],
            "total_leaves" => $row['total_leaves'],
            "availed_leaves" => $row['availed_leaves'],
            "balance" => $row['balance']
        ];
    }

    // 6️⃣ Return Response
    echo json_encode([
        "success" => true,
        "message" => "Leave ledger fetched successfully",
        "data" => $formatted
    ]);
} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error"
    ]);
}
