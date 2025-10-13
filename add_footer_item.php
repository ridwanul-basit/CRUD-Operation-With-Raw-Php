<?php
session_start();
include 'db.php';

// Headers
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// Check admin login
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['type'], $data['name'], $data['link'])){
    echo json_encode(["success"=>false,"message"=>"Type, Name and Link are required"]);
    exit;
}

$type = $conn->real_escape_string($data['type']);
$name = $conn->real_escape_string($data['name']);
$link = $conn->real_escape_string($data['link']);
$icon_svg = isset($data['icon_svg']) ? $conn->real_escape_string($data['icon_svg']) : null;

// Insert into DB
$stmt = $conn->prepare("INSERT INTO footer_links (type, name, link, icon_svg, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $type, $name, $link, $icon_svg);

if($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Footer item added successfully"]);
} else {
    echo json_encode(["success"=>false,"message"=>"Failed to add footer item"]);
}

$stmt->close();
$conn->close();
?>
