<?php
session_start();
include 'db.php';

// CORS headers
$frontend_origin = "http://localhost:5173";
header("Access-Control-Allow-Origin: $frontend_origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['email'], $data['password'])) {
    echo json_encode(["success" => false, "message" => "Email and password required"]);
    exit;
}

$email = $conn->real_escape_string($data['email']);
$password = $data['password'];

// Fetch student by email
$sql = "SELECT * FROM students_details WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows === 1) {
    $student = $result->fetch_assoc();

    // Check if email is verified
    if (!$student['email_verified_at']) {
        echo json_encode(["success" => false, "message" => "Email not verified. Please check your inbox."]);
        exit;
    }

    // Verify password
    if (password_verify($password, $student['password'])) {
        // Store session
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['name'];

        // Send student data back
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "student" => [
                "id" => $student['id'],
                "name" => $student['name'],
                "email" => $student['email'],
                "roll" => $student['roll'],
                "age" => $student['age'],
                "gender" => $student['gender'],
                "university" => $student['university'],
                "cgpa" => $student['cgpa'],
                "major" => $student['major']
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Incorrect password"]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Student not found"]);
}

$conn->close();
?>
