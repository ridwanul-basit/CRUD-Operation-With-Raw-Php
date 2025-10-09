<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

$result = $conn->query("SELECT * FROM posts WHERE status='approved' ORDER BY id DESC");
$posts = [];

while ($row = $result->fetch_assoc()) {
    $post_id = $row['id'];
    $cRes = $conn->query("SELECT * FROM comments WHERE post_id = $post_id AND status='approved' ORDER BY id ASC");
    $comments = [];
    while ($c = $cRes->fetch_assoc()) $comments[] = $c;
    $row['comments'] = $comments;
    $posts[] = $row;
}

echo json_encode(["success" => true, "posts" => $posts]);
$conn->close();
?>
