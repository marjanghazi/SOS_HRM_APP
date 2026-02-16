<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

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

// 3️⃣ Check user exists
$userExists = DB::queryFirstField(
    "SELECT erp_number FROM users WHERE erp_number = %s",
    $erp_number
);

if (!$userExists) {
    http_response_code(404);
    die(json_encode([
        "success" => false,
        "error" => "User not found"
    ]));
}

// 4️⃣ Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode([
        "success" => false,
        "error" => "Method not allowed"
    ]));
}

// 5️⃣ Get JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    die(json_encode([
        "success" => false,
        "error" => "Invalid JSON data"
    ]));
}

try {

    // 7️⃣ Get Leave Type ID
    $leaveType = DB::queryFirstRow(
        "SELECT id FROM leave_types WHERE name = %s AND status = 1",
        $data["leave_type"] ?? null
    );

    $leave_type_id = $leaveType['id'] ?? null;

    // 8️⃣ Calculate requested leave days
    $start = isset($data["start_date"]) ? new DateTime($data["start_date"]) : null;
    $end   = isset($data["end_date"]) ? new DateTime($data["end_date"]) : null;

    if ($start && $end && $start > $end) {
        http_response_code(400);
        die(json_encode([
            "success" => false,
            "error" => "Start date cannot be after end date"
        ]));
    }

    $days = 0;
    if ($start && $end) {
        $interval = $start->diff($end);
        $days = $interval->days + 1; // inclusive
    }

    // If Half Day
    if (isset($data["leave_nature"]) && strtolower($data["leave_nature"]) === "half day") {
        $days = 0.5;
    }

    $currentYear = date("Y");

    // 9️⃣ Get Leave Balance
    $ledger = $leave_type_id ? DB::queryFirstRow(
        "SELECT balance FROM leaves_ledger 
         WHERE erp_number = %s AND leave_type = %i AND year = %i",
        $erp_number,
        $leave_type_id,
        $currentYear
    ) : null;

    if ($ledger && $ledger['balance'] < $days) {
        http_response_code(400);
        die(json_encode([
            "success" => false,
            "error" => "Insufficient leave balance",
            "available_balance" => $ledger['balance'],
            "requested_days" => $days
        ]));
    }

    // 🔟 Insert into apply_leaves
    DB::insert("apply_leaves", [
        "erp_number" => $erp_number,
        "manager_id" => isset($data["manager_id"]) ? (int)$data["manager_id"] : null,
        "hr_id" => isset($data["hr_id"]) ? (int)$data["hr_id"] : null,
        "segment_head_id" => isset($data["segment_head_id"]) ? (int)$data["segment_head_id"] : null,
        "attendance_id" => isset($data["attendance_id"]) ? (int)$data["attendance_id"] : null,
        "leave_type" => isset($leave_type_id) ? (int)$leave_type_id : null,
        "leave_nature" => $data["leave_nature"] ?? null,
        "start_date" => $data["start_date"] ?? null,
        "end_date" => $data["end_date"] ?? null,
        "start_time" => $data["start_time"] ?? null,
        "end_time" => $data["end_time"] ?? null,
        "reason" => $data["reason"] ?? null,
        "created_at" => date("Y-m-d H:i:s"),
        "updated_at" => date("Y-m-d H:i:s"),
        "status" => "pending",
        "manager_status" => "pending",
        "hr_status" => "pending",
        "segment_head_status" => "pending",
        "attendance_status" => "pending"
    ]);
    $leave_id = DB::insertId();

    echo json_encode([
        "success" => true,
        "message" => "Leave applied successfully",
        "data" => [
            "leave_id" => $leave_id,
            "erp_number" => $erp_number,
            "requested_days" => $days,
            // "remaining_balance" => $ledger['balance'] - $days ?? null
        ]
    ]);
} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error"
    ]);
}
