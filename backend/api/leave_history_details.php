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

    // 3️⃣ Fetch all leaves for ERP
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
            created_at
         FROM apply_leaves
         WHERE erp_number = %s
         ORDER BY id DESC",
        $erp_number
    );

    $response = [];

    foreach ($leaves as $leave) {

        // 🔹 Calculate leave days first
        $start = new DateTime($leave['start_date']);
        $end   = new DateTime($leave['end_date']);
        $interval = $start->diff($end);
        $days = $interval->days + 1;

        if (strtolower($leave['leave_nature']) === "half day") {
            $days = 0.5;
        }

        // 🔹 Initialize final_status
        $final_status = "pending";
        $approval_fields = [
            "manager_status" => $leave['manager_status'],
            "attendance_status" => $leave['attendance_status']
        ];

        if ($days <= 4) {
            // Only manager + attendance required
            $final_status = (strtolower($leave['manager_status']) === "approved" &&
                strtolower($leave['attendance_status']) === "approved") ?
                "approved" : (
                    (strtolower($leave['manager_status']) === "rejected" ||
                        strtolower($leave['attendance_status']) === "rejected") ?
                    "rejected" : "pending");
        } else {
            // Include HR and Segment Head approvals for leaves > 4
            $approval_fields['hr_status'] = $leave['hr_status'];
            $approval_fields['segment_head_status'] = $leave['segment_head_status'];

            $final_status = (strtolower($leave['manager_status']) === "approved" &&
                strtolower($leave['attendance_status']) === "approved" &&
                strtolower($leave['hr_status']) === "approved" &&
                strtolower($leave['segment_head_status']) === "approved") ?
                "approved" : (
                    (strtolower($leave['manager_status']) === "rejected" ||
                        strtolower($leave['attendance_status']) === "rejected" ||
                        strtolower($leave['hr_status']) === "rejected" ||
                        strtolower($leave['segment_head_status']) === "rejected") ?
                    "rejected" : "pending");
        }

        // 🔹 Build response
        $response[] = [
            "id" => $leave['id'],
            "erp_number" => $leave['erp_number'],
            "reason" => $leave['reason'],
            "leave_type" => $leave['leave_type'],
            "leave_nature" => $leave['leave_nature'],
            "start_date" => $leave['start_date'],
            "end_date" => $leave['end_date'],
            "created_at" => $leave['created_at'],
            "leave_days" => $days,
            "final_status" => $final_status
        ] + $approval_fields; // merge only relevant approval statuses
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
