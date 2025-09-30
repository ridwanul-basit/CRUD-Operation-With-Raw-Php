<?php
date_default_timezone_set("UTC"); // Set UTC timezone

header("Access-Control-Allow-Origin: http://localhost:5173");  
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'db.php';
require 'vendor/autoload.php'; // PHPMailer autoload

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email'])) {
    $email = $conn->real_escape_string($data['email']);

    $sql = "SELECT * FROM students_details WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes")); // 5 minutes expiry

        $update = "UPDATE students_details 
                   SET reset_token='$token', reset_expiry='$expiry' 
                   WHERE email='$email'";

        if ($conn->query($update)) {
            $resetLink = "http://localhost:5173/student-reset-password?token=$token";

            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'basitridwanul@gmail.com'; // Your Gmail
                $mail->Password   = 'jzkb lgrj nydv uxlm';       // App password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                //Recipients
                $mail->setFrom('basitridwanul@gmail.com', 'University App');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Student Password Reset';
                $mail->Body    = "Click the link below to reset your password:<br>
                                  <a href='$resetLink'>$resetLink</a><br>
                                  Link will expire in 5 minutes.";

                $mail->send();

                echo json_encode(["success" => true, "message" => "Password reset link sent to your email."]);

            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => "Mailer Error: " . $mail->ErrorInfo]);
            }

        } else {
            echo json_encode(["success" => false, "message" => "Database error"]);
        }

    } else {
        echo json_encode(["success" => false, "message" => "Email not found"]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
}

$conn->close();
?>
