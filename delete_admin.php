<?php
session_start();
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'db.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode(["success"=>false,"message"=>"Not authorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if(!isset($data['id'])){
    echo json_encode(["success"=>false,"message"=>"Admin ID required"]);
    exit;
}

$id = $conn->real_escape_string($data['id']);

if($conn->query("DELETE FROM admins WHERE id='$id'")){
    echo json_encode(["success"=>true,"message"=>"Admin deleted successfully"]);
} else {
    echo json_encode(["success"=>false,"message"=>"Failed to delete admin"]);
}

$conn->close();
?>
