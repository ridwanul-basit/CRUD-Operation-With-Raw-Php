<?php
header("Content-Type: application/json");
include 'db.php';

$token = $_GET['token'] ?? '';

if ($token == '') {
    echo json_encode(["success"=>false,"message"=>"Invalid token"]);
    exit;
}

// Check token and expiry
$sql = "SELECT * FROM students_details WHERE verify_token='$token' AND verify_expires > UTC_TIMESTAMP()";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $conn->query("UPDATE students_details SET email_verified_at=UTC_TIMESTAMP(), verify_token=NULL, verify_expires=NULL WHERE verify_token='$token'");
    echo json_encode(["success"=>true,"message"=>"Email verified successfully! You can now login."]);
} else {
    echo json_encode(["success"=>false,"message"=>"Invalid or expired token"]);
}

$conn->close();

?>
