<?php
include "../app/db_connection.php"; 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; margin-top: 100px; }
        .login-card { max-width: 400px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow login-card mx-auto">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">Admin Login</h4>
            </div>
            <div class="card-body p-4">
                <form action="admin_actions.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="../index.php">← Back to Home</a>
        </div>
    </div>
</body>
</html>