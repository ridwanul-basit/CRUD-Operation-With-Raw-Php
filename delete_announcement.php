<?php
session_start();
include 'db.php';
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

$id = $_GET['id'];
$conn->query("DELETE FROM announcements WHERE id='$id'");
echo json_encode(["success"=>true,"message"=>"Announcement deleted"]);
$conn->close();
?>
