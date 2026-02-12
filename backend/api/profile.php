<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";
require_once "../middleware/auth.php";

$stmt = $conn->prepare("SELECT id, name, erp_number, email FROM users WHERE id=?");
$stmt->bind_param("i", $decoded->id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

echo json_encode([
    "success" => true,
    "data" => $user
]);
