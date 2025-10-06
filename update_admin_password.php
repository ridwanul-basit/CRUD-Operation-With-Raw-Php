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

if(isset($data['password'])){
    $password = password_hash($data['password'], PASSWORD_BCRYPT);
    if($conn->query("UPDATE admins SET password='$password' WHERE id=$id")){
        echo json_encode(["success"=>true,"message"=>"Password updated successfully"]);
    } else {
        echo json_encode(["success"=>false,"message"=>"Database error: ".$conn->error]);
    }
} else {
    echo json_encode(["success"=>false,"message"=>"Password required"]);
}

$conn->close();
?>
