<?php
session_start();
include 'db.php';

// ✅ Proper CORS headers for React + PHP session-based login
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// ✅ Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// ✅ Read and validate JSON body
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$type = $data['type'] ?? null;

if (!$id || !$type) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$student_id = $_SESSION['student_id'];
$success = false;

// ✅ Delete logic
if ($type === 'post') {
    // Delete the post only if it belongs to this student
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND author_id = ? AND author_type = 'student'");
    $stmt->bind_param("ii", $id, $student_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Delete related comments if post deleted
        $stmt2 = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $success = true;
    }

} elseif ($type === 'comment') {
    // Delete the comment only if it belongs to this student
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND author_id = ? AND author_type = 'student'");
    $stmt->bind_param("ii", $id, $student_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = true;
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit;
}

// ✅ Final response
if ($success) {
    echo json_encode(['success' => true, 'message' => ucfirst($type) . ' deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Cannot delete: not your own or already deleted']);
}

$conn->close();
?>
