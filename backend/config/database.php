<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/db.class.php';

use Dotenv\Dotenv;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Configure MeekroDB
DB::$host = $_ENV['DB_HOST'];
DB::$user = $_ENV['DB_USER'];
DB::$password = $_ENV['DB_PASS'];
DB::$dbName = $_ENV['DB_NAME'];
DB::$encoding = 'utf8mb4';



// Headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
