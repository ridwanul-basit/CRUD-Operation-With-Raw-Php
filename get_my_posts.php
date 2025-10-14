<?php
session_start();
include 'db.php';

// CORS setup
$frontend_origin = "http://localhost:5173";
header("Access-Control-Allow-Origin: $frontend_origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check login (admin or student)
if (isset($_SESSION['student_id'])) {
   $author_id = $_SESSION['student_id'];
    $author_type = 'student';
} elseif (isset($_SESSION['admin_id'])) {
    $author_id = $_SESSION['admin_id'];
    $author_type = 'admin';
} else {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// Fetch posts
if ($author_type === 'admin') {
    // Admin sees all their posts, approved or pending
    $stmt = $conn->prepare("SELECT * FROM posts WHERE author_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $author_id);
} else {
    // Students see only approved posts
    $stmt = $conn->prepare("SELECT * FROM posts WHERE author_id = ? AND status='approved' ORDER BY id DESC");
    $stmt->bind_param("i", $author_id);
}

$stmt->execute();
$result = $stmt->get_result();
$posts = [];

while ($row = $result->fetch_assoc()) {
    $post_id = $row['id'];

    // Fetch comments
    if ($author_type === 'admin') {
        // Admin can see all comments on their posts
        $cStmt = $conn->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY id ASC");
    } else {
        // Student sees only approved comments
        $cStmt = $conn->prepare("SELECT * FROM comments WHERE post_id = ? AND status='approved' ORDER BY id ASC");
    }
    $cStmt->bind_param("i", $post_id);
    $cStmt->execute();
    $cResult = $cStmt->get_result();

    $comments = [];
    while ($c = $cResult->fetch_assoc()) {
        $comments[] = $c;
    }
    $row['comments'] = $comments;

    // Full image path for frontend
    if (!empty($row['image'])) {
        $row['image'] = "http://localhost/college_api/" . $row['image'];
    }

    $posts[] = $row;
    $cStmt->close();
}

$stmt->close();
$conn->close();

echo json_encode(["success" => true, "posts" => $posts]);
?>
