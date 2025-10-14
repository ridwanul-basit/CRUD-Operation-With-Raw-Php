<?php
session_start();
include 'db.php';

// CORS
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

$query = "SELECT * FROM posts WHERE status='approved' ORDER BY id DESC";
$result = $conn->query($query);

$posts = [];

while ($row = $result->fetch_assoc()) {
    $post_id = $row['id'];

    // Attach comments
    $cRes = $conn->query("SELECT * FROM comments WHERE post_id = $post_id AND status='approved' ORDER BY id ASC");
    $comments = [];
    while ($c = $cRes->fetch_assoc()) {
        $comments[] = $c;
    }
    $row['comments'] = $comments;

    // If image exists, prepend path (for easier React access)
    if (!empty($row['image'])) {
        $row['image'] = "http://localhost/college_api/" . $row['image'];
    }

    $posts[] = $row;
}

echo json_encode(["success" => true, "posts" => $posts]);
$conn->close();
?>
