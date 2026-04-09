<?php
$base_url = "http://localhost/STARTCURE/";
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Database Connection
$conn = mysqli_connect("localhost", "root", "", "courier_pro");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

if (!isset($_SESSION['customer_id'])) {
    header("Location: " . $base_url . "views/login.php");
    exit();
}
include "../header.php"; 
$customer_id = $_SESSION['customer_id'];
// Fetch shipments including rating columns
$sql = "SELECT * FROM shipments WHERE customer_id = '$customer_id' ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="fw-bold text-dark mb-1">My Bookings</h2>
            <p class="text-secondary small">View and manage your recent courier history</p>
        </div>
        <div class="col-auto">
            <a href="book.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> New Booking
            </a>
        </div>
    </div>
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-4 py-3 border-0">Booking ID</th>
                            <th class="py-3 border-0">Receiver Details</th>
                            <th class="py-3 border-0">Total Amount</th>
                            <th class="py-3 text-center border-0">Status</th>
                            <th class="py-3 text-center border-0">Feedback</th> </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $status = strtoupper($row['status'] ?? 'BOOKED'); 
                                // Dynamic Status Colors
                                $colorClass = "bg-secondary";
                                if($status == 'BOOKED') $colorClass = "bg-warning text-dark";
                                if($status == 'OUT FOR DELIVERY') $colorClass = "bg-info text-dark";
                                if($status == 'DELIVERED') $colorClass = "bg-success";
                                if($status == 'CANCELLED') $colorClass = "bg-danger";
                                $display_price = $row['total_price'] ?? $row['amount'] ?? 0;
                        ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-primary">#<?php echo $row['id']; ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark text-capitalize"><?php echo $row['receiver_name']; ?></div>
                                <div class="text-muted small">Tracking Active</div>
                            </td>
                            <td>
                                <span class="fw-bold text-dark">
                                    ₹<?php echo number_format($display_price, 2); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill <?php echo $colorClass; ?> px-3 py-2 shadow-sm" style="min-width: 120px; font-size: 0.7rem;">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if($status == 'DELIVERED'): ?>
                                    <?php if(empty($row['rating'])): ?>
                                        <button class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" 
                                                onclick="openRatingModal(<?php echo $row['id']; ?>)">
                                            <i class="bi bi-star-fill me-1"></i> Rate Order
                                        </button>
                                    <?php else: ?>
                                        <div class="text-warning fw-bold small">
                                            <?php echo $row['rating']; ?>/5 <i class="bi bi-star-fill"></i>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small italic">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5'>
                                    <i class='bi bi-inbox display-1 text-light d-block mb-3'></i>
                                    <h5 class='text-muted'>No Bookings Found</h5>
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="ratingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning border-0 p-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-chat-heart me-2"></i>Rate Your Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../routes/auth_web.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="submit_feedback">
                    <input type="hidden" name="shipment_id" id="modal_shipment_id"> 
                    <div class="mb-4 text-center">
                        <label class="form-label d-block fw-bold text-secondary mb-3">How was the delivery?</label>
                        <select name="rating" class="form-select form-select-lg rounded-pill border-2 border-warning text-center" required>
                            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                            <option value="4">⭐⭐⭐⭐ Very Good</option>
                            <option value="3">⭐⭐⭐ Good</option>
                            <option value="2">⭐⭐ Fair</option>
                            <option value="1">⭐ Poor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Add a Comment (Optional)</label>
                        <textarea name="review" class="form-control bg-light border-0 rounded-3" rows="3" placeholder="Tell us more..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill py-2 shadow-sm">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openRatingModal(id) {
    // 1. Set Shipment ID
    document.getElementById('modal_shipment_id').value = id;
    // 2. Trigger Modal correctly for Bootstrap 5
    var modalElement = document.getElementById('ratingModal');
    var myModal = new bootstrap.Modal(modalElement);
    myModal.show();
}
</script>
<style>
    body { background-color: #f8f9fa; }
    .table thead th { font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
    .table-hover tbody tr:hover { background-color: #f1f4f9 !important; transition: 0.3s ease; }
    .card { border-radius: 1rem !important; }
    .modal-content { border-radius: 1.5rem !important; }
</style>
<?php include "../footer.php"; ?>