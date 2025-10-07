<?php
session_start();
include 'db.php';
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$title = $data['title'];
$message = $data['message'];

$conn->query("INSERT INTO announcements(title,message) VALUES('$title','$message')");
echo json_encode(["success"=>true,"message"=>"Announcement added successfully"]);
$conn->close();
?>
