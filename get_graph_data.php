<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// ✅ Only logged-in admin can access
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// --- Graph 1: Verified vs Pending ---
$verified = $conn->query("SELECT COUNT(*) AS total FROM students WHERE status='verified'")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) AS total FROM students WHERE status='pending'")->fetch_assoc()['total'];

// --- Graph 2: Students by Department ---
$deptData = [];
$deptQuery = $conn->query("SELECT department, COUNT(*) as total FROM students GROUP BY department");
while($row = $deptQuery->fetch_assoc()) {
    $deptData[] = $row;
}

// --- Graph 3: Monthly Registrations ---
$monthData = [];
$monthQuery = $conn->query("
    SELECT DATE_FORMAT(created_at, '%b') AS month, COUNT(*) AS total
    FROM students
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
");
while($row = $monthQuery->fetch_assoc()) {
    $monthData[] = $row;
}

echo json_encode([
    "success" => true,
    "verified" => $verified,
    "pending" => $pending,
    "departments" => $deptData,
    "monthly" => $monthData
]);
$conn->close();
?>
