<?php
session_start();
include 'db.php';

// CORS headers
$frontend_origin = "http://localhost:5173";
header("Access-Control-Allow-Origin: $frontend_origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

// Identify poster
if (isset($_SESSION['admin_id'])) {
    $author_id = $_SESSION['admin_id'];
    $author_name = "Admin";
    $author_type = 'admin';
    $status = 'approved';
} elseif (isset($_SESSION['student_id'])) {
    $author_id = $_SESSION['student_id'];
    $author_name = $_SESSION['student_name'] ?? "Student";
    $author_type = 'student';
    $status = 'pending';
} else {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

// Expecting multipart/form-data now
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';

if (empty($title) || empty($content)) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

$author_name = $conn->real_escape_string($author_name);
$title = $conn->real_escape_string($title);
$content = $conn->real_escape_string($content);

// Handle image upload
$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . "/uploads/posts/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = uniqid() . "_" . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $imagePath = "uploads/posts/" . $filename;
    } else {
        echo json_encode(["success" => false, "message" => "Failed to upload image"]);
        exit;
    }
}

// Insert into DB
$q = "INSERT INTO posts (title, content, image, author_id, author_name, author_type, status, created_at)
      VALUES ('$title', '$content', " . ($imagePath ? "'$imagePath'" : "NULL") . ", '$author_id', '$author_name', '$author_type', '$status', NOW())";

if ($conn->query($q)) {
    echo json_encode([
        "success" => true,
        "message" => "Post added successfully",
        "post_id" => $conn->insert_id,
        "image" => $imagePath
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
}

$conn->close();
?>
