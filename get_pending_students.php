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

$result = $conn->query("SELECT id, name, email, roll, age, gender, university, cgpa, major FROM students_details WHERE email_verified_at IS NULL ORDER BY id DESC");

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode(["success" => true, "students" => $students]);

$conn->close();
?>
