<?php
session_start();

// ------------------ CORS HEADERS ------------------
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ------------------ DATABASE ------------------
include 'db.php';

// ------------------ GET About Us ------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $res = $conn->query("SELECT * FROM about_us ORDER BY id DESC LIMIT 1");
    $about = $res->fetch_assoc();

    if ($about) {
        // fetch images
        $imagesRes = $conn->query("SELECT * FROM about_us_images WHERE about_id=" . $about['id']);
        $images = [];
        while($row = $imagesRes->fetch_assoc()){
            $images[] = $row;
        }
        $about['images'] = $images;

        echo json_encode(["success"=>true,"about"=>$about]);
    } else {
        echo json_encode(["success"=>true,"about"=>null]);
    }
    exit();
}

// ------------------ POST -> Save About Us ------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $short_description = $conn->real_escape_string($_POST['short_description'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');

    // Check if there is already an entry
    $res = $conn->query("SELECT * FROM about_us ORDER BY id DESC LIMIT 1");
    $about = $res->fetch_assoc();

    if ($about) {
        $about_id = $about['id'];
        $conn->query("UPDATE about_us SET short_description='$short_description', description='$description' WHERE id=$about_id");
    } else {
        $conn->query("INSERT INTO about_us (short_description, description) VALUES ('$short_description', '$description')");
        $about_id = $conn->insert_id;
    }

    // Handle uploaded images
    $uploadDir = "uploads/about_us/";
    if(isset($_FILES['images'])){
        foreach($_FILES['images']['tmp_name'] as $idx => $tmpName){
            $fileName = time().'_'.$_FILES['images']['name'][$idx];
            move_uploaded_file($tmpName, $uploadDir.$fileName);
            $conn->query("INSERT INTO about_us_images (about_id, image_path) VALUES ($about_id, '$fileName')");
        }
    }

    echo json_encode(["success"=>true,"message"=>"About Us saved successfully"]);
    exit();
}

$conn->close();
?>
