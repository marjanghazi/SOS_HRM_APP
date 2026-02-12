<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Firebase\JWT\JWT;

header("Content-Type: application/json");

// 1️⃣ Custom App Token
$CUSTOM_TOKEN = $_ENV['APP_CUSTOM_TOKEN'] ?? 'abc123';

// 2️⃣ Get input
$data = json_decode(file_get_contents("php://input"));
if (!isset($data->erp_number, $data->password, $data->token)) {
    http_response_code(400);
    die(json_encode(["success" => false, "error" => "erp_number, password, and token are required"]));
}

// 3️⃣ Validate token
if ($data->token !== $CUSTOM_TOKEN) {
    http_response_code(401);
    die(json_encode(["success" => false, "error" => "Invalid token"]));
}

$erp_number = trim($data->erp_number);
$password = $data->password;

// 4️⃣ Validate erp_number format
if (!preg_match('/^\d{6}$/', $erp_number)) {
    http_response_code(400);
    die(json_encode(["success" => false, "error" => "erp_number must be exactly 6 digits"]));
}

// 5️⃣ Fetch user from database (select all relevant columns)
$stmt = $conn->prepare("
    SELECT * 
    FROM users 
    WHERE erp_number = ?
");
$stmt->bind_param("s", $erp_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    http_response_code(401);
    die(json_encode(["success" => false, "error" => "Invalid credentials"]));
}

$user = $result->fetch_assoc();

// 6️⃣ Verify password
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    die(json_encode(["success" => false, "error" => "Invalid credentials"]));
}

// 7️⃣ Create JWT
$payload = [
    "iat" => time(),
    "exp" => time() + ($_ENV['JWT_EXP'] ?? 3600),
    "id"  => $user['id'],
    "erp_number" => $user['erp_number']
];

$jwt_secret = $_ENV['JWT_SECRET'] ?? 'my_super_strong_jwt_secret_!123';
$jwt = JWT::encode($payload, $jwt_secret, 'HS256');

// 8️⃣ Return all user data
echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "token" => $jwt,
    "data" => $user  // returns all columns as-is
]);
