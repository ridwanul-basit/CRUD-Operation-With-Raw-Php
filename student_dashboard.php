<?php
session_start();
include 'db.php';

$frontend_origin = "http://localhost:5173";
header("Access-Control-Allow-Origin: $frontend_origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if (!isset($_SESSION['student_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// Fetch only logged-in student's data
$id = $_SESSION['student_id'];
$sql = "SELECT id, name, email, roll, age, gender, university, cgpa, major FROM students_details WHERE id=$id";
$result = $conn->query($sql);

if ($result->num_rows === 1) {
    $student = $result->fetch_assoc();
    echo json_encode(["success" => true, "student" => $student]);
} else {
    echo json_encode(["success" => false, "message" => "Student not found"]);
}

$conn->close();
?>
