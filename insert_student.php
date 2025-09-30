<?php
header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

include 'db.php';
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents("php://input"), true);

if (
    isset($data['name'], $data['email'], $data['roll'], $data['age'], 
          $data['gender'], $data['university'], $data['cgpa'], $data['major'], $data['password'])
) {
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $roll = $conn->real_escape_string($data['roll']);
    $age = (int)$data['age'];
    $gender = $conn->real_escape_string($data['gender']);
    $university = $conn->real_escape_string($data['university']);
    $cgpa = (float)$data['cgpa'];
    $major = $conn->real_escape_string($data['major']);
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    // Check for duplicate email or roll
    $check = $conn->query("SELECT * FROM students_details WHERE email='$email' OR roll='$roll'");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $errors = [];
        if ($row['email'] == $email) $errors[] = "Email already exists";
        if ($row['roll'] == $roll) $errors[] = "Roll number already exists";
        echo json_encode(["success"=>false, "message"=>implode(", ", $errors)]);
        exit;
    }

    // Generate verification token
    $verify_token = bin2hex(random_bytes(40));
    $verify_expires = date("Y-m-d H:i:s", strtotime("+24 hours"));

    $sql = "INSERT INTO students_details (name, email, roll, age, gender, university, cgpa, major, password, verify_token, verify_expires)
            VALUES ('$name', '$email', '$roll', $age, '$gender', '$university', $cgpa, '$major', '$password', '$verify_token', '$verify_expires')";

    if ($conn->query($sql) === TRUE) {
        // Send verification email
        $verifyLink = "http://localhost:5173/verify-email?token=$verify_token";
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'basitridwanul@gmail.com'; // your Gmail
            $mail->Password   = 'jzkb lgrj nydv uxlm';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('basitridwanul@gmail.com', 'College App');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email';
            $mail->Body    = "Hi $name,<br><br>Click the link below to verify your email:<br>
                              <a href='$verifyLink'>$verifyLink</a><br><br>Link expires in 24 hours.";

            $mail->send();

            echo json_encode(["success"=>true, "message"=>"Registration successful! Please check your email to verify your account."]);
        } catch (Exception $e) {
            echo json_encode(["success"=>false,"message"=>"Mailer Error: ".$mail->ErrorInfo]);
        }
    } else {
        echo json_encode(["success"=>false,"message"=>"Database error: ".$conn->error]);
    }

} else {
    echo json_encode(["success"=>false, "message"=>"Invalid input"]);
}

$conn->close();
?>
