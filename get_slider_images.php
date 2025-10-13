<?php
session_start();
header("Content-Type: application/json");
include 'db.php';

$result = $conn->query("SELECT * FROM slider_images ORDER BY created_at DESC");
$images = [];
while($row = $result->fetch_assoc()) $images[] = $row;

echo json_encode(['success'=>true,'images'=>$images]);
?>
