<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Only allow logged-in admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// Fetch documents
$query = $conn->query("SELECT id, title, file_path FROM documents ORDER BY id DESC");

$documents = [];
if ($query) {
    while ($row = $query->fetch_assoc()) {
        // Make sure file_path is accessible from frontend
        $row['file_path'] = 'uploads/' . basename($row['file_path']);
        $documents[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "documents" => $documents
]);

$conn->close();
?>
