<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

// 1️⃣ Authorization
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

// 3️⃣ Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode([
        "success" => false,
        "error" => "Method not allowed"
    ]));
}

// 4️⃣ Get JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['leave_id']) || !isset($data['action'])) {
    http_response_code(400);
    die(json_encode([
        "success" => false,
        "error" => "leave_id and action are required"
    ]));
}

$leave_id = $data['leave_id'];
$action = strtolower($data['action']); // approve or reject
$remarks = $data['remarks'] ?? '';

if (!in_array($action, ['approve', 'reject'])) {
    http_response_code(400);
    die(json_encode([
        "success" => false,
        "error" => "Invalid action"
    ]));
}

try {

    // 5️⃣ Get logged-in user info
    $user = DB::queryFirstRow(
        "SELECT * FROM users WHERE erp_number = %s",
        $erp_number
    );

    if (!$user) {
        http_response_code(404);
        die(json_encode([
            "success" => false,
            "error" => "User not found"
        ]));
    }

    // 6️⃣ Get leave record
    $leave = DB::queryFirstRow(
        "SELECT * FROM apply_leaves WHERE id = %i",
        $leave_id
    );

    if (!$leave) {
        http_response_code(404);
        die(json_encode([
            "success" => false,
            "error" => "Leave not found"
        ]));
    }

    $now = date("Y-m-d H:i:s");

    // 7️⃣ Check Role and Approve
    if ($leave['manager_id'] == $user['id']) {

        DB::update("apply_leaves", [
            "is_manager_approve" => $action == 'approve' ? 1 : 0,
            "manager_status" => $action,
            "manager_remarks" => $remarks,
            "manager_approval_date" => $now
        ], "id=%i", $leave_id);

        $role = "Manager";
    } elseif ($leave['hr_id'] == $user['id']) {

        DB::update("apply_leaves", [
            "is_hr_approve" => $action == 'approve' ? 1 : 0,
            "hr_status" => $action,
            "hr_remarks" => $remarks,
            "hr_approval_date" => $now
        ], "id=%i", $leave_id);

        $role = "HR";
    } elseif ($leave['segment_head_id'] == $user['id']) {

        DB::update("apply_leaves", [
            "is_segment_head_approved" => $action == 'approve' ? 1 : 0,
            "segment_head_status" => $action,
            "segment_head_remarks" => $remarks,
            "segment_head_approval_date" => $now
        ], "id=%i", $leave_id);

        $role = "Segment Head";
    } elseif ($leave['attendance_id'] == $user['id']) {

        DB::update("apply_leaves", [
            "is_attendance_approved" => $action == 'approve' ? 1 : 0,
            "attendance_status" => $action,
            "attendance_remarks" => $remarks,
            "attendance_approval_date" => $now
        ], "id=%i", $leave_id);

        $role = "Attendance";
    } else {

        http_response_code(403);
        die(json_encode([
            "success" => false,
            "error" => "You are not authorized to approve this leave"
        ]));
    }

    // 8️⃣ If rejected → mark final status rejected
    if ($action == 'reject') {
        DB::update("apply_leaves", [
            "status" => "rejected"
        ], "id=%i", $leave_id);
    }

    echo json_encode([
        "success" => true,
        "message" => "$role has $action the leave successfully"
    ]);
} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error"
    ]);
}
