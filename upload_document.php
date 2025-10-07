<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

if (!isset($_FILES['file']) || !isset($_POST['title'])) {
    echo json_encode(["success" => false, "message" => "Missing file or title"]);
    exit;
}

$title = $conn->real_escape_string($_POST['title']);
$file = $_FILES['file'];

// Create uploads folder if not exist
$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Prepare file name
$filename = time() . "_" . basename($file['name']);
$targetFile = $uploadDir . $filename;
$filePathForDB = "uploads/" . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $targetFile)) {
    // Save to database
    $sql = "INSERT INTO documents (title, file_path) VALUES ('$title', '$filePathForDB')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true, "message" => "File uploaded successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Failed to move uploaded file."]);
}

$conn->close();
?>
