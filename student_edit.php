<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
header("Content-Type: application/json");

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$type = $data['type'] ?? null;
$content = trim($data['content'] ?? '');

if (!$id || !$type || !$content) {
    echo json_encode(['success' => false, 'message' => 'Content cannot be empty']);
    exit;
}

$student_id = $_SESSION['student_id'];

if ($type === 'post') {
    $stmt = $conn->prepare("UPDATE posts SET content=? WHERE id=? AND author_id=? AND author_type='student'");
    $stmt->bind_param("sii", $content, $id, $student_id);
} elseif ($type === 'comment') {
    $stmt = $conn->prepare("UPDATE comments SET content=? WHERE id=? AND author_id=? AND author_type='student'");
    $stmt->bind_param("sii", $content, $id, $student_id);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit;
}

$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => ucfirst($type) . " updated successfully"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cannot update: either not your own or no changes']);
}

$conn->close();
?>
