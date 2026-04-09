<?php
include "../app/db_connection.php";
session_start();
if(isset($_POST['tid'])) {
    $tid = mysqli_real_escape_string($conn, $_POST['tid']);
    // Payment status 'Paid' update 
    $sql = "UPDATE shipments SET payment_status = 'Paid' WHERE tracking_id = '$tid'";
    if($conn->query($sql)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>