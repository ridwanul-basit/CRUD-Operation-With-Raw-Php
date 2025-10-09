<?php
session_start();
include 'db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$type = $data['type'] ?? null;

if (!$id || !$type) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$student_id = $_SESSION['student_id'];

if ($type === 'post') {
    $stmt = $conn->prepare("DELETE FROM posts WHERE id=? AND author_id=? AND author_type='student'");
    $stmt->bind_param("ii", $id, $student_id);
    $stmt->execute();

    // Delete related comments
    $stmt2 = $conn->prepare("DELETE FROM comments WHERE post_id=?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
} elseif ($type === 'comment') {
    $stmt = $conn->prepare("DELETE FROM comments WHERE id=? AND author_id=? AND author_type='student'");
    $stmt->bind_param("ii", $id, $student_id);
    $stmt->execute();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit;
}

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => ucfirst($type) . " deleted successfully"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cannot delete: not your own or already deleted']);
}
?>
