<?php include "header.php"; ?>
<div class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0" style="border-radius: 20px;">
                <div class="card-body p-5">
                    <div class="text-center mb-4 text-primary">
                        <i class="bi bi-bicycle display-1"></i>
                        <h3 class="fw-bold mt-2">Delivery Partner Login</h3>
                    </div>
                    <form action="../routes/auth_web.php" method="POST">
                        <input type="hidden" name="action" value="delivery_login">   
                        <div class="mb-3">
                            <label class="fw-bold small">Email ID</label>
                            <input type="email" name="email" class="form-control" placeholder="rider@startcure.com" required>
                        </div>   
                        <div class="mb-3">
                            <label class="fw-bold small">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>     
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">LOGIN TO DASHBOARD</button>
                    </form>
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0 text-muted small">Don't have a delivery account?</p>
                        <a href="rider_signup.php" class="btn btn-outline-primary btn-sm mt-2 fw-bold px-4">
                            <i class="bi bi-person-plus-fill me-1"></i> Register as a Rider
                        </a>
                    </div>
                    </div>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>