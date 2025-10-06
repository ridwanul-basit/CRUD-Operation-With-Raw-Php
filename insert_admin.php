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

$data = json_decode(file_get_contents("php://input"), true);

if(isset($data['name'],$data['email'],$data['password'])){
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    // Check duplicate email
    $check = $conn->query("SELECT * FROM admins WHERE email='$email'");
    if($check->num_rows > 0){
        echo json_encode(["success"=>false,"message"=>"Email already exists"]);
        exit;
    }

    $sql = "INSERT INTO admins (name,email,password) VALUES ('$name','$email','$password')";
    if($conn->query($sql)){
        echo json_encode(["success"=>true,"message"=>"Admin added successfully"]);
    } else {
        echo json_encode(["success"=>false,"message"=>"Database error: ".$conn->error]);
    }
} else {
    echo json_encode(["success"=>false,"message"=>"Invalid input"]);
}

$conn->close();
?>
