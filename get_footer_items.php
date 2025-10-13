<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
//     echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
//     exit;
// }

$result = $conn->query("SELECT * FROM footer_links ORDER BY id ASC");
$items = [];
while($row = $result->fetch_assoc()){
    $items[] = $row;
}

echo json_encode(["success"=>true,"items"=>$items]);
$conn->close();
