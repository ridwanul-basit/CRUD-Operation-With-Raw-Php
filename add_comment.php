<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['post_id'], $data['content'])) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

$post_id = intval($data['post_id']);
$content = trim($conn->real_escape_string($data['content']));

if ($content === "") {
    echo json_encode(["success" => false, "message" => "Content cannot be empty"]);
    exit;
}

// Identify poster
if (isset($_SESSION['admin_id'])) {
    $author_id = $_SESSION['admin_id'];
    $author_name = "Admin";
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

$author_name = $conn->real_escape_string($author_name);

$q = "INSERT INTO comments (post_id, author_id, author_name, author_type, content, status, created_at)
      VALUES ($post_id, $author_id, '$author_name', '$author_type', '$content', '$status', NOW())";

if ($conn->query($q)) {
    // Return the new comment as JSON for immediate UI update
    $comment_id = $conn->insert_id;
    echo json_encode([
        "success" => true,
        "message" => "Comment added successfully",
        "comment" => [
            "id" => $comment_id,
            "post_id" => $post_id,
            "author_id" => $author_id,
            "author_name" => $author_name,
            "author_type" => $author_type,
            "content" => $content,
            "status" => $status,
            "created_at" => date("Y-m-d H:i:s")
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
}

$conn->close();
?>
