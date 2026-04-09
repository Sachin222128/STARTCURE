<?php
include "../app/db_connection.php"; 
session_start();
//DEBUGGING  
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
// 1. SHIPMENT BOOKING (Customer/User) 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['type']) && $_POST['type'] == 'add_shipment') {
    // CUSTOMER ID 
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id'] ?? $_SESSION['customer_id'] ?? 0);
    // SENDER DATA
    $s_name     = mysqli_real_escape_string($conn, $_POST['s_name'] ?? '');
    $s_mobile   = mysqli_real_escape_string($conn, $_POST['s_mobile'] ?? '');
    $s_email    = mysqli_real_escape_string($conn, $_POST['s_email'] ?? '');
    $origin     = mysqli_real_escape_string($conn, $_POST['origin'] ?? '');
    $s_pincode  = mysqli_real_escape_string($conn, $_POST['s_pincode'] ?? '');
    $s_state    = mysqli_real_escape_string($conn, $_POST['s_state'] ?? '');
    $s_city     = mysqli_real_escape_string($conn, $_POST['s_district'] ?? ''); 
    $s_country  = mysqli_real_escape_string($conn, $_POST['s_country'] ?? 'India');
    // RECEIVER DATA
    $r_name     = mysqli_real_escape_string($conn, $_POST['r_name'] ?? '');
    $r_mobile   = mysqli_real_escape_string($conn, $_POST['r_mobile'] ?? '');
    $r_email    = mysqli_real_escape_string($conn, $_POST['r_email'] ?? ''); 
    $dest       = mysqli_real_escape_string($conn, $_POST['dest'] ?? '');
    $r_pincode  = mysqli_real_escape_string($conn, $_POST['r_pincode'] ?? '');
    $r_state    = mysqli_real_escape_string($conn, $_POST['r_state'] ?? '');
    $r_city     = mysqli_real_escape_string($conn, $_POST['r_district'] ?? '');
    $r_country  = mysqli_real_escape_string($conn, $_POST['r_country'] ?? 'India');
    // PACKAGE DATA
    $item_name  = mysqli_real_escape_string($conn, $_POST['item_name'] ?? '');
    $item_size  = mysqli_real_escape_string($conn, $_POST['item_size'] ?? '');
    $weight     = mysqli_real_escape_string($conn, $_POST['weight'] ?? 0);
    $amount     = mysqli_real_escape_string($conn, $_POST['amount'] ?? 0);
    $payment_mode = mysqli_real_escape_string($conn, $_POST['payment_mode'] ?? 'COD');
    if ($amount <= 0) {
        echo "Error: Invalid Amount.";
        exit();
    }
    $tracking_id  = "SC" . rand(100000, 999999); 
    $payment_status = ($payment_mode == 'Online') ? 'QR Pending' : 'Pending (COD)';
    $sql = "INSERT INTO shipments (
                customer_id, tracking_id, sender_name, sender_mobile, sender_email, origin, 
                sender_pincode, sender_state, sender_city, sender_country,
                receiver_name, receiver_mobile, receiver_email, destination, 
                receiver_pincode, receiver_state, receiver_city, receiver_country,
                item_name, item_size, weight, amount, price, status, payment_status
            ) VALUES (
                '$customer_id', '$tracking_id', '$s_name', '$s_mobile', '$s_email', '$origin', 
                '$s_pincode', '$s_state', '$s_city', '$s_country',
                '$r_name', '$r_mobile', '$r_email', '$dest', 
                '$r_pincode', '$r_state', '$r_city', '$r_country',
                '$item_name', '$item_size', '$weight', '$amount', '$amount', 'Booked', '$payment_status'
            )";
    if ($conn->query($sql) === TRUE) {
        $conn->query("INSERT INTO shipment_logs (tracking_id, status, description) 
                      VALUES ('$tracking_id', 'Booked', 'Shipment booked via $payment_mode')");
        echo "Success! Tracking ID: $tracking_id";
        exit();
    } else {
        echo "Error: " . $conn->error;
        exit();
    }
}
// 2. UPDATE SHIPMENT (Admin Dashboard) 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['type']) && $_POST['type'] == 'update_status_admin') { 
    $shipment_id = mysqli_real_escape_string($conn, $_POST['shipment_id']);
    $dboy_id     = mysqli_real_escape_string($conn, $_POST['delivery_boy_id']);
    $new_status  = mysqli_real_escape_string($conn, $_POST['new_status']);
    $new_pay     = mysqli_real_escape_string($conn, $_POST['new_payment_status']);
    $sql = "UPDATE shipments SET 
            delivery_boy_id = '$dboy_id', 
            status = '$new_status', 
            payment_status = '$new_pay' 
            WHERE id = '$shipment_id'";
    if ($conn->query($sql) === TRUE) {
        $conn->query("INSERT INTO shipment_logs (tracking_id, status, description) 
                      SELECT tracking_id, '$new_status', 'Status updated by Admin to $new_status' 
                      FROM shipments WHERE id = '$shipment_id'");
        header("Location: ../views/dashboard.php?update=success");
        exit();
    } else {
        echo "Error: " . $conn->error;
        exit();
    }
}
// 3. DELIVERY UPDATE (Rider App)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['type']) && $_POST['type'] == 'update_delivery_status') {  
    $shipment_id = mysqli_real_escape_string($conn, $_POST['shipment_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']); 
    if ($status == 'Delivered') {
        $ship_query = "SELECT delivery_boy_id, amount, tracking_id FROM shipments WHERE id = '$shipment_id'";
        $ship_res = $conn->query($ship_query);
        $ship_data = $ship_res->fetch_assoc();
        
        $rider_id = $ship_data['delivery_boy_id'];
        $order_amount = $ship_data['amount'];
        $tracking_id = $ship_data['tracking_id'];

        $fuel_fee = 20;
        $comm_fee = $order_amount * 0.05;
        $bonus = ($order_amount > 1500) ? 30 : 0;
        
        $total_payout = $fuel_fee + $comm_fee + $bonus;
        $desc_text = "Earned: ₹$fuel_fee (Fuel) + ₹" . number_format($comm_fee, 2) . " (5% Comm)";
        if($bonus > 0) { $desc_text .= " + ₹$bonus (High-Value Bonus)"; }
        
        $desc = mysqli_real_escape_string($conn, $desc_text);

        $conn->query("UPDATE delivery_boys SET wallet_balance = wallet_balance + $total_payout WHERE id = '$rider_id'");
        $conn->query("INSERT INTO rider_transactions (rider_id, shipment_id, amount, type, description) 
                      VALUES ('$rider_id', '$shipment_id', '$total_payout', 'Credit', '$desc')");
        
        // FIX: Update payment_status to 'Paid' automatically when status is 'Delivered'
        $sql = "UPDATE shipments SET status = '$status', payment_status = 'Paid' WHERE id = '$shipment_id'";
    } else {
        $sql = "UPDATE shipments SET status = '$status' WHERE id = '$shipment_id'";
    }
    if ($conn->query($sql) === TRUE) {
        $conn->query("INSERT INTO shipment_logs (tracking_id, status, description) 
                      SELECT tracking_id, '$status', 'Status updated by Rider to $status' 
                      FROM shipments WHERE id = '$shipment_id'");
        header("Location: ../views/delivery_dashboard.php?status=updated");
        exit();
    } else {
        echo "Error updating status: " . $conn->error;
        exit();
    }
}
// 4. WITHDRAWAL REQUEST (STARTCURE EXCLUSIVE - WITH UPDATED SECURITY CHECK)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['type']) && $_POST['type'] == 'request_withdrawal') {
    $dboy_id = $_SESSION['dboy_id']; 
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $upi_id = mysqli_real_escape_string($conn, $_POST['upi_id']);
    // Fetch current wallet balance safely
    $check_q = "SELECT wallet_balance FROM delivery_boys WHERE id = '$dboy_id'";
    $row = mysqli_fetch_assoc(mysqli_query($conn, $check_q));
    $current_balance = $row['wallet_balance'];
    // BACKEND SECURITY: Check for Min Limit (100) AND available balance
    if ($amount < 100) {
        header("Location: ../views/delivery_dashboard.php?withdraw_msg=Error: Minimum ₹100 is required!");
        exit();
    }
    if ($current_balance >= $amount) {
        $insert_sql = "INSERT INTO withdrawal_requests (rider_id, amount, upi_id, status) 
                       VALUES ('$dboy_id', '$amount', '$upi_id', 'Pending')";
        if (mysqli_query($conn, $insert_sql)) {
            header("Location: ../views/delivery_dashboard.php?withdraw_msg=Success: Request Sent to Admin!");
        } else {
            header("Location: ../views/delivery_dashboard.php?withdraw_msg=Error: Database Connection Failed");
        }
    } else {
        header("Location: ../views/delivery_dashboard.php?withdraw_msg=Error: Insufficient Balance in your Wallet.");
    }
    exit();
}
?>