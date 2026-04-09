<?php 
include "header.php"; 
// URL se 'type' uthao, default 'customer' rahega
$type = $_GET['type'] ?? 'customer'; 
?>
<div class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0" style="border-radius: 20px;">
                <div class="card-body p-5">    
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Startcure Login</h3>
                        <p class="text-muted">Please select how you want to login</p>
                    </div>
                    <ul class="nav nav-pills nav-justified mb-4" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo ($type == 'customer') ? 'active' : ''; ?> fw-bold" id="customer-tab" data-bs-toggle="pill" data-bs-target="#customer-form" type="button" role="tab">
                                <i class="bi bi-person me-2"></i>Customer
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo ($type == 'rider') ? 'active' : ''; ?> fw-bold" id="rider-tab" data-bs-toggle="pill" data-bs-target="#rider-form" type="button" role="tab">
                                <i class="bi bi-bicycle me-2"></i>Rider
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade <?php echo ($type == 'customer') ? 'show active' : ''; ?>" id="customer-form" role="tabpanel">
                            <form action="../routes/auth_web.php" method="POST">
                                <input type="hidden" name="action" value="customer_login">
                                <div class="mb-3">
                                    <label class="small fw-bold">Customer Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="customer@mail.com" required>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold">Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold py-2">LOGIN AS CUSTOMER</button>
                            </form>
                        </div>
                        
                        <div class="tab-pane fade <?php echo ($type == 'rider') ? 'show active' : ''; ?>" id="rider-form" role="tabpanel">
                            <form action="../routes/auth_web.php" method="POST">
                                <input type="hidden" name="action" value="delivery_login">
                                <div class="mb-3">
                                    <label class="fw-bold small">Rider Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="rider@mail.com" required>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold small">Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                                </div>
                                <button type="submit" class="btn btn-warning w-100 fw-bold py-2">LOGIN AS RIDER</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>