<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
header("Content-Type: application/json");

// Allow only admins to delete
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$type = $data['type'] ?? null;

if (!$id || !$type) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

if ($type === 'post') {
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $conn->query("DELETE FROM comments WHERE post_id = $id");
} elseif ($type === 'comment') {
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit();
}

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => ucfirst($type) . ' deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => ucfirst($type) . ' not found or already deleted']);
}

$stmt->close();
$conn->close();
?>
