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
    "name","status","designation","erp_number",
    "appointment_date","segment","segment_id",
    "email","phone","password"
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

/* Check duplicate */
$check = $conn->prepare("SELECT id FROM users WHERE email = ? OR erp_number = ?");
$check->bind_param("ss", $email, $erp_number);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    http_response_code(409);
    exit(json_encode(["error" => "Email or ERP already exists"]));
}

/* Insert */
$stmt = $conn->prepare("
INSERT INTO users (
    name,status,designation,designation_code,erp_number,
    appointment_date,current_posting,last_posting,
    line_manager,segment_head,segment,segment_id,
    shift,current_salary,email,phone,password,
    emergency_contact,profile_image,
    role_id,cfo_id,hr_id,attendance_id,
    accountant_id,admin_id,
    gender,is_permanent,employment_status,admin_approved
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");
$stmt->bind_param(
    "sssssssssssissssssssiiiiiiisi",
    $name,
    $status,
    $designation,
    $designation_code,
    $erp_number,
    $appointment_date,
    $current_posting,
    $last_posting,
    $line_manager,
    $segment_head,
    $segment,
    $segment_id,
    $shift,
    $current_salary,
    $email,
    $phone,
    $password,
    $emergency_contact,
    $profile_image,
    $role_id,
    $cfo_id,
    $hr_id,
    $attendance_id,
    $accountant_id,
    $admin_id,
    $gender,
    $is_permanent,
    $employment_status,
    $admin_approved
);



if ($stmt->execute()) {
    echo json_encode(["message" => "User registered successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Registration failed"]);
}
