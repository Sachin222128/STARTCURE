<?php
include('db_connection.php'); // Ensure path is correct

if(isset($_POST['pincode'])) {
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $query = mysqli_query($conn, "SELECT price_per_kg, location FROM rate_chart WHERE pincode = '$pincode'");
    
    if(mysqli_num_rows($query) > 0) {
        echo json_encode(['status' => 'found', 'data' => mysqli_fetch_assoc($query)]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
}
?>