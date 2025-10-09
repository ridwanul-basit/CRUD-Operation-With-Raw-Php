<?php
session_start();
include 'db.php';

// CORS headers
$frontend_origin = "http://localhost:5173";
header("Access-Control-Allow-Origin: $frontend_origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

// Only admin can approve
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    exit;
}

// Get data
$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id']);
$type = $data['type']; // 'post' or 'comment'

if ($type === 'post') {
    $q = "UPDATE posts SET status='approved' WHERE id=$id";
} elseif ($type === 'comment') {
    $q = "UPDATE comments SET status='approved' WHERE id=$id";
} else {
    echo json_encode(["success"=>false,"message"=>"Invalid type"]);
    exit;
}

if ($conn->query($q)) {
    echo json_encode(["success"=>true,"message"=>"Approved successfully"]);
} else {
    echo json_encode(["success"=>false,"message"=>$conn->error]);
}

$conn->close();
?>
