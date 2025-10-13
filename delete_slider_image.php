<?php
session_start();
header("Content-Type: application/json");
include 'db.php';

if(!isset($_SESSION['admin_id'])) {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$id = (int)$_GET['id'] ?? 0;
if($id > 0){
    $res = $conn->query("SELECT image_path FROM slider_images WHERE id=$id");
    if($res->num_rows > 0){
        $row = $res->fetch_assoc();
        @unlink($row['image_path']); // delete image file
        $conn->query("DELETE FROM slider_images WHERE id=$id");
        echo json_encode(['success'=>true,'message'=>'Image deleted']);
    } else echo json_encode(['success'=>false,'message'=>'Image not found']);
} else {
    echo json_encode(['success'=>false,'message'=>'Invalid ID']);
}
?>
