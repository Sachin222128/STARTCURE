<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "courier_pro"; 

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Opps! There is some technical problem. Please try again later.");
}
?>