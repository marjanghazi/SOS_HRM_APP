<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(["success" => false, "error" => "Only POST requests are allowed"]));
}

/* =========================
   AUTHENTICATION
========================= */

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    die(json_encode(["success" => false, "error" => "Authorization header missing or invalid"]));
}

$token = $matches[1];
$jwt_secret = $_ENV['JWT_SECRET'] ?? 'my_super_strong_jwt_secret_!123';

try {
    $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
} catch (Exception $e) {
    http_response_code(401);
    die(json_encode(["success" => false, "error" => "Invalid or expired token"]));
}

$erp_number = $decoded->erp_number ?? null;

if (!$erp_number) {
    http_response_code(401);
    die(json_encode(["success" => false, "error" => "Invalid token payload"]));
}

$erp_number_str = strval($erp_number);

/* =========================
   FETCH LEAVES
========================= */

try {

    $leaves = DB::query(
        "SELECT * FROM apply_leaves WHERE manager_id=%s ORDER BY created_at DESC",
        $erp_number_str
    );

    $response = [
        "pending"  => [],
        "approved" => [],
        "rejected" => []
    ];

    foreach ($leaves as $leave) {

        if ($leave['manager_status'] === 'approved') {
            $response['approved'][] = $leave;

        } elseif ($leave['manager_status'] === 'rejected') {
            $response['rejected'][] = $leave;

        } else {
            $response['pending'][] = $leave;
        }
    }

    echo json_encode([
        "success" => true,
        "manager_erp" => $erp_number_str,
        "counts" => [
            "pending"  => count($response['pending']),
            "approved" => count($response['approved']),
            "rejected" => count($response['rejected']),
        ],
        "data" => $response
    ]);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
