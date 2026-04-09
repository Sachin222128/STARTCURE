<?php include "header.php"; ?>
<?php
// Security check
if(!isset($_GET['tid']) || !isset($_GET['amt'])) {
    echo "<script>window.location='book.php';</script>";
    exit();
}
$tid = htmlspecialchars($_GET['tid']);
$amt = htmlspecialchars($_GET['amt']);
$s_name = htmlspecialchars($_GET['s_name']);
// --- CONFIGURATION ---
$upi_id = "YOUR_UPI_ID@okicici"; 
$merchant_name = "StartCure Logistics"; 
// UPI URL parameters: pa = upi id, pn = name, am = amount, tn = transaction note
$pay_url = "upi://pay?pa=" . $upi_id . "&pn=" . urlencode($merchant_name) . "&am=" . $amt . "&tn=" . urlencode("Booking_" . $tid) . "&cu=INR";
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5 text-center">
            <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">                
                <div class="card-header bg-primary text-white py-4 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Secure Payment Gateway</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3 text-muted small text-uppercase fw-bold">Tracking ID: <span class="text-primary"><?php echo $tid; ?></span></div>   
                    <h1 class="display-5 fw-bold text-dark mb-4">₹<?php echo $amt; ?></h1>    
                    <div class="bg-white p-3 rounded-4 shadow-sm border mb-4 d-inline-block">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?php echo urlencode($pay_url); ?>" 
                             alt="Scan to Pay" class="img-fluid">
                        <div class="mt-2 small fw-bold text-secondary">Scan QR to Pay ₹<?php echo $amt; ?></div>
                    </div>
                    <div class="d-md-none mb-4">
                        <a href="<?php echo $pay_url; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                            <i class="bi bi-phone me-2"></i>Open UPI App
                        </a>
                    </div>
                    <div class="alert alert-warning border-0 small text-start mb-4" style="background-color: #fff9e6;">
                        <ul class="mb-0 ps-3">
                            <li>Complete the payment through the App.</li>
                            <li><strong>Amount: ₹<?php echo $amt; ?></strong> Confirm.</li>
                            <li>After payment, press the "Paid" button below.</li>
                        </ul>
                    </div>
                    <a href="book.php?booking=success&tid=<?php echo $tid; ?>&s_name=<?php echo urlencode($s_name); ?>" 
                       class="btn btn-success btn-lg w-100 fw-bold shadow-sm py-3 mb-3">
                        I HAVE PAID SUCCESSFULLY <i class="bi bi-check-circle ms-2"></i>
                    </a>
                    
                    <a href="book.php" class="text-decoration-none text-muted small">
                        <i class="bi bi-x-circle me-1"></i> Cancel & Go Back
                    </a>
                </div>
                <div class="card-footer bg-light border-0 py-3">
                    <div class="d-flex justify-content-center gap-3 opacity-50">
                        <i class="bi bi-google" title="Google Pay"></i>
                        <i class="bi bi-paypal" title="PhonePe"></i>
                        <i class="bi bi-credit-card-2-front" title="Paytm"></i>
                    </div>
                </div>
            </div>
            <p class="mt-4 text-muted small">
                <i class="bi bi-lock-fill me-1"></i> Your transaction is encrypted and secure.
            </p>
        </div>
    </div>
</div>
<?php include "footer.php"; ?>