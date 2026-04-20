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
    // 3. STARTCURE PAYOUT APPROVAL
    else if (isset($_POST['approve_payout'])) {
        if (!isset($_SESSION['admin_logged_in'])) {
            die("Unauthorized access!");
        }
        $req_id = mysqli_real_escape_string($conn, $_POST['req_id']);
        $rider_id = mysqli_real_escape_string($conn, $_POST['rider_id']);
        $amount = mysqli_real_escape_string($conn, $_POST['amount']);
        $sql1 = "UPDATE withdrawal_requests SET status = 'Approved' WHERE id = '$req_id'";
        $sql2 = "UPDATE delivery_boys SET wallet_balance = wallet_balance - $amount WHERE id = '$rider_id'";
        $desc = mysqli_real_escape_string($conn, "Withdrawal of ₹$amount Approved & Paid");
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
    // 4. ADD SERVICE (NEW)
    else if (isset($_POST['add_service'])) {
        if (!isset($_SESSION['admin_logged_in'])) { die("Access Denied!"); }
        $name = mysqli_real_escape_string($conn, $_POST['service_name']);
        $desc = mysqli_real_escape_string($conn, $_POST['description']);
        $conn->query("INSERT INTO services (service_name, description) VALUES ('$name', '$desc')");
        header("Location: ../views/dashboard.php?update=success");
        exit();
    }
    // 5. ADD FAQ (NEW)
    else if (isset($_POST['add_faq'])) {
        if (!isset($_SESSION['admin_logged_in'])) { die("Access Denied!"); }
        $q = mysqli_real_escape_string($conn, $_POST['question']);
        $a = mysqli_real_escape_string($conn, $_POST['answer']);
        $conn->query("INSERT INTO faqs (question, answer) VALUES ('$q', '$a')");
        header("Location: ../views/dashboard.php?update=success");
        exit();
    }
} else {
    echo "Direct access not allowed!";
}
?>