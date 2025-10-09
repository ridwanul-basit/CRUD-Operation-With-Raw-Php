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

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['title'], $data['content'])) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

// Identify poster
if (isset($_SESSION['admin_id'])) {
    $author_id = $_SESSION['admin_id'];
    $author_name =  "Admin";
    $author_type = 'admin';
    $status = 'approved';
} elseif (isset($_SESSION['student_id'])) {
    $author_id = $_SESSION['student_id'];
    $author_name = $_SESSION['student_name'] ?? "Student";
    $author_type = 'student';
    $status = 'pending';
} else {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// Escape input
$title = $conn->real_escape_string($data['title']);
$content = $conn->real_escape_string($data['content']);
$author_name = $conn->real_escape_string($author_name);

// Insert post
$q = "INSERT INTO posts (title, content, author_id, author_name, author_type, status, created_at) 
      VALUES ('$title', '$content', '$author_id', '$author_name', '$author_type', '$status', NOW())";

if ($conn->query($q)) {
    echo json_encode(["success" => true, "message" => "Post added successfully", "post_id" => $conn->insert_id]);
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
}

$conn->close();
?>
