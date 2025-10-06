<?php
session_start();
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(["success"=>false,"message"=>"Not authorized"]);
    exit;
}

$result = $conn->query("SELECT id, name, email FROM admins ORDER BY id ASC");
$admins = [];
while($row = $result->fetch_assoc()) {
    $admins[] = $row;
}

echo json_encode($admins);
$conn->close();
?>
