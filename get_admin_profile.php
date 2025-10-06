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

$id = intval($_SESSION['admin_id']);
$result = $conn->query("SELECT id,name,email FROM admins WHERE id=$id");

if($result && $result->num_rows > 0){
    $admin = $result->fetch_assoc();
    echo json_encode(["success"=>true,"admin"=>$admin]);
} else {
    echo json_encode(["success"=>false,"message"=>"Admin not found"]);
}

$conn->close();
?>
