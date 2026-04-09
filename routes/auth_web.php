<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../app/db_connection.php";
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? $_POST['type'] ?? '';  
    // --- 1. CUSTOMER LOGIN ---
    if ($action == 'customer_login') {
        $email = $_POST['email'];
        $pass  = $_POST['password'];
        
        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();  
            if (password_verify($pass, $user['password'])) {
                $_SESSION['customer_id'] = $user['id'];
                $_SESSION['customer_name'] = $user['name'];
                $_SESSION['customer_phone'] = $user['phone'] ?? '';
                header("Location: ../views/book.php");
                exit();
            } else {
                echo "<script>alert('Incorrect password!'); window.location.href='../views/login.php';</script>";
            }
        } else {
            echo "<script>alert('No accounts found!'); window.location.href='../views/login.php';</script>";
        }
    }
    // --- 2. ADMIN LOGIN ---
    else if ($action == 'admin_login') {
        $admin_id = $_POST['admin_id'];
        $pass     = $_POST['password'];  
        
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $admin_id, $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $admin = $res->fetch_assoc();
            if ($pass === $admin['password']) {
                $_SESSION['admin_logged_in'] = true; 
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['username'];
                header("Location: ../views/dashboard.php"); 
                exit();
            } else {
                // UPDATE: Redirecting back to secret admin login instead of public login.php
                echo "<script>alert('Incorrect Admin Password!'); window.location.href='../views/admin_login.php';</script>";
            }
        } else {
            // UPDATE: Redirecting back to secret admin login instead of public login.php
            echo "<script>alert('Admin account not found!'); window.location.href='../views/admin_login.php';</script>";
        }
    }
    //3. DELIVERY BOY LOGIN (Rider)
    else if ($action == 'delivery_login') {
        $email = $_POST['email'];
        $pass  = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM delivery_boys WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $dboy = $res->fetch_assoc();    
            if (password_verify($pass, $dboy['password']) || $pass === $dboy['password']) {         
                if ($dboy['status'] == 'Pending') {
                    echo "<script>alert('Your account is Pending for Admin Approval. Please wait.'); window.location.href='../views/delivery_login.php';</script>";
                    exit();
                } else if ($dboy['status'] == 'Rejected') {
                    echo "<script>alert('Your application has been Rejected. Contact Support.'); window.location.href='../views/delivery_login.php';</script>";
                    exit();
                }
                $_SESSION['dboy_id'] = $dboy['id'];
                $_SESSION['dboy_name'] = $dboy['name'];
                header("Location: ../views/delivery_dashboard.php");
                exit();    
            } else {
                echo "<script>alert('Incorrect Password!'); window.location.href='../views/delivery_login.php';</script>";
            }
        } else {
            echo "<script>alert('Rider account not found!'); window.location.href='../views/delivery_login.php';</script>";
        }
    }
    //4. CUSTOMER SIGNUP 
    else if ($action == 'customer_signup') {
        $full_name = $_POST['first_name'] . " " . $_POST['last_name']; 
        $email     = $_POST['email'];
        $phone     = $_POST['phone'];
        $password  = password_hash($_POST['password'], PASSWORD_DEFAULT); 
        $address   = $_POST['address'];
        $pincode   = $_POST['pincode'];

        $stmt = $conn->prepare("SELECT email FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo "<script>alert('Email already exists! Please login.'); window.location.href='../views/login.php';</script>";
        } else {
            $ins = $conn->prepare("INSERT INTO customers (name, email, phone, password, address, pincode) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->bind_param("ssssss", $full_name, $email, $phone, $password, $address, $pincode);
            if ($ins->execute()) {
                echo "<script>alert('Registration Successful! Please login.'); window.location.href='../views/login.php';</script>";
            }
        }
    }
    //5. RIDER SIGNUP
    else if ($action == 'rider_signup') {
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $rider_code = "STR-" . date("Y") . "-" . rand(1000, 9999);

        $stmt = $conn->prepare("SELECT email FROM delivery_boys WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo "<script>alert('Email already registered as Rider!'); window.location.href = '../views/delivery_login.php';</script>";
        } else {
            $ins = $conn->prepare("INSERT INTO delivery_boys (rider_code, name, phone, email, password, address, city, state, pincode, aadhar_no, license_no, vehicle_type, vehicle_no, bank_acc_no, ifsc_code, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $ins->bind_param("sssssssssssssss", $rider_code, $_POST['name'], $_POST['phone'], $email, $password, $_POST['address'], $_POST['city'], $_POST['state'], $_POST['pincode'], $_POST['aadhar_no'], $_POST['license_no'], $_POST['vehicle_type'], $_POST['vehicle_no'], $_POST['bank_acc'], $_POST['ifsc']);
            if ($ins->execute()) {
                echo "<script>alert('Registration Successful! Your Rider ID is: $rider_code. Please wait for Admin Approval.'); window.location.href = '../views/delivery_login.php';</script>";
            }
        }
    }
    //6. ADMIN RIDER APPROVAL / REJECTION 
    else if ($action == 'update_rider_status') {
        if (!isset($_SESSION['admin_logged_in'])) {
            echo "<script>alert('Access Denied!'); window.location.href='../views/login.php';</script>";
            exit();
        }
        $id = $_POST['rider_id'];
        $status = $_POST['status']; 
        $stmt = $conn->prepare("UPDATE delivery_boys SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Rider status updated to $status successfully!'); window.location.href='../views/admin_riders.php';</script>";
        }
    }
    // 7. CUSTOMER PROFILE UPDATE 
    else if ($action == 'update_profile') {
        $cid = $_SESSION['customer_id'];
        $stmt = $conn->prepare("UPDATE customers SET name=?, phone=?, address=?, district=?, state=?, pincode=? WHERE id=?");
        $stmt->bind_param("ssssssi", $_POST['name'], $_POST['phone'], $_POST['address'], $_POST['district'], $_POST['state'], $_POST['pincode'], $cid);
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error: " . $conn->error;
        }
    }
    //8. FEEDBACK SUBMISSION LOGIC 
    else if ($action == 'submit_feedback') {
        $ship_id = (int)$_POST['shipment_id'];
        $rating = (int)$_POST['rating'];
        $review = htmlspecialchars($_POST['review']); 
        $tid = $_POST['tid'] ?? ''; 

        $stmt = $conn->prepare("UPDATE shipments SET rating = ?, review = ? WHERE id = ?");
        $stmt->bind_param("isi", $rating, $review, $ship_id);
        if ($stmt->execute()) {
            if ($tid == 'my_bookings' || empty($tid)) {
                echo "<script>alert('Thank you for your feedback!'); window.location.href='../views/my_bookings.php';</script>";
            } else {
                echo "<script>alert('Thank you for your feedback!'); window.location.href='../views/track_result.php?tid=$tid';</script>";
            }
        }
    }
    // 9. SUPPORT TICKET SUBMISSION 
    else if ($action == 'submit_ticket') {
        if (!isset($_SESSION['customer_id'])) exit();
        $customer_id = $_SESSION['customer_id'];
        $ship_id = !empty($_POST['shipment_id']) ? $_POST['shipment_id'] : NULL;
        $subject = htmlspecialchars($_POST['subject']);
        $message = htmlspecialchars($_POST['message']);

        $stmt = $conn->prepare("INSERT INTO support_tickets (customer_id, shipment_id, category, subject, message, status) VALUES (?, ?, ?, ?, ?, 'Open')");
        $stmt->bind_param("iisss", $customer_id, $ship_id, $_POST['category'], $subject, $message);
        if ($stmt->execute()) {
            echo "<script>alert('Your support ticket has been raised successfully!'); window.location.href='../views/support.php';</script>";
        }
    }
    // 10. RESOLVE SUPPORT TICKET 
    else if ($action == 'resolve_ticket') {
        if (!isset($_SESSION['admin_logged_in'])) exit();
        $remarks = htmlspecialchars($_POST['admin_remarks']);
        $stmt = $conn->prepare("UPDATE support_tickets SET status = 'Resolved', admin_remarks = ? WHERE id = ?");
        $stmt->bind_param("si", $remarks, $_POST['ticket_id']);
        if ($stmt->execute()) {
            echo "<script>alert('Ticket Resolved with remarks!'); window.location.href='../views/support.php';</script>";
        }
    }
    // 11. NEW: SEND TICKET REPLY (Chat System) 
    else if ($action == 'send_ticket_reply') {
        $ticket_id = (int)$_POST['ticket_id'];
        $sender_type = $_POST['sender_type']; // 'Customer' or 'Admin'
        $message = htmlspecialchars($_POST['reply_msg']);
        $sender_id = ($sender_type == 'Admin') ? ($_SESSION['admin_id'] ?? 0) : ($_SESSION['customer_id'] ?? 0);

        if ($sender_id != 0 && !empty($message)) {
            $stmt = $conn->prepare("INSERT INTO ticket_replies (ticket_id, sender_id, sender_type, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $ticket_id, $sender_id, $sender_type, $message);
            if ($stmt->execute()) {
                header("Location: ../views/support.php?chat=success");
                exit();
            }
        }
    }
} else {
    echo "Direct access not allowed!";
}
?>