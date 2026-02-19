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

    if (!$leaves) {
        echo json_encode([
            "success" => true,
            "count" => 0,
            "data" => []
        ]);
        exit;
    }

    // 4️⃣ Collect all ERP IDs needed
    $allApproverIds = [];

    foreach ($leaves as $leave) {
        $allApproverIds[] = $leave['manager_id'];
        $allApproverIds[] = $leave['attendance_id'];
        $allApproverIds[] = $leave['hr_id'];
        $allApproverIds[] = $leave['segment_head_id'];
    }

    $allApproverIds = array_unique(array_filter($allApproverIds));

    // 5️⃣ Fetch users in one query
    $users = [];

    if (!empty($allApproverIds)) {
        $placeholders = implode(',', array_fill(0, count($allApproverIds), '%s'));
        $query = "SELECT erp_number, name FROM users WHERE erp_number IN ($placeholders)";
        $usersData = DB::query($query, ...$allApproverIds);

        foreach ($usersData as $user) {
            $users[$user['erp_number']] = $user['name'];
        }
    }

    // 6️⃣ Build response
    $response = [];

    foreach ($leaves as $leave) {

        $start = new DateTime($leave['start_date']);
        $end   = new DateTime($leave['end_date']);
        $interval = $start->diff($end);
        $days = $interval->days + 1;

        if (strtolower($leave['leave_nature']) === "half day") {
            $days = 0.5;
        }

        // Helper function
        $getName = function ($erp) use ($users) {
            if (!$erp) return null;
            return $users[$erp] ?? "ERP number not matched";
        };

        $leaveData = [
            "id" => $leave['id'],
            "leave_type" => $leave['leave_type'],
            "leave_nature" => $leave['leave_nature'],
            "start_date" => $leave['start_date'],
            "end_date" => $leave['end_date'],
            "reason" => $leave['reason'],
            "leave_days" => $days,
            "final_status" => $leave['status'],

            "manager" => [
                "erp_number" => $leave['manager_id'],
                "name" => $getName($leave['manager_id']),
                "status" => $leave['manager_status']
            ],

            "attendance" => [
                "erp_number" => $leave['attendance_id'],
                "name" => $getName($leave['attendance_id']),
                "status" => $leave['attendance_status']
            ]
        ];

        if ($days > 4) {
            $leaveData["hr"] = [
                "erp_number" => $leave['hr_id'],
                "name" => $getName($leave['hr_id']),
                "status" => $leave['hr_status']
            ];

            $leaveData["segment_head"] = [
                "erp_number" => $leave['segment_head_id'],
                "name" => $getName($leave['segment_head_id']),
                "status" => $leave['segment_head_status']
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
