<?php
session_start();
include 'db.php';
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

$result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;
echo json_encode(["success"=>true,"announcements"=>$data]);
$conn->close();
?>
