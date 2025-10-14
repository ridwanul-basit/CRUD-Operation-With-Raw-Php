<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only admins can edit
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = $_POST['id'] ?? null;
$type = $_POST['type'] ?? null;
$content = trim($_POST['content'] ?? '');

if (!$id || !$type || $content === '') {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('post_', true) . '.' . $ext;
    $destination = 'uploads/' . $filename;

    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
        $imagePath = $destination;
    }
}

if ($type === 'post') {
    if ($imagePath) {
        $stmt = $conn->prepare("UPDATE posts SET content = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssi", $content, $imagePath, $id);
    } else {
        $stmt = $conn->prepare("UPDATE posts SET content = ? WHERE id = ?");
        $stmt->bind_param("si", $content, $id);
    }
} elseif ($type === 'comment') {
    $stmt = $conn->prepare("UPDATE comments SET content = ? WHERE id = ?");
    $stmt->bind_param("si", $content, $id);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit();
}

if ($stmt->execute()) {
    $response = ['success' => true, 'message' => ucfirst($type) . ' updated successfully'];
    if ($imagePath) {
        $response['image'] = "http://localhost/college_api/" . $imagePath;
    }
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>
