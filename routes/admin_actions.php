<?php
// STARTCURE Enterprise Admin Actions Router Controller
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../app/db_connection.php";

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 1. ADMIN LOGIN (Strict Verification) ---
    if (isset($_POST['username']) && isset($_POST['password']) && !isset($_POST['rider_id']) && !isset($_POST['approve_payout'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password']; 
        
        // Strict system credentials check matrix
        if ($username === "admin" && $password === "admin123") {
            $_SESSION['admin_logged_in'] = true;
            header("Location: ../views/dashboard.php"); 
            exit();
        } else {
            echo "<script>alert('Wrong Username or Password!'); window.location='../views/admin.php';</script>";
            exit();
        }
    }
    
    // --- 2. RIDER APPROVAL (Prepared Statement Implementation) ---
    else if (isset($_POST['rider_id']) && isset($_POST['status']) && !isset($_POST['approve_payout'])) {
        if (!isset($_SESSION['admin_logged_in'])) {
            echo "<script>alert('Access Denied!'); window.location='../views/admin.php';</script>";
            exit();
        }
        
        $id = intval($_POST['rider_id']);
        $status = trim($_POST['status']);
        
        $stmt = $conn->prepare("UPDATE delivery_boys SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            echo "<script>
                alert('Rider status updated to $status successfully!');
                window.location.href='../views/admin_riders.php';
            </script>";
            exit();
        } else {
            die("Execution Fatal Error: Unable to update rider registration parameters.");
        }
    }
    
    // --- 3. STARTCURE PAYOUT APPROVAL (Atomic Secure Ledger Updates) ---
    else if (isset($_POST['approve_payout'])) {
        if (!isset($_SESSION['admin_logged_in'])) {
            die("Unauthorized access attempt flagged!");
        }
        
        $req_id = intval($_POST['req_id']);
        $rider_id = intval($_POST['rider_id']);
        $amount = floatval($_POST['amount']);
        
        // Strict Transaction Block Simulation to prevent race-conditions
        $conn->begin_transaction();
        try {
            // Update withdrawal status
            $stmt1 = $conn->prepare("UPDATE withdrawal_requests SET status = 'Approved' WHERE id = ?");
            $stmt1->bind_param("i", $req_id);
            $stmt1->execute();
            
            // Deduct Wallet Balance securely
            $stmt2 = $conn->prepare("UPDATE delivery_boys SET wallet_balance = wallet_balance - ? WHERE id = ?");
            $stmt2->bind_param("di", $amount, $rider_id);
            $stmt2->execute();
            
            // Log into Financial Transactions Audit Trail
            $desc = "Withdrawal of ₹" . number_format($amount, 2) . " Approved & Paid";
            $zero_shipment = 0;
            $type_debit = "Debit";
            
            $stmt3 = $conn->prepare("INSERT INTO rider_transactions (rider_id, shipment_id, amount, type, description) VALUES (?, ?, ?, ?, ?)");
            $stmt3->bind_param("iidss", $rider_id, $zero_shipment, $amount, $type_debit, $desc);
            $stmt3->execute();
            
            $conn->commit();
            
            echo "<script>
                alert('Payout Approved! Wallet updated.');
                window.location.href='../views/admin_payouts.php';
            </script>";
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            die("Transaction Error: Data Integrity Protection Rollback Executed.");
        }
    }
    
    // --- 4. ADD SERVICE (Strict Sanitization Check) ---
    else if (isset($_POST['add_service'])) {
        if (!isset($_SESSION['admin_logged_in'])) { die("Access Denied!"); }
        
        $name = htmlspecialchars(trim($_POST['service_name']));
        $desc = htmlspecialchars(trim($_POST['description']));
        
        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO services (service_name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $desc);
            $stmt->execute();
            header("Location: ../views/dashboard.php?update=success");
            exit();
        } else {
            die("Validation Mismatch: Service name field parameters missing.");
        }
    }
    
    // --- 5. ADD FAQ (Prepared Statement Upgrade) ---
    else if (isset($_POST['add_faq'])) {
        if (!isset($_SESSION['admin_logged_in'])) { die("Access Denied!"); }
        
        $q = htmlspecialchars(trim($_POST['question']));
        $a = htmlspecialchars(trim($_POST['answer']));
        
        if (!empty($q) && !empty($a)) {
            $stmt = $conn->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)");
            $stmt->bind_param("ss", $q, $a);
            $stmt->execute();
            header("Location: ../views/dashboard.php?update=success");
            exit();
        } else {
            die("Validation Mismatch: Mandatory parameters constraint cannot be null.");
        }
    }
    
    // --- 6. CORE AUTO-EMAIL BACKEND RECEIPT INJECTION FOR DISPATCH SYSTEM ---
    else if (isset($_POST['action']) && $_POST['action'] == 'trigger_manual_receipt') {
        if (!isset($_SESSION['admin_logged_in'])) { die("Access Denied!"); }
        
        include_once "../app/email_helper.php";
        
        $customer_email = trim($_POST['customer_email']);
        $customer_name  = trim($_POST['customer_name']);
        
        $booking_data = [
            'tracking_id' => trim($_POST['tracking_id']),
            'item_name'   => trim($_POST['item_name']),
            'total_price' => floatval($_POST['total_price'])
        ];
        
        if (!empty($customer_email) && !empty($booking_data['tracking_id'])) {
            sendAutoEmailReceipt($customer_email, $customer_name, $booking_data);
            header("Location: ../views/dashboard.php?email=sent");
            exit();
        } else {
            die("System Flag: Invalid customer parameters mapping failure.");
        }
    }
    
} else {
    echo "Direct access not allowed!";
}
?>