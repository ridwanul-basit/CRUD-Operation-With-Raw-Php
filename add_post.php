<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['title'], $data['content'])) {
    echo json_encode(["success" => false, "message" => "Missing title or content"]);
    exit;
}

$title = $conn->real_escape_string($data['title']);
$content = $conn->real_escape_string($data['content']);

// Identify who is posting
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $author_id = $_SESSION['admin_id'];
    $author_name = $_SESSION['admin_name'];
    $author_type = 'admin';
} elseif (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in']) {
    $author_id = $_SESSION['student_id'];
    $author_name = $_SESSION['student_name'];
    $author_type = 'student';
} else {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$query = "INSERT INTO posts (title, content, author_id, author_name, author_type, created_at)
          VALUES ('$title', '$content', '$author_id', '$author_name', '$author_type', NOW())";

if ($conn->query($query)) {
    echo json_encode(["success" => true, "message" => "Post created successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
}

$conn->close();
?>
