<?php
session_start();
$frontend_origin = "http://localhost:5173";
header("Access-Control-Allow-Origin: $frontend_origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

session_destroy();
echo json_encode(["success" => true, "message" => "Logged out successfully"]);
?>
