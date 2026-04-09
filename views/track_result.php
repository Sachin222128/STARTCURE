<?php 
// 1. Header & DB
include "header.php"; 
include "../app/db_connection.php"; 
// Input 
$tid = isset($_GET['tid']) ? mysqli_real_escape_string($conn, trim($_GET['tid'])) : '';
$shipment = null;
if (!empty($tid)) {
    $sql = "SELECT * FROM shipments 
            WHERE tracking_id = '$tid' 
            OR sender_mobile = '$tid' 
            OR receiver_mobile = '$tid' 
            ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $shipment = $result->fetch_assoc();
    }
}
?>
<style>
    /* Professional Tracking CSS */
    .track-line { display: flex; justify-content: space-between; align-items: center; position: relative; margin: 40px 0; }
    .track-line::before { content: ''; position: absolute; top: 15px; left: 5%; right: 5%; height: 4px; background: #e0e0e0; z-index: 1; }
    .step { position: relative; z-index: 2; text-align: center; width: 25%; }
    .dot { width: 30px; height: 30px; background: #fff; border: 4px solid #e0e0e0; border-radius: 50%; margin: 0 auto 10px; transition: 0.3s; }
    .step.completed .dot { border-color: #f17b21; background: #f17b21; box-shadow: 0 0 10px rgba(241, 123, 33, 0.5); }
    .step-text { font-size: 0.75rem; font-weight: 600; color: #888; text-transform: uppercase; }
    .step.completed .step-text { color: #f17b21; }
    .timeline-item { border-left: 3px solid #f17b21; position: relative; padding-left: 25px; padding-bottom: 25px; }
    .timeline-dot { position: absolute; left: -11px; top: 0; width: 18px; height: 18px; background: #f17b21; border-radius: 50%; border: 3px solid #fff; }
</style>
<div class="container mt-5 py-4">
    <?php if ($shipment): 
        $status = $shipment['status']; 
        $booking_date = !empty($shipment['created_at']) ? $shipment['created_at'] : date('Y-m-d H:i:s');
        $expected_date = date('d M Y', strtotime($booking_date . ' + 4 days'));
        $p_color = ($shipment['payment_status'] == 'Paid') ? 'success' : 'danger';
    ?>  
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h5 class="text-muted mb-1">Tracking ID: <span class="text-dark fw-bold">#<?php echo $shipment['tracking_id']; ?></span></h5>
                        <p class="mb-0">Item: <strong><?php echo $shipment['item_name']; ?></strong> | Mode: <span class="badge bg-light text-dark border"><?php echo $shipment['payment_mode'] ?? 'Standard'; ?></span></p>
                    </div>
                    <div class="col-md-5 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-<?php echo $p_color; ?> p-2 px-3 fs-6">
                             <i class="bi bi-wallet2 me-2"></i>Payment: <?php echo $shipment['payment_status']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-lg border-0 mb-4" style="border-radius: 15px;">
             <div class="card-body p-4">
                 <div class="track-line">
                    <?php 
                        $stages = ['Booked', 'In Transit', 'Out for Delivery', 'Delivered'];
                        $current_idx = array_search($status, $stages);
                        if($current_idx === false && ($status == 'Pending')) $current_idx = 0;
                    ?>
                    <div class="step <?php echo ($current_idx >= 0) ? 'completed' : ''; ?>">
                        <div class="dot"></div><div class="step-text">Ordered</div>
                    </div>
                    <div class="step <?php echo ($current_idx >= 1) ? 'completed' : ''; ?>">
                        <div class="dot"></div><div class="step-text">In Transit</div>
                    </div>
                    <div class="step <?php echo ($current_idx >= 2) ? 'completed' : ''; ?>">
                        <div class="dot"></div><div class="step-text">Out for Delivery</div>
                    </div>
                    <div class="step <?php echo ($current_idx >= 3) ? 'completed' : ''; ?>">
                        <div class="dot"></div><div class="step-text">Delivered</div>
                    </div>
                 </div> 
                 <div class="row text-center mt-4 g-3">
                     <div class="col-6 col-md-4 border-end">
                         <small class="text-muted d-block">Current Status</small>
                         <span class="fw-bold text-primary"><?php echo $status; ?></span>
                     </div>
                     <div class="col-6 col-md-4 border-end">
                         <small class="text-muted d-block">Destination</small>
                         <span class="fw-bold"><?php echo $shipment['destination']; ?></span>
                     </div>
                     <div class="col-12 col-md-4">
                         <small class="text-muted d-block">Est. Delivery</small>
                         <span class="fw-bold text-success"><?php echo ($status == 'Delivered') ? 'Delivered ✅' : $expected_date; ?></span>
                     </div>
                 </div>
             </div>
        </div>
        <?php if ($shipment['status'] == 'Delivered' && empty($shipment['rating'])): ?>
        <div class="card mt-4 border-0 shadow-sm mb-4" style="border-radius: 15px; background: #f8f9fa;">
            <div class="card-body p-4 text-center">
                <h6 class="fw-bold text-dark mb-3">Rate Your Delivery Experience</h6>
                <form action="../routes/auth_web.php" method="POST"> 
                    <input type="hidden" name="action" value="submit_feedback">
                    <input type="hidden" name="shipment_id" value="<?= $shipment['id'] ?>">
                    <input type="hidden" name="tid" value="<?= $shipment['tracking_id'] ?>">   
                    <div class="mb-3">
                        <select name="rating" class="form-select rounded-pill border-primary" required style="max-width: 300px; margin: 0 auto;">
                            <option value="">Select Stars ⭐</option>
                            <option value="5">5 Stars - Excellent</option>
                            <option value="4">4 Stars - Good</option>
                            <option value="3">3 Stars - Average</option>
                            <option value="2">2 Stars - Poor</option>
                            <option value="1">1 Star - Very Bad</option>
                        </select>
                    </div>
                    <textarea name="review" class="form-control mb-3 shadow-sm" rows="2" placeholder="Write a quick review..." style="border-radius: 10px; max-width: 500px; margin: 0 auto;"></textarea>
                    <button type="submit" class="btn btn-primary px-5 rounded-pill shadow">Submit Feedback</button>
                </form>
            </div>
        </div>
        <?php elseif (!empty($shipment['rating'])): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4 text-center" style="border-radius: 15px;">
                <h6 class="mb-1 fw-bold">Your Rating: <?= $shipment['rating'] ?>/5 ⭐</h6>
                <p class="mb-0 small italic text-muted">"<?= htmlspecialchars($shipment['review']) ?>"</p>
            </div>
        <?php endif; ?>
        <div class="card shadow-sm border-0 p-4 mb-4" style="border-radius: 15px;">
            <h5 class="fw-bold mb-4"><i class="bi bi-clock-history me-2 text-primary"></i>Detailed Logs</h5>
            <div class="timeline-container ms-2">
                <?php
                $logs = $conn->query("SELECT * FROM shipment_logs WHERE tracking_id = '".$shipment['tracking_id']."' ORDER BY id DESC");
                if($logs && $logs->num_rows > 0):
                    while($log = $logs->fetch_assoc()):
                ?>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="fw-bold text-dark"><?php echo $log['status']; ?></div>
                        <small class="text-muted"><?php echo date('d M Y, h:i A', strtotime($log['created_at'] ?? $log['updated_at'])); ?></small>
                        <p class="mb-0 mt-1 text-secondary small"><?php echo $log['description']; ?></p>
                    </div>
                <?php endwhile; else: ?>
                    <p class='text-muted small italic'>Shipment process started.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-center">
            <button onclick="window.print()" class="btn btn-dark px-4 me-2"><i class="bi bi-printer me-2"></i>Print Status</button>
            <a href="../index.php" class="btn btn-outline-primary px-4">New Search</a>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="display-1 text-muted mb-4">🔍</div>
            <h3 class="text-danger fw-bold">No Record Found</h3>
            <p class="text-muted">Aapka entered ID "<?php echo htmlspecialchars($tid); ?>" It could be wrong.</p>
            <a href="../index.php" class="btn btn-primary mt-3 px-5 rounded-pill">Try Again</a>
        </div>
    <?php endif; ?>
</div>
<?php include "footer.php"; ?>