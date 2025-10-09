<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check login (admin or student)
if (isset($_SESSION['admin_id'])) {
    $author_id = $_SESSION['admin_id'];
    $author_type = 'admin';
} elseif (isset($_SESSION['student_id'])) {
    $author_id = $_SESSION['student_id'];
    $author_type = 'student';
} else {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// Fetch only user's posts
$q = "SELECT * FROM posts WHERE author_id = '$author_id' AND author_type = '$author_type' ORDER BY id DESC";
$result = $conn->query($q);

$posts = [];
while ($row = $result->fetch_assoc()) {
    // Get comments for each post
    $post_id = $row['id'];
    $commentsRes = $conn->query("SELECT * FROM comments WHERE post_id = $post_id AND status='approved' ORDER BY id ASC");
    $comments = [];
    while ($c = $commentsRes->fetch_assoc()) {
        $comments[] = $c;
    }
    $row['comments'] = $comments;
    $posts[] = $row;
}

echo json_encode(["success" => true, "posts" => $posts]);
$conn->close();
?>
