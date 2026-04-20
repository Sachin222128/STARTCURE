<?php
// Session check and Security headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 
// session_regenerate_id(true); 
$base_url = "http://localhost/STARTCURE/";
// Roles check pehle hi kar lete hain
$isAdmin = isset($_SESSION['admin_logged_in']);
$isCustomer = isset($_SESSION['customer_id']);
$isRider = isset($_SESSION['dboy_id']);
// PAGE LEVEL PROTECTION 
$current_page = basename($_SERVER['PHP_SELF']);
// List of protected pages
// ADDED: admin_payouts.php to protected list
$protected_pages = ['dashboard.php', 'admin_riders.php', 'admin_payouts.php', 'book.php', 'customer_profile.php', 'my_bookings.php', 'delivery_dashboard.php'];
if (in_array($current_page, $protected_pages)) {
    if (!$isAdmin && !$isCustomer && !$isRider) {
        header("Location: " . $base_url . "views/login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Startcure Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; }
        body { display: flex; flex-direction: column; background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-content { flex: 1 0 auto; }
        .navbar-brand { font-size: 1.5rem; letter-spacing: 1px; }
        .footer { flex-shrink: 0; background-color: #212529; color: white; padding: 20px 0; }
        .nav-link { font-weight: 500; }
        /* Dropdown style improvement */
        .dropdown-menu { border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
</head>
<body>
<div class="main-content">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $base_url; ?>index.php">
            <i class="bi bi-box-seam text-warning"></i> STARTCURE
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>views/about.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>views/services.php">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>views/shipping_calc.php">Calculator</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>views/faq.php">FAQ</a>
                </li>
                <?php if($isAdmin): ?>
                    <li class="nav-item ms-lg-3">
                        <a href="<?php echo $base_url; ?>views/dashboard.php" class="nav-link text-info fw-bold">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>views/admin_riders.php" class="btn btn-outline-warning btn-sm ms-lg-2 px-3 rounded-pill">
                            <i class="bi bi-people"></i> Manage Riders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>views/admin_payouts.php" class="btn btn-outline-success btn-sm ms-lg-2 px-3 rounded-pill">
                            <i class="bi bi-cash-stack"></i> Payout Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>views/support.php" class="btn btn-outline-light btn-sm ms-lg-2 px-3 rounded-pill border-info text-info">
                            <i class="bi bi-ticket-perforated"></i> Support Tickets
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="<?php echo $base_url; ?>routes/logout.php" class="btn btn-danger btn-sm px-3 rounded-pill">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                <?php elseif($isCustomer): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>views/book.php">Book Now</a>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle btn btn-outline-light px-3 rounded-pill" href="#" id="userDrop" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> Hi, <?php echo explode(' ', $_SESSION['customer_name'] ?? 'User')[0]; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow mt-2">
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>views/customer_profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>views/my_bookings.php"><i class="bi bi-list-check me-2"></i>My Bookings</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>views/support.php"><i class="bi bi-headset me-2"></i>Help & Support</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $base_url; ?>routes/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php elseif($isRider): ?>
                    <li class="nav-item">
                        <a href="<?php echo $base_url; ?>views/delivery_dashboard.php" class="nav-link text-info fw-bold">Rider Panel</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="<?php echo $base_url; ?>routes/logout.php" class="btn btn-danger btn-sm px-3 rounded-pill">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-2">
                        <a href="<?php echo $base_url; ?>views/login.php?type=customer" class="nav-link">Login</a>
                    </li>      
                    <li class="nav-item ms-lg-2">
                        <a href="<?php echo $base_url; ?>views/signup.php" class="btn btn-warning fw-bold px-4 rounded-pill">Sign Up</a>
                    </li>
                    <li class="nav-item ms-lg-4 border-start ps-lg-3 d-flex gap-2">
                        <a href="<?php echo $base_url; ?>views/login.php?type=rider" class="btn btn-outline-info btn-sm rounded-pill px-3">Rider Login</a>
                        <a href="<?php echo $base_url; ?>views/rider_signup.php" class="btn btn-info btn-sm rounded-pill px-3">Rider Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>