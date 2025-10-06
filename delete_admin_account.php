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

if($conn->query("DELETE FROM admins WHERE id=$id")){
    session_destroy(); // log out after deletion
    echo json_encode(["success"=>true,"message"=>"Account deleted successfully"]);
} else {
    echo json_encode(["success"=>false,"message"=>"Failed to delete account"]);
}

$conn->close();
?>
