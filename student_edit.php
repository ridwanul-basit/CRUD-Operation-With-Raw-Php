<?php
session_start();
include 'db.php';

// CORS setup
$frontend_origin = "http://localhost:5173";
header("Access-Control-Allow-Origin: $frontend_origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only students can edit
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$student_id = $_SESSION['student_id'];

// If it's a post edit, we might have file upload (multipart/form-data)
if (isset($_POST['type']) && $_POST['type'] === 'post') {
    $id = $_POST['id'] ?? null;
    $content = trim($_POST['content'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $hasImage = isset($_FILES['image']);

    if (!$id || (!$content && !$hasImage)) {
        echo json_encode(['success' => false, 'message' => 'Nothing to update']);
        exit;
    }

    // Handle optional image upload
    $imagePath = null;
    if ($hasImage && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/posts/";
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('post_') . '.' . $ext;
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    // Prepare SQL dynamically
    $sql = "UPDATE posts SET content=?, status='pending'";
    $params = [$content];
    $types = "s";

    if (!empty($title)) {
        $sql .= ", title=?";
        $params[] = $title;
        $types .= "s";
    }

    if ($imagePath) {
        // delete old image
        $res = $conn->query("SELECT image FROM posts WHERE id='$id' AND author_id='$student_id' AND author_type='student'");
        if ($res && $old = $res->fetch_assoc()) {
            if (!empty($old['image']) && file_exists($old['image'])) unlink($old['image']);
        }
        $sql .= ", image=?";
        $params[] = $imagePath;
        $types .= "s";
    }

    $sql .= " WHERE id=? AND author_id=? AND author_type='student'";
    $params[] = $id;
    $params[] = $student_id;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes or not authorized']);
    }
    exit;
}

// Otherwise, it’s a text-only JSON edit (for comments or post text)
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$type = $data['type'] ?? null;
$content = trim($data['content'] ?? '');

if (!$id || !$type || !$content) {
    echo json_encode(['success' => false, 'message' => 'Content cannot be empty']);
    exit;
}

if ($type === 'post') {
    $stmt = $conn->prepare("UPDATE posts SET content=?, status='pending' WHERE id=? AND author_id=? AND author_type='student'");
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
