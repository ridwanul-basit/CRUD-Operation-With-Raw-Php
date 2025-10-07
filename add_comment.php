<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if(isset($data['post_id'], $data['author_name'], $data['content'])){
    $post_id = (int)$data['post_id'];
    $author_name = $conn->real_escape_string($data['author_name']);
    $content = $conn->real_escape_string($data['content']);

    $sql = "INSERT INTO comments(post_id, author_name, content) VALUES($post_id,'$author_name','$content')";
    if($conn->query($sql)){
        echo json_encode(["success"=>true,"message"=>"Comment added successfully"]);
    } else {
        echo json_encode(["success"=>false,"message"=>"Database error: ".$conn->error]);
    }
}else{
    echo json_encode(["success"=>false,"message"=>"All fields are required"]);
}

$conn->close();
?>
