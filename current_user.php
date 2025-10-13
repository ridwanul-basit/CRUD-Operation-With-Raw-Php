<?php
session_start();
include 'db.php';
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if(isset($_SESSION['student_id'])) {
    $id = $_SESSION['student_id'];
    $res = $conn->query("SELECT id, name, email FROM students_details WHERE id=$id");
    $student = $res->fetch_assoc();
    echo json_encode(["success"=>true,"user"=>$student]);
} else {
    echo json_encode(["success"=>false]);
}
$conn->close();
?>
