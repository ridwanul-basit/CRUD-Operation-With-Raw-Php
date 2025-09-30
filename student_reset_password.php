<?php
// Set timezone to UTC
date_default_timezone_set("UTC");

header("Access-Control-Allow-Origin: http://localhost:5173");  
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer autoload

$data = json_decode(file_get_contents("php://input"), true);

// Check required fields
if (isset($data['token'], $data['new_password'])) {
    $token = $conn->real_escape_string($data['token']);
    $new_password = password_hash($data['new_password'], PASSWORD_DEFAULT);

    // Select user with valid token and not expired
    $sql = "SELECT * FROM students_details 
            WHERE reset_token='$token' AND reset_expiry > UTC_TIMESTAMP() LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Update password and clear token & expiry
        $update = "UPDATE students_details 
                   SET password='$new_password', reset_token=NULL, reset_expiry=NULL
                   WHERE reset_token='$token'";

        if ($conn->query($update)) {
            echo json_encode([
                "success" => true,
                "message" => "Password reset successfully!"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Database error: " . $conn->error
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid or expired token"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid input"
    ]);
}

$conn->close();
?>
