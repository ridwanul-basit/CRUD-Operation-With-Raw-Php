<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
header("Content-Type: application/json");

// Allow only admins to edit
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$type = $data['type'] ?? null;
$content = trim($data['content'] ?? '');

if (!$id || !$type || $content === '') {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

if ($type === 'post') {
    $stmt = $conn->prepare("UPDATE posts SET content = ? WHERE id = ?");
} elseif ($type === 'comment') {
    $stmt = $conn->prepare("UPDATE comments SET content = ? WHERE id = ?");
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit();
}

$stmt->bind_param("si", $content, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => ucfirst($type) . ' updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>
