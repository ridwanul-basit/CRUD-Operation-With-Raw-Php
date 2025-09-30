<?php
session_start();

// CORS headers for React dev server
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

include 'db.php';

// Session check: only logged-in admins can fetch
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

// Fetch students
$sql = "SELECT * FROM students_details ORDER BY roll ASC";
$result = $conn->query($sql);

$students = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

echo json_encode($students);
$conn->close();
?>
