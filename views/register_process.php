<?php include "header.php"; ?>
<div class="container my-5">
    <div class="card shadow-lg border-0 mx-auto" style="max-width: 850px; border-radius: 12px;">
        <div class="card-header bg-success text-white p-3 text-center">
            <h4 class="mb-0">New Customer Registration</h4>
        </div>
        <div class="card-body p-4">
            <form action="../routes/web.php" method="POST">
                <input type="hidden" name="type" value="register">    
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter Full Name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Mobile Number</label>
                        <input type="tel" name="mobile" class="form-control" placeholder="10 Digit Mobile" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Email ID</label>
                        <input type="email" name="email" class="form-control" placeholder="customer@example.com" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">State</label>
                        <input type="text" name="state" class="form-control" placeholder="e.g. Maharashtra" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Pincode</label>
                        <input type="text" name="pincode" class="form-control" placeholder="6 Digit Pincode" maxlength="6" required>
                    </div>
                    <div class="col-md-12 mb-4">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Create a password" required>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg shadow-sm fw-bold">
                        REGISTER & GENERATE OTP <i class="bi bi-shield-lock ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>