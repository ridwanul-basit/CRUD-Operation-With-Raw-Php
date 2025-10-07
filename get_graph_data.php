<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// For debugging — remove these lines later
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Only logged-in admin can access
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// --- Graph 1: Verified vs Pending ---
$verifiedQuery = $conn->query("SELECT COUNT(*) AS total FROM students_details WHERE email_verified_at IS NOT NULL");
$pendingQuery  = $conn->query("SELECT COUNT(*) AS total FROM students_details WHERE email_verified_at IS NULL");

if (!$verifiedQuery || !$pendingQuery) {
    echo json_encode(["success" => false, "message" => "Database query failed", "error" => $conn->error]);
    exit;
}

// ✅ Convert string counts to integers
$verified = (int)($verifiedQuery->fetch_assoc()['total'] ?? 0);
$pending  = (int)($pendingQuery->fetch_assoc()['total'] ?? 0);

// ✅ Fallback for empty data (optional)
if ($verified === 0 && $pending === 0) {
    $verified = 1;
    $pending = 1;
}

// --- Graph 2: Students by Major ---
$deptData = [];
$deptQuery = $conn->query("SELECT major AS department, COUNT(*) AS total FROM students_details GROUP BY major");
if ($deptQuery) {
    while ($row = $deptQuery->fetch_assoc()) {
        $deptData[] = [
            "department" => $row['department'],
            "total" => (int)$row['total']
        ];
    }
}

// --- Graph 3: Monthly Registrations ---
$monthData = [];
$monthQuery = $conn->query("
    SELECT DATE_FORMAT(created_at, '%b') AS month, COUNT(*) AS total
    FROM students_details
    WHERE created_at IS NOT NULL
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
");
if ($monthQuery) {
    while ($row = $monthQuery->fetch_assoc()) {
        $monthData[] = [
            "month" => $row['month'],
            "total" => (int)$row['total']
        ];
    }
}

// ✅ Optional debug (you can remove later)
file_put_contents("debug_graph.txt", json_encode([
    "verified" => $verified,
    "pending" => $pending,
    "departments" => $deptData,
    "monthly" => $monthData
], JSON_PRETTY_PRINT));

echo json_encode([
    "success" => true,
    "verified" => $verified,
    "pending" => $pending,
    "departments" => $deptData,
    "monthly" => $monthData
]);

$conn->close();
?>
