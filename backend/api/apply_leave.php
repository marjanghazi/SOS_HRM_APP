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

// 6️⃣ Required fields
$requiredFields = [
    "manager_id",
    "hr_id",
    "segment_head_id",
    "attendance_id",
    "leave_type",
    "leave_nature",
    "start_date",
    "end_date",
    "start_time",
    "end_time",
    "reason"
];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || $data[$field] === "") {
        http_response_code(400);
        die(json_encode([
            "success" => false,
            "error" => "$field is required"
        ]));
    }
}

try {

    // 7️⃣ Get Leave Type ID
    $leaveType = DB::queryFirstRow(
        "SELECT id FROM leave_types WHERE name = %s AND status = 1",
        $data["leave_type"]
    );

    if (!$leaveType) {
        http_response_code(400);
        die(json_encode([
            "success" => false,
            "error" => "Invalid leave type"
        ]));
    }

    $leave_type_id = $leaveType['id'];

    // 8️⃣ Calculate requested leave days
    $start = new DateTime($data["start_date"]);
    $end   = new DateTime($data["end_date"]);

    if ($start > $end) {
        http_response_code(400);
        die(json_encode([
            "success" => false,
            "error" => "Start date cannot be after end date"
        ]));
    }

    $interval = $start->diff($end);
    $days = $interval->days + 1; // inclusive

    // If Half Day
    if (strtolower($data["leave_nature"]) === "half day") {
        $days = 0.5;
    }

    $currentYear = date("Y");

    // 9️⃣ Get Leave Balance
    $ledger = DB::queryFirstRow(
        "SELECT balance FROM leaves_ledger 
         WHERE erp_number = %s AND leave_type = %i AND year = %i",
        $erp_number,
        $leave_type_id,
        $currentYear
    );

    if (!$ledger) {
        http_response_code(400);
        die(json_encode([
            "success" => false,
            "error" => "Leave ledger not found for this leave type"
        ]));
    }

    if ($ledger['balance'] < $days) {
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
        "manager_id" => $data["manager_id"],
        "hr_id" => $data["hr_id"],
        "segment_head_id" => $data["segment_head_id"],
        "attendance_id" => $data["attendance_id"],
        "leave_type" => $data["leave_type"],
        "leave_nature" => $data["leave_nature"],
        "start_date" => $data["start_date"],
        "end_date" => $data["end_date"],
        "start_time" => $data["start_time"],
        "end_time" => $data["end_time"],
        "reason" => $data["reason"],
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
            "remaining_balance" => $ledger['balance'] - $days
        ]
    ]);
} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error"
    ]);
}
