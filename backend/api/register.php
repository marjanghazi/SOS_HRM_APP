<?php
require_once "../config/database.php";

$data = json_decode(file_get_contents("php://input"));

// Check required fields
$required = [
    "name", "status", "designation", "erp_number", "appointment_date",
    "current_posting", "last_posting", "line_manager", "segment_head",
    "shift", "email", "password", "emergency_contact", "profile_image"
];

foreach ($required as $field) {
    if (!isset($data->$field) || empty($data->$field)) {
        http_response_code(400);
        die(json_encode(["error" => "All fields are required, missing: $field"]));
    }
}

// Trim and assign variables
$name = trim($data->name);
$status = trim($data->status);
$designation = trim($data->designation);
$erp_number = trim($data->erp_number);
$appointment_date = $data->appointment_date;
$current_posting = trim($data->current_posting);
$last_posting = trim($data->last_posting);
$line_manager = trim($data->line_manager);
$segment_head = trim($data->segment_head);
$shift = trim($data->shift);
$email = trim($data->email);
$password = password_hash($data->password, PASSWORD_BCRYPT);
$emergency_contact = trim($data->emergency_contact);
$profile_image = trim($data->profile_image);

// Prepare SQL
$stmt = $conn->prepare("
    INSERT INTO users 
    (name, status, designation, erp_number, appointment_date, current_posting, last_posting, line_manager, segment_head, shift, email, password, emergency_contact, profile_image) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "ssssssssssssss",
    $name, $status, $designation, $erp_number, $appointment_date,
    $current_posting, $last_posting, $line_manager, $segment_head,
    $shift, $email, $password, $emergency_contact, $profile_image
);

// Execute and respond
if ($stmt->execute()) {
    echo json_encode(["message" => "User registered successfully"]);
} else {
    http_response_code(409);
    echo json_encode(["error" => "User with this ERP number or email may already exist"]);
}
