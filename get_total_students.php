<?php
session_start();
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'db.php';

// Only admin can access
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "Not authorized"]);
    exit;
}

// Total students
$resultTotal = $conn->query("SELECT COUNT(*) AS total FROM students_details");

// Verified students
$resultVerified = $conn->query("SELECT COUNT(*) AS verified 
                                FROM students_details 
                                WHERE email_verified_at IS NOT NULL AND email_verified_at != ''");

// Pending / Non-verified students
$resultPending = $conn->query("SELECT COUNT(*) AS pending 
                               FROM students_details 
                               WHERE email_verified_at IS NULL OR email_verified_at = ''");

// Fetch counts
$total = $resultTotal->fetch_assoc()['total'] ?? 0;
$verified = $resultVerified->fetch_assoc()['verified'] ?? 0;
$pending = $resultPending->fetch_assoc()['pending'] ?? 0;

// Return JSON
echo json_encode([
    "success" => true,
    "total" => (int)$total,
    "verified" => (int)$verified,
    "pending" => (int)$pending
]);

$conn->close();
?>
