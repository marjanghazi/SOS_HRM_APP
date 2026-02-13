<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";
require_once "../middleware/auth.php";

header("Content-Type: application/json");

try {

    // Fetch user using MeekroDB
    $user = DB::queryFirstRow(
        "SELECT id, name, erp_number, email 
         FROM users 
         WHERE id = %i",
        $decoded->id
    );

    if (!$user) {
        http_response_code(404);
        die(json_encode([
            "success" => false,
            "error" => "User not found"
        ]));
    }

    echo json_encode([
        "success" => true,
        "data" => $user
    ]);
} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error"
    ]);
}
