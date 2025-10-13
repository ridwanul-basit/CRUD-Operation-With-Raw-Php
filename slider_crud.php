<?php
session_start();
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'db.php';

// Simple admin check (optional for GET)
if(!isset($_SESSION['admin_logged_in'])){
    // Comment this if you want guests to see slider
    // echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    // exit();
}

// GET all slider images
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $res = $conn->query("SELECT * FROM slider_images ORDER BY created_at DESC");
    $images = [];
    while($row = $res->fetch_assoc()){
        $row['image_url'] = "http://localhost/college_api/uploads/" . $row['image_path'];
        $images[] = $row;
    }
    echo json_encode(["success"=>true,"images"=>$images]);
    exit();
}

// POST -> add, update, delete logic remains the same
$uploadDir = "uploads/";
$data = $_POST;

if(isset($data['action']) && $data['action'] === 'add'){
    $desc = $conn->real_escape_string($data['description']);
    $fileName = "";
    if(isset($_FILES['image'])){
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($fileTmp, $uploadDir.$fileName);
    }
    $conn->query("INSERT INTO slider_images (image_path, description) VALUES ('$fileName', '$desc')");
    echo json_encode(["success"=>true,"message"=>"Slider added"]);
    exit();
}

if(isset($data['action']) && $data['action'] === 'update'){
    $id = (int)$data['id'];
    $desc = $conn->real_escape_string($data['description']);
    $sql = "UPDATE slider_images SET description='$desc'";
    if(isset($_FILES['image'])){
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($fileTmp, $uploadDir.$fileName);
        $sql .= ", image_path='$fileName'";
    }
    $sql .= " WHERE id=$id";
    $conn->query($sql);
    echo json_encode(["success"=>true,"message"=>"Slider updated"]);
    exit();
}

if(isset($data['action']) && $data['action'] === 'delete'){
    $ids = explode(",", $data['ids']);
    foreach($ids as $id){
        $conn->query("DELETE FROM slider_images WHERE id=".(int)$id);
    }
    echo json_encode(["success"=>true,"message"=>"Slider(s) deleted"]);
    exit();
}

$conn->close();
?>
