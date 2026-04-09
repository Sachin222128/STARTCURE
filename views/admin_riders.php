<?php 
session_start();
include "../app/db_connection.php";
// 1. Admin Session Validation
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php?type=admin"); 
    exit();
}
include "header.php"; 
// 2. Query Updated: Humne LEFT JOIN karke average rating fetch kar li hai
$sql = "SELECT db.*, ROUND(AVG(s.rating), 1) as avg_rating 
        FROM delivery_boys db 
        LEFT JOIN shipments s ON db.id = s.delivery_boy_id 
        GROUP BY db.id 
        ORDER BY db.status DESC"; 

$res = $conn->query($sql);
if (!$res) {
    echo "<div class='alert alert-danger'>Database Error: " . $conn->error . "</div>";
    exit();
}
?>
<style>
    .modal-content { border-radius: 15px; border: none; }
    .detail-box { background: #f8f9fa; padding: 15px; border-radius: 10px; border-left: 4px solid #0d6efd; }
    .label-small { font-size: 0.75rem; color: #6c757d; font-weight: 700; text-transform: uppercase; }
    .rating-badge { background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 5px; font-weight: bold; }
</style>
<div class="container py-5">
    <div class="card shadow-sm border-0 p-4" style="border-radius: 15px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-primary mb-0"><i class="bi bi-people-fill me-2"></i>Rider Management (Approvals)</h4>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
                <div class="alert alert-success py-1 px-3 mb-0">Action Successful!</div>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Rider Name</th>
                        <th>Phone</th>
                        <th>Rating</th> <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($res->num_rows > 0): ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($row['name'] ?? 'N/A') ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($row['rider_code'] ?? '-') ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['phone'] ?? 'N/A') ?></td>
                            <td>
                                <span class="rating-badge">
                                    <i class="bi bi-star-fill me-1 text-warning"></i>
                                    <?= $row['avg_rating'] ? $row['avg_rating'] : '0.0' ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    $status = $row['status'] ?? 'Pending';
                                    $badgeClass = 'bg-warning text-dark';
                                    if($status == 'Active' || $status == 'Approved') $badgeClass = 'bg-success';
                                    if($status == 'Rejected') $badgeClass = 'bg-danger';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= $status ?></span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm btn-outline-primary px-3 rounded-pill" 
                                            onclick='openRiderModal(<?= json_encode($row) ?>)'>
                                        <i class="bi bi-eye me-1"></i> View
                                    </button>
                                    <?php if($status == 'Pending'): ?>
                                        <form action="../routes/admin_actions.php" method="POST" style="display:inline;" onsubmit="return confirm('APPROVE this rider?');">
                                            <input type="hidden" name="rider_id" value="<?= (int)$row['id'] ?>">
                                            <input type="hidden" name="status" value="Active">
                                            <button type="submit" class="btn btn-sm btn-success px-3 rounded-pill">Approve</button>
                                        </form>
                                        <form action="../routes/admin_actions.php" method="POST" style="display:inline;" onsubmit="return confirm('REJECT this rider?');">
                                            <input type="hidden" name="rider_id" value="<?= (int)$row['id'] ?>">
                                            <input type="hidden" name="status" value="Rejected">
                                            <button type="submit" class="btn btn-sm btn-danger px-3 rounded-pill">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-2"></i> No riders found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="riderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-badge me-2"></i>Full Rider Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="modal-content-area"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
function openRiderModal(data) {
    const ratingDisplay = data.avg_rating ? `${data.avg_rating} / 5 ⭐` : "No ratings yet"; 
    const content = `
        <div class="row g-4">
            <div class="col-md-6">
                <div class="detail-box h-100">
                    <label class="label-small">Personal Information</label>
                    <h5 class="fw-bold mb-3 text-primary">${data.name}</h5>
                    <p class="mb-1"><strong>Email:</strong> ${data.email || 'N/A'}</p>
                    <p class="mb-1"><strong>Phone:</strong> ${data.phone}</p>
                    <hr>
                    <p><strong>Performance Rating:</strong> <span class="text-warning fw-bold">${ratingDisplay}</span></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="detail-box h-100" style="border-left-color: #ffc107;">
                    <label class="label-small">Government Documentation</label>
                    <p class="mb-2"><strong>Aadhar No:</strong> ${data.aadhar_no || 'Not Provided'}</p>
                    <p class="mb-2"><strong>License No:</strong> ${data.license_no || 'Not Provided'}</p>
                    <hr>
                    <label class="label-small">Vehicle Details</label>
                    <p class="mb-1"><strong>Type:</strong> ${data.vehicle_type || 'N/A'}</p>
                    <p class="mb-0"><strong>Number:</strong> ${data.vehicle_no || 'N/A'}</p>
                </div>
            </div>
            <div class="col-12">
                <div class="detail-box" style="border-left-color: #198754; background: #f0fff4;">
                    <label class="label-small text-success">Bank Account Information (For Payouts)</label>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <p class="mb-0"><strong>Account No:</strong> ${data.bank_acc_no || 'N/A'}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-0"><strong>IFSC Code:</strong> ${data.ifsc_code || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.getElementById('modal-content-area').innerHTML = content;
    var myModal = new bootstrap.Modal(document.getElementById('riderModal'));
    myModal.show();
}
</script>
<?php include "footer.php"; ?>