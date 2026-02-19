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

    // 3️⃣ Fetch leaves
    $leaves = DB::query(
        "SELECT 
            id,
            erp_number,
            leave_type,
            leave_nature,
            start_date,
            end_date,
            reason,
            manager_status,
            manager_id,
            hr_status,
            hr_id,
            segment_head_status,
            segment_head_id,
            attendance_status,
            attendance_id,
            status,
            created_at
         FROM apply_leaves
         WHERE erp_number = %s
         ORDER BY id DESC",
        $erp_number
    );

    $response = [];

    foreach ($leaves as $leave) {

        // 🔹 Calculate leave days
        $start = new DateTime($leave['start_date']);
        $end   = new DateTime($leave['end_date']);
        $interval = $start->diff($end);
        $days = $interval->days + 1;

        if (strtolower($leave['leave_nature']) === "half day") {
            $days = 0.5;
        }

        // 🔹 Base response
        $leaveData = [
            "id" => $leave['id'],
            "leave_type" => $leave['leave_type'],
            "leave_nature" => $leave['leave_nature'],
            "start_date" => $leave['start_date'],
            "end_date" => $leave['end_date'],
            "reason" => $leave['reason'],
            "leave_days" => $days,
            "final_status" => $leave['status'], // direct from DB
            "manager_status" => $leave['manager_status'],
            "manager_id" => $leave['manager_id'],
            "attendance_status" => $leave['attendance_status'],
            "attendance_id" => $leave['attendance_id'],
        ];

        // 🔹 If days > 4 include HR & Segment Head
        if ($days > 4) {
            $leaveData["hr_status"] = $leave['hr_status'];
            $leaveData["segment_head_status"] = $leave['segment_head_status'];
            $leaveData["hr_id"] = $leave['hr_id'];
            $leaveData["segment_head_id"] = $leave['segment_head_id'];
        }

        $response[] = $leaveData;
    }

    echo json_encode([
        "success" => true,
        "count" => count($response),
        "data" => $response
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
