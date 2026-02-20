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
    http_response_code(405);
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

    // 3️⃣ Fetch leaves with leave type name
    $leaves = DB::query(
        "SELECT 
            al.id,
            lt.name AS leave_type,
            al.leave_nature,
            al.start_date,
            al.end_date,
            al.reason,
            al.manager_status,
            al.manager_id,
            al.manager_approval_date,
            al.hr_status,
            al.hr_id,
            al.hr_approval_date,
            al.segment_head_status,
            al.segment_head_id,
            al.segment_head_approval_date,
            al.attendance_status,
            al.attendance_id,
            al.attendance_approval_date,
            al.status,
            al.created_at
         FROM apply_leaves al
         LEFT JOIN leave_types lt ON al.leave_type = lt.id
         WHERE al.erp_number = %s
         ORDER BY al.id DESC",
        $erp_number
    );

    if (!$leaves) {
        echo json_encode([
            "success" => true,
            "count" => 0,
            "data" => []
        ]);
        exit;
    }

    $allApproverIds = [];
    foreach ($leaves as $leave) {
        $allApproverIds[] = $leave['manager_id'];
        $allApproverIds[] = $leave['attendance_id'];
        $allApproverIds[] = $leave['hr_id'];
        $allApproverIds[] = $leave['segment_head_id'];
    }
    $allApproverIds = array_unique(array_filter($allApproverIds));

    $users = [];
    if (!empty($allApproverIds)) {
        $placeholders = implode(',', array_fill(0, count($allApproverIds), '%s'));
        $query = "SELECT erp_number, name FROM users WHERE erp_number IN ($placeholders)";
        $usersData = DB::query($query, ...$allApproverIds);

        foreach ($usersData as $user) {
            $users[$user['erp_number']] = $user['name'];
        }
    }

    $response = [];
    foreach ($leaves as $leave) {
        $start = new DateTime($leave['start_date']);
        $end   = new DateTime($leave['end_date']);
        $interval = $start->diff($end);
        $days = $interval->days + 1;

        if (strtolower($leave['leave_nature']) === "half day") {
            $daysString = "0.5";
        } else {
            $daysString = (string) $days;
        }

        $getName = function ($erp) use ($users) {
            if (!$erp) return null;
            return $users[$erp] ?? "ERP number not matched";
        };

        $leaveData = [
            "id" => $leave['id'],
            "leave_type" => $leave['leave_type'], // now the NAME
            "leave_nature" => $leave['leave_nature'],
            "start_date" => $leave['start_date'],
            "end_date" => $leave['end_date'],
            "reason" => $leave['reason'],
            "leave_days" => $daysString,
            "final_status" => $leave['status'],
            "manager" => [
                "erp_number" => $leave['manager_id'],
                "name" => $getName($leave['manager_id']),
                "status" => $leave['manager_status'],
                "manager_approval_date" => $leave['manager_approval_date']
            ],
            "attendance" => [
                "erp_number" => $leave['attendance_id'],
                "name" => $getName($leave['attendance_id']),
                "status" => $leave['attendance_status'],
                "attendance_approval_date" => $leave['attendance_approval_date']
            ]
        ];

        if ($days > 4) {
            $leaveData["hr"] = [
                "erp_number" => $leave['hr_id'],
                "name" => $getName($leave['hr_id']),
                "status" => $leave['hr_status'],
                "hr_approval_date" => $leave['hr_approval_date']
            ];
            $leaveData["segment_head"] = [
                "erp_number" => $leave['segment_head_id'],
                "name" => $getName($leave['segment_head_id']),
                "status" => $leave['segment_head_status'],
                "segment_head_approval_date" => $leave['segment_head_approval_date']
            ];
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
        "error" => $e->getMessage()
    ]);
}
