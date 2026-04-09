<?php
include "../app/db_connection.php";
$userMsg = $_POST['message'];
$reply = "Sorry, I did not receive your parcel information.";
preg_match('/SC\d+/', $userMsg, $matches);
$tid = $matches[0] ?? '';
if ($tid) {
    $res = $conn->query("SELECT status FROM shipments WHERE tracking_id = '$tid'");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $status = $row['status'];    
        $reply = "Yes, I checked your parcel.(#$tid) abhi *$status* Is on stage. 😊";
    }
}
echo json_encode(['reply' => $reply]);
?>