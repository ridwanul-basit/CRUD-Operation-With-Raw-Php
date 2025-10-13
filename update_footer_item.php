<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = (int)($data['id'] ?? 0);
$type = $conn->real_escape_string($data['type'] ?? '');
$name = $conn->real_escape_string($data['name'] ?? '');
$link = $conn->real_escape_string($data['link'] ?? '');
$icon_svg = $conn->real_escape_string($data['icon_svg'] ?? '');

if(!$id || !$type || !$name || !$link){
    echo json_encode(["success"=>false,"message"=>"All fields required"]);
    exit;
}

$conn->query("UPDATE footer_links SET type='$type', name='$name', link='$link', icon_svg='$icon_svg' WHERE id=$id");
echo json_encode(["success"=>true,"message"=>"Footer item updated"]);
$conn->close();
