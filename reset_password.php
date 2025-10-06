<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php';

// Always UTC
date_default_timezone_set("Asia/Dhaka");

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
header("Content-Type: application/json");

// Get input
$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? '';
$newPassword = $data['password'] ?? '';

if (!$token || !$newPassword) {
    echo json_encode(["success"=>false,"message"=>"Token and new password required"]);
    exit;
}

// Check token
$stmt = $conn->prepare("SELECT id, reset_token_expires FROM admins WHERE reset_token=? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $expires = strtotime($admin['reset_token_expires']);

    if ($expires >= time()) { // still valid
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt2 = $conn->prepare("UPDATE admins SET password=?, reset_token=NULL, reset_token_expires=NULL WHERE id=?");
        $stmt2->bind_param("si", $hashedPassword, $admin['id']);

        if ($stmt2->execute()) {
            echo json_encode(["success"=>true,"message"=>"Password reset successful"]);
        } else {
            echo json_encode(["success"=>false,"message"=>"Failed to reset password"]);
        }
    } else {
        echo json_encode(["success"=>false,"message"=>"Invalid or expired token"]);
    }
} else {
    echo json_encode(["success"=>false,"message"=>"Invalid or expired token"]);
}

$conn->close();
