<?php
require_once "../config/database.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    exit(json_encode(["error" => "Invalid JSON"]));
}

/* Required fields */
$required = [
    "name",
    "status",
    "designation",
    "erp_number",
    "appointment_date",
    "segment",
    "segment_id",
    "email",
    "phone",
    "password"
];

foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        exit(json_encode(["error" => "$field is required"]));
    }
}

/* Assign variables */
$name = trim($data["name"]);
$status = trim($data["status"]);
$designation = trim($data["designation"]);
$designation_code = $data["designation_code"] ?? null;
$erp_number = trim($data["erp_number"]);
$appointment_date = $data["appointment_date"];
$current_posting = $data["current_posting"] ?? null;
$last_posting = $data["last_posting"] ?? null;
$line_manager = $data["line_manager"] ?? null;
$segment_head = $data["segment_head"] ?? null;
$segment = trim($data["segment"]);
$segment_id = intval($data["segment_id"]);
$shift = $data["shift"] ?? null;
$current_salary = $data["current_salary"] ?? 20000;
$email = trim($data["email"]);
$phone = trim($data["phone"]);
$emergency_contact = $data["emergency_contact"] ?? null;
$gender = $data["gender"] ?? "other";
$password = password_hash($data["password"], PASSWORD_BCRYPT);
$profile_image = $data["profile_image"] ?? null;

/* Default system values */
$role_id = 1;
$cfo_id = 408;
$hr_id = 1115;
$attendance_id = 138;
$accountant_id = 125;
$admin_id = 594;
$employment_status = 'A';
$is_permanent = ($status === "Permanent") ? 1 : 0;
$admin_approved = 1;

try {

    /* Check duplicate */
    $existing = DB::queryFirstRow(
        "SELECT id FROM users WHERE email=%s OR erp_number=%s",
        $email,
        $erp_number
    );

    if ($existing) {
        http_response_code(409);
        exit(json_encode(["error" => "Email or ERP already exists"]));
    }

    /* Insert */
    DB::insert('users', [
        'name' => $name,
        'status' => $status,
        'designation' => $designation,
        'designation_code' => $designation_code,
        'erp_number' => $erp_number,
        'appointment_date' => $appointment_date,
        'current_posting' => $current_posting,
        'last_posting' => $last_posting,
        'line_manager' => $line_manager,
        'segment_head' => $segment_head,
        'segment' => $segment,
        'segment_id' => $segment_id,
        'shift' => $shift,
        'current_salary' => $current_salary,
        'email' => $email,
        'phone' => $phone,
        'password' => $password,
        'emergency_contact' => $emergency_contact,
        'profile_image' => $profile_image,
        'role_id' => $role_id,
        'cfo_id' => $cfo_id,
        'hr_id' => $hr_id,
        'attendance_id' => $attendance_id,
        'accountant_id' => $accountant_id,
        'admin_id' => $admin_id,
        'gender' => $gender,
        'is_permanent' => $is_permanent,
        'employment_status' => $employment_status,
        'admin_approved' => $admin_approved
    ]);

    echo json_encode([
        "message" => "User registered successfully"
    ]);
} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "error" => "Registration failed"
    ]);
}
