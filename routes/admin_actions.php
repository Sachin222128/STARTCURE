<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../app/db_connection.php";
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. ADMIN LOGIN
    if (isset($_POST['username']) && isset($_POST['password']) && !isset($_POST['rider_id']) && !isset($_POST['approve_payout'])) {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = $_POST['password']; 
        if ($username == "admin" && $password == "admin123") {
            $_SESSION['admin_logged_in'] = true;
            header("Location: ../views/dashboard.php"); 
            exit();
        } else {
            echo "<script>alert('Wrong Username or Password!'); window.location='../views/admin.php';</script>";
            exit();
        }
    }
    // 2. RIDER APPROVAL
    else if (isset($_POST['rider_id']) && isset($_POST['status']) && !isset($_POST['approve_payout'])) {
        if (!isset($_SESSION['admin_logged_in'])) {
            echo "<script>alert('Access Denied!'); window.location='../views/admin.php';</script>";
            exit();
        }
        $id = mysqli_real_escape_string($conn, $_POST['rider_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $sql = "UPDATE delivery_boys SET status = '$status' WHERE id = '$id'";
        if (mysqli_query($conn, $sql)) {
            echo "<script>
                alert('Rider status updated to $status successfully!');
                window.location.href='../views/admin_riders.php';
            </script>";
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
    // 3. STARTCURE PAYOUT APPROVAL (Line 64 Fixed)
    else if (isset($_POST['approve_payout'])) {
        if (!isset($_SESSION['admin_logged_in'])) {
            die("Unauthorized access!");
        }
        $req_id = mysqli_real_escape_string($conn, $_POST['req_id']);
        $rider_id = mysqli_real_escape_string($conn, $_POST['rider_id']);
        $amount = mysqli_real_escape_string($conn, $_POST['amount']);
        // A. Update withdrawal request status
        $sql1 = "UPDATE withdrawal_requests SET status = 'Approved' WHERE id = '$req_id'";
        // B. Deduct amount from rider's wallet balance
        $sql2 = "UPDATE delivery_boys SET wallet_balance = wallet_balance - $amount WHERE id = '$rider_id'";
        // C. Log the debit transaction (FIXED: shipment_id = 0 added)
        $desc = mysqli_real_escape_string($conn, "Withdrawal of ₹$amount Approved & Paid");
        // YAHAN FIX HAI: shipment_id column mein '0' bhej rahe hain
        $sql3 = "INSERT INTO rider_transactions (rider_id, shipment_id, amount, type, description) 
                 VALUES ('$rider_id', '0', '$amount', 'Debit', '$desc')";
        if (mysqli_query($conn, $sql1) && mysqli_query($conn, $sql2)) {
            mysqli_query($conn, $sql3); 
            echo "<script>
                alert('Payout Approved! Wallet updated.');
                window.location.href='../views/admin_payouts.php';
            </script>";
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
} else {
    echo "Direct access not allowed!";
}
?>