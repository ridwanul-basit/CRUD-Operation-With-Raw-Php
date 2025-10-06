<?php
session_start();
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'db.php';

// Only admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "Not authorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id'])) {
    echo json_encode(["success" => false, "message" => "Student ID required"]);
    exit;
}

$id = $conn->real_escape_string($data['id']);

// Set timezone to your local timezone (change if needed)
date_default_timezone_set("Asia/Dhaka"); // for Bangladesh local time
$now = date("Y-m-d H:i:s");

// Update student: set email_verified_at and clear token & expiry
$sql = "UPDATE students_details 
        SET email_verified_at='$now', verify_token=NULL, verify_expires=NULL 
        WHERE id='$id'";

if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Student verified successfully!"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to verify student"
    ]);
}

$conn->close();
?>
