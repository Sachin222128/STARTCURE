<?php
include "../app/db_connection.php";
session_start();

// Strict debugging format
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Rider session secure verification
if (!isset($_SESSION['dboy_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dboy_id = $_SESSION['dboy_id'];
    $lat = mysqli_real_escape_string($conn, $_POST['lat'] ?? '');
    $lng = mysqli_real_escape_string($conn, $_POST['lng'] ?? '');

    if (!empty($lat) && !empty($lng)) {
        // Bina kisi purane column ko touch kiye dynamic coordinates update query
        $sql = "UPDATE delivery_boys SET current_lat = '$lat', current_lng = '$lng' WHERE id = '$dboy_id'";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Empty coordinates received"]);
    }
    exit();
}
?>