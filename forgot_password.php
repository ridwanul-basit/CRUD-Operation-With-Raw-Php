<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include 'db.php';

// Always work in local
date_default_timezone_set("Asia/Dhaka");

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
header("Content-Type: application/json");

// Get email from JSON
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

if (!$email) {
    echo json_encode(["success"=>false,"message"=>"Email is required"]);
    exit;
}

// Check if email exists
$stmt = $conn->prepare("SELECT id FROM admins WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $token = bin2hex(random_bytes(16));
    $expires = gmdate("Y-m-d H:i:s", strtotime("+5 minutes")); // UTC expiry for 5 minutes


    // Save token and expiry in DB
    $stmt2 = $conn->prepare("UPDATE admins SET reset_token=?, reset_token_expires=? WHERE id=?");
    $stmt2->bind_param("ssi", $token, $expires, $admin['id']);
    if (!$stmt2->execute()) {
        error_log("Failed to save reset token: ".$stmt2->error);
        echo json_encode(["success"=>false,"message"=>"Failed to generate reset link"]);
        exit;
    }

    $resetLink = "http://localhost:5173/reset-password?token=$token";

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'basitridwanul@gmail.com'; // <-- Replace with sending Gmail
        $mail->Password   = 'jzkb lgrj nydv uxlm';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('basitridwanul@gmail.com', 'Admin');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Reset your admin password';
        $mail->Body = "Click <a href='$resetLink'>this link</a> to reset your password.<br>Expires in 5 minutes (UTC).";

        $mail->send();
        echo json_encode(["success"=>true,"message"=>"Reset link sent to your email."]);

    } catch (Exception $e) {
        error_log("PHPMailer error: ".$mail->ErrorInfo);
        echo json_encode(["success"=>false,"message"=>"Failed to send email."]);
    }

} else {
    echo json_encode(["success"=>false,"message"=>"Email not found"]);
}

$conn->close();
