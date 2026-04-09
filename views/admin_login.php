<?php 
session_start();
// Agar admin pehle se login hai, toh seedha dashboard bhejo
if(isset($_SESSION['admin_logged_in'])) { 
    header("Location: dashboard.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Startcure Admin - Secret Access</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #1a1a1a; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .admin-card { background: #ffffff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.5); width: 100%; max-width: 400px; overflow: hidden; }
        .card-header-custom { background: #0d6efd; color: white; padding: 30px; text-align: center; }
        .form-control { border-radius: 10px; padding: 12px; border: 1px solid #ddd; }
        .btn-access { border-radius: 10px; padding: 12px; font-weight: bold; letter-spacing: 1px; transition: 0.3s; }
        .btn-access:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4); }
    </style>
</head>
<body>
<div class="admin-card">
    <div class="card-header-custom">
        <i class="bi bi-shield-lock-fill" style="font-size: 3rem;"></i>
        <h4 class="fw-bold mt-2 mb-0">ADMIN PORTAL</h4>
        <p class="small opacity-75">Startcure Logistics Internal System</p>
    </div>
    <div class="card-body p-4">
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger py-2 small text-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <form action="../routes/auth_web.php" method="POST">
            <input type="hidden" name="action" value="admin_login">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted text-uppercase">Admin ID / Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="admin_id" class="form-control border-start-0" placeholder="Enter Admin ID" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-muted text-uppercase">Security Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-access mb-3">
                SECURE LOGIN <i class="bi bi-arrow-right-short ms-1"></i>
            </button>
            <div class="text-center">
                <a href="../index.php" class="text-decoration-none text-muted small">
                    <i class="bi bi-house-door me-1"></i> Return to Public Site
                </a>
            </div>
        </form>
    </div>
</div>
</body>
</html>