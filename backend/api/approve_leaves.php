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

$data = json_decode(file_get_contents("php://input"), true);
$leave_id = $data['leave_id'] ?? null;
$remarks  = $data['remarks'] ?? '';

if (!$leave_id) {
    http_response_code(400);
    die(json_encode(["success" => false, "error" => "leave_id is required"]));
}

try {
    $leave = DB::queryFirstRow("SELECT * FROM apply_leaves WHERE id=%i", $leave_id);
    if (!$leave) {
        http_response_code(404);
        die(json_encode(["success" => false, "error" => "Leave not found"]));
    }

    $now = date('Y-m-d H:i:s');
    $erp_number_str = strval($erp_number);

    $sqlSet = "";
    $approval_type = "";

    if ($leave['manager_id'] == $erp_number_str) {
        $sqlSet = "manager_status='approved', is_manager_approve=1, manager_remarks=%s, manager_approval_date=%s";
        $approval_type = "manager_status";
    } elseif ($leave['hr_id'] == $erp_number_str) {
        $sqlSet = "hr_status='approved', is_hr_approve=1, hr_remarks=%s, hr_approval_date=%s";
        $approval_type = "hr_status";
    } elseif ($leave['segment_head_id'] == $erp_number_str) {
        $sqlSet = "segment_head_status='approved', is_segment_head_approved=1, segment_head_remarks=%s, segment_head_approval_date=%s";
        $approval_type = "segment_head_status";
    } elseif ($leave['attendance_id'] == $erp_number_str) {
        $sqlSet = "attendance_status='approved', status='approved', is_attendance_approved=1, attendance_remarks=%s, attendance_approval_date=%s";
        $approval_type = "attendance_status";
    } else {
        http_response_code(403);
        die(json_encode(["success" => false, "error" => "You are not authorized to approve this leave"]));
    }

    // ✅ Execute safe SQL with correct placeholders
    DB::query("UPDATE apply_leaves SET $sqlSet WHERE id=%i", $remarks, $now, $leave_id);

    echo json_encode([
        "success" => true,
        "message" => "Leave approved successfully",
        "leave_id" => $leave_id,
        "approved_by" => $erp_number_str,
        "approval_type" => $approval_type
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
