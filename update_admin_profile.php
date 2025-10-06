<?php
session_start();
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'db.php';

if(!isset($_SESSION['admin_logged_in']) || !isset($_SESSION['admin_id'])){
    echo json_encode(["success"=>false,"message"=>"Not authorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($_SESSION['admin_id']);

if(isset($data['name'],$data['email'])){
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    
    $sql = "UPDATE admins SET name='$name', email='$email' WHERE id=$id";
    if($conn->query($sql)){
        echo json_encode(["success"=>true,"message"=>"Profile updated successfully"]);
    } else {
        echo json_encode(["success"=>false,"message"=>"Database error: ".$conn->error]);
    }
} else {
    echo json_encode(["success"=>false,"message"=>"Invalid input"]);
}

$conn->close();
?>
