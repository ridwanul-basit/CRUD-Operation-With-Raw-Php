<?php
session_start();
include 'db.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if(!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']){
    echo json_encode(["success"=>false,"message"=>"Not authorized"]);
    exit;
}

$posts = [];
$postResult = $conn->query("SELECT * FROM posts ORDER BY id DESC");
if($postResult){
    while($post = $postResult->fetch_assoc()){
        // Get comments for this post
        $comments = [];
        $cRes = $conn->query("SELECT * FROM comments WHERE post_id=".$post['id']." ORDER BY id ASC");
        if($cRes){
            while($c = $cRes->fetch_assoc()){
                $comments[] = $c;
            }
        }
        $post['comments'] = $comments;
        $posts[] = $post;
    }
    echo json_encode(["success"=>true,"posts"=>$posts]);
}else{
    echo json_encode(["success"=>false,"message"=>"Database error"]);
}

$conn->close();
?>
