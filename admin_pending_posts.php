<?php
session_start();
include 'db.php';

// CORS headers
$frontend_origin = "http://localhost:5173";
header("Access-Control-Allow-Origin: $frontend_origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

// Only admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    exit;
}

$result = $conn->query("SELECT * FROM posts WHERE status='pending' ORDER BY id DESC");
$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

echo json_encode(["success"=>true,"posts"=>$posts]);
$conn->close();
?>
