<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// 1. Session check (Security)
if(!isset($_SESSION['dboy_id'])) { 
    header("Location: delivery_login.php"); 
    exit(); 
}
// 2. Database Connection
if (file_exists("../app/db_connection.php")) {
    include "../app/db_connection.php";
} else {
    die("Error: db_connection.php file nahi mili!");
}
include "header.php"; 
$dboy_id = $_SESSION['dboy_id'];
// 3. Fetch Rider Wallet Info 
$rider_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT wallet_balance FROM delivery_boys WHERE id = '$dboy_id'"));
$wallet_balance = $rider_data['wallet_balance'] ?? 0.00;
// 4. Today's Earnings Logic
$today = date('Y-m-d');
$today_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM rider_transactions WHERE rider_id = '$dboy_id' AND DATE(created_at) = '$today'"));
$today_earnings = $today_data['total'] ?? 0.00;
// 5. Payout Validation (Min ₹100)
$min_payout = 100;
$can_withdraw = ($wallet_balance >= $min_payout);
$disabled_attr = !$can_withdraw ? 'disabled' : '';
?>
<style>
    body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
    /* Stats Cards */
    .card-balance { background: linear-gradient(45deg, #0d6efd, #004fb1); color: white; border: none; border-radius: 15px; }
    .card-today { background: linear-gradient(45deg, #198754, #11633d); color: white; border: none; border-radius: 15px; }
    /* Toggle Buttons Style */
    .nav-btn-custom { 
        background: white; border: 1px solid #dee2e6; padding: 12px 2px; 
        border-radius: 12px; font-weight: 600; font-size: 13px; color: #555;
        transition: all 0.3s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .btn-active { background: #0d6efd !important; color: white !important; border-color: #0d6efd !important; }
    .btn-active i { color: white !important; }
    /* Hidden Sections */
    .toggle-section { display: none; animation: slideDown 0.3s ease-out; }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    /* Tables & Tasks */
    .content-card { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); background: white; overflow: hidden; }
    .task-card { border-radius: 15px; border: none; border-left: 5px solid #0d6efd; background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.05); }
</style>
<div class="container mt-4 pb-5">
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-0">Namaste, <?php echo explode(' ', $_SESSION['dboy_name'])[0]; ?> 🛵</h4>
        <p class="text-muted small">StartCure Logistics Delivery Partner</p>
    </div>
    <div class="row g-3 mb-4 text-center">
        <div class="col-6">
            <div class="card card-balance p-3 h-100 shadow-sm">
                <small class="text-uppercase fw-bold opacity-75" style="font-size: 9px; letter-spacing: 0.5px;">Available Balance</small>
                <h3 class="fw-bold mb-0">₹<?php echo number_format($wallet_balance, 2); ?></h3>
            </div>
        </div>
        <div class="col-6">
            <div class="card card-today p-3 h-100 shadow-sm">
                <small class="text-uppercase fw-bold opacity-75" style="font-size: 9px; letter-spacing: 0.5px;">Today's Earnings</small>
                <h3 class="fw-bold mb-0">₹<?php echo number_format($today_earnings, 2); ?></h3>
            </div>
        </div>
    </div>
    <div class="row g-2 mb-4">
        <div class="col-4">
            <button class="btn nav-btn-custom w-100" onclick="smartToggle('earn-box', this)">
                <i class="bi bi-graph-up d-block mb-1 text-primary"></i> Earnings
            </button>
        </div>
        <div class="col-4">
            <button class="btn nav-btn-custom w-100" onclick="smartToggle('pay-box', this)">
                <i class="bi bi-plus-circle d-block mb-1 text-primary"></i> Payout
            </button>
        </div>
        <div class="col-4">
            <button class="btn nav-btn-custom w-100" onclick="smartToggle('hist-box', this)">
                <i class="bi bi-clock-history d-block mb-1 text-primary"></i> History
            </button>
        </div>
    </div>
    <div class="dynamic-sections">
        <div id="earn-box" class="toggle-section mb-4">
            <div class="card content-card">
                <div class="card-header bg-white fw-bold py-3 text-primary border-0">Recent Earnings History</div>
                <div class="table-responsive text-center">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr><th>Order</th><th>Amount</th><th>Details</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php 
                            $trans = mysqli_query($conn, "SELECT * FROM rider_transactions WHERE rider_id = '$dboy_id' AND type='Credit' ORDER BY id DESC LIMIT 5");
                            if(mysqli_num_rows($trans) > 0):
                                while($t = mysqli_fetch_assoc($trans)): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border">#<?php echo $t['shipment_id']; ?></span></td>
                                    <td class="text-success fw-bold">₹<?php echo number_format($t['amount'], 2); ?></td>
                                    <td class="text-muted" style="font-size: 11px;"><?php echo $t['description']; ?></td> 
                                    <td><?php echo date('d M', strtotime($t['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="4" class="py-3 text-muted">No transactions found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div id="pay-box" class="toggle-section mb-4">
            <div class="card content-card p-4">
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-wallet2 me-2"></i>Request Withdrawal</h6>
                <form action="../routes/web.php" method="POST">
                    <input type="hidden" name="type" value="request_withdrawal">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted">Withdraw Amount</label>
                            <input type="number" name="amount" class="form-control border-2 rounded-3" placeholder="Min ₹100" min="100" max="<?php echo $wallet_balance; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted">UPI ID</label>
                            <input type="text" name="upi_id" class="form-control border-2 rounded-3" placeholder="example@upi" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill mt-3 fw-bold py-2 shadow-sm" <?php echo $disabled_attr; ?>>SEND REQUEST</button>
                    <?php if(!$can_withdraw): ?>
                        <div class="text-danger small mt-2 text-center fw-bold"><i class="bi bi-info-circle me-1"></i> Balance low (Need ₹100)</div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <div id="hist-box" class="toggle-section mb-4">
            <div class="card content-card">
                <div class="card-header bg-dark text-white fw-bold py-3 text-center border-0">My Payout Status</div>
                <div class="table-responsive text-center">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php 
                            $withdrawals = mysqli_query($conn, "SELECT * FROM withdrawal_requests WHERE rider_id = '$dboy_id' ORDER BY id DESC LIMIT 5");
                            if(mysqli_num_rows($withdrawals) > 0):
                                while($w = mysqli_fetch_assoc($withdrawals)): 
                                    $s_badge = ($w['status'] == 'Approved') ? 'bg-success' : 'bg-warning text-dark';
                                ?>
                                <tr>
                                    <td class="fw-bold text-dark">₹<?php echo number_format($w['amount'], 2); ?></td>
                                    <td><span class="badge <?php echo $s_badge; ?> rounded-pill px-3"><?php echo $w['status']; ?></span></td>
                                    <td class="text-muted"><?php echo date('d M', strtotime($w['request_date'])); ?></td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="3" class="py-3 text-muted">No withdrawal history.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <h5 class="fw-bold mb-3 border-bottom pb-2 mt-4"><i class="bi bi-box-seam me-2 text-primary"></i> Assigned Deliveries</h5>
    <div class="row">
        <?php
        $query = "SELECT * FROM shipments WHERE delivery_boy_id = '$dboy_id' AND status != 'Delivered' ORDER BY id DESC";
        $res = $conn->query($query);
        if($res && $res->num_rows > 0) {
            while($row = $res->fetch_assoc()) {
                $status_clr = ($row['status'] == 'Picked Up') ? 'bg-info text-white' : 'bg-warning text-dark';
                ?>
                <div class='col-md-6 mb-4'>
                    <div class='card task-card p-4'>
                        <div class="d-flex justify-content-between mb-3">
                            <h6 class="fw-bold text-primary mb-0">#<?php echo $row['tracking_id']; ?></h6>
                            <span class="badge <?php echo $status_clr; ?> rounded-pill px-3 py-2 small fw-bold"><?php echo $row['status']; ?></span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?php echo $row['receiver_name']; ?></h5>
                        <p class="text-muted small mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?php echo $row['destination']; ?></p>
                        
                        <div class="bg-light p-3 rounded-4 mb-4 d-flex justify-content-between align-items-center border">
                            <div>
                                <small class="text-muted fw-bold d-block" style="font-size: 9px;">COD AMOUNT</small>
                                <h3 class="mb-0 fw-bold text-primary">₹<?php echo number_format($row['amount'], 2); ?></h3>
                            </div>
                            <span class="badge bg-white text-dark border px-2 py-1 small"><?php echo $row['payment_status']; ?></span>
                        </div>
                        <div class="row g-2">
                            <div class="col-6"><a href="tel:<?php echo $row['receiver_mobile']; ?>" class="btn btn-outline-danger w-100 rounded-pill fw-bold py-2 btn-sm">Call Now</a></div>
                            <div class="col-6">
                                <form action='../routes/web.php' method='POST'>
                                    <input type='hidden' name='type' value='update_delivery_status'>
                                    <input type='hidden' name='shipment_id' value='<?php echo $row['id']; ?>'>  
                                    <button name='status' value='<?php echo ($row['status'] != 'Picked Up') ? "Picked Up" : "Delivered"; ?>' class='btn btn-primary w-100 rounded-pill fw-bold py-2 btn-sm'>
                                        <?php echo ($row['status'] != 'Picked Up') ? "PICK UP" : "DELIVERED"; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<div class='text-center py-5'><i class='bi bi-check-circle display-4 text-muted opacity-25'></i><p class='text-muted mt-2 fw-bold'>All orders delivered!</p></div>";
        }
        ?>
    </div>
</div>
<script>
    function smartToggle(secId, btn) {
        const target = document.getElementById(secId);
        const alreadyVisible = (target.style.display === 'block');
        // 1. Sabhi sections ko hide karo aur buttons reset karo
        document.querySelectorAll('.toggle-section').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.nav-btn-custom').forEach(b => b.classList.remove('btn-active'));
        // 2. Agar button pehli baar daba hai, toh show karo
        if (!alreadyVisible) {
            target.style.display = 'block';
            btn.classList.add('btn-active');
        }
    }
</script>
<?php include "footer.php"; ?>