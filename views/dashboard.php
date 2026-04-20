<?php 
session_start();
// SECURITY CHECK - UPDATED TO SECRET URL
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php"); 
    exit();
}
include "../app/db_connection.php";
include "header.php"; 
// NEW PROFESSIONAL CALCULATIONS 
// 1. Gross Revenue 
$gross_res = $conn->query("SELECT SUM(amount) as total FROM shipments WHERE payment_status = 'Paid'");
$gross_revenue = $gross_res->fetch_assoc()['total'] ?? 0;
// 2. Rider Payouts 
$payout_res = $conn->query("SELECT SUM(wallet_balance) as total FROM delivery_boys");
$total_payouts = $payout_res->fetch_assoc()['total'] ?? 0;
// 3. Net Profit 
$net_profit = $gross_revenue - $total_payouts;
// CALCULATIONS FOR PIE CHART (ORDER STATUS)
$total_shipments = $conn->query("SELECT id FROM shipments")->num_rows;
$delivered_count = $conn->query("SELECT id FROM shipments WHERE status = 'Delivered'")->num_rows;
$remaining_count = $total_shipments - $delivered_count;
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">     
            <?php if(isset($_GET['update']) && $_GET['update'] == 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                    <strong>✅ Success!</strong> Shipment details updated successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-3">
                    <h2 class="fw-bold text-dark"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h2>
                    <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#analyticsModal">
                        <i class="bi bi-graph-up-arrow me-1"></i> View Analytics
                    </button>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <h5 class="text-primary fw-bold mb-3"><i class="bi bi-gear-wide-connected me-2"></i>Quick Content Management</h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addServiceModal">Add Service</button>
                                <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addFAQModal">Add FAQ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white border-0 shadow-sm mb-2">
                        <div class="card-body text-center">
                            <h6 class="opacity-75 text-uppercase small">Total Shipments</h6>
                            <h2 class="fw-bold mb-0">
                                <?php echo $total_shipments; ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white border-0 shadow-sm mb-2">
                        <div class="card-body text-center">
                            <h6 class="opacity-75 text-uppercase small">Gross Revenue</h6>
                            <h2 class="fw-bold mb-0">₹<?php echo number_format($gross_revenue, 0); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white border-0 shadow-sm mb-2">
                        <div class="card-body text-center">
                            <h6 class="opacity-75 text-uppercase small">Rider Payouts</h6>
                            <h2 class="fw-bold mb-0">₹<?php echo number_format($total_payouts, 0); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white border-0 shadow-sm mb-2">
                        <div class="card-body text-center">
                            <h6 class="opacity-75 text-uppercase small">Company Net Profit</h6>
                            <h2 class="fw-bold mb-0 text-warning">₹<?php echo number_format($net_profit, 0); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-list-ul me-2"></i>Recent Shipments</h5>   
                    <form action="" method="GET" class="d-flex shadow-sm mt-2 mt-md-0" style="max-width: 300px;">
                        <input type="text" name="search" class="form-control form-control-sm border-end-0" placeholder="Search Name, ID or Mobile..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button type="submit" class="btn btn-primary btn-sm border-start-0">
                            <i class="bi bi-search"></i>
                        </button>
                        <?php if(isset($_GET['search'])): ?>
                            <a href="dashboard.php" class="btn btn-outline-danger btn-sm ms-1">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tracking ID</th>
                                    <th>Sender & Receiver</th>
                                    <th>Item & Amount</th>
                                    <th>Status</th>
                                    <th>Rider</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $search = $_GET['search'] ?? '';
                                if(!empty($search)) {
                                    $search_param = "%$search%";
                                    $stmt = $conn->prepare("SELECT s.*, db.name as rider_name FROM shipments s LEFT JOIN delivery_boys db ON s.delivery_boy_id = db.id WHERE s.tracking_id LIKE ? OR s.sender_name LIKE ? OR s.receiver_name LIKE ? OR s.receiver_mobile LIKE ? ORDER BY s.id DESC");
                                    $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                } else {
                                    $res = $conn->query("SELECT s.*, db.name as rider_name FROM shipments s LEFT JOIN delivery_boys db ON s.delivery_boy_id = db.id ORDER BY s.id DESC");
                                }
                                if($res->num_rows > 0) {
                                    while($row = $res->fetch_assoc()) {
                                        $p_bg = ($row['payment_status'] == 'Paid') ? 'bg-success' : 'bg-warning text-dark';   
                                        $rider_display = $row['rider_name'] ? "<span class='badge bg-dark'><i class='bi bi-bicycle me-1'></i> " . htmlspecialchars($row['rider_name']) . "</span>" : "<span class='badge bg-light text-muted border'>Not Assigned</span>";
                                        $msg = "Hi *" . $row['receiver_name'] . "*,\nYour parcel (#" . $row['tracking_id'] . ") is *" . $row['status'] . "*.\nTrack: " . $base_url . "views/track_result.php?tid=" . $row['tracking_id'];
                                        $wa_link = "https://wa.me/91" . $row['receiver_mobile'] . "?text=" . urlencode($msg);    
                                        ?>
                                        <tr>
                                            <td>
                                                <span class='fw-bold text-primary'>#<?php echo $row['tracking_id']; ?></span><br>
                                                <small class='text-muted'><?php echo date('d M', strtotime($row['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <small><b>F:</b> <?php echo htmlspecialchars($row['sender_name']); ?></small><br>
                                                <small><b>T:</b> <?php echo htmlspecialchars($row['receiver_name']); ?></small>
                                            </td>
                                            <td>
                                                <span class='badge bg-info text-dark'><?php echo htmlspecialchars($row['item_name']); ?></span><br>
                                                <span class='fw-bold'>₹<?php echo $row['amount']; ?></span>
                                            </td>
                                            <td>
                                                <span class='badge bg-secondary'><?php echo $row['status']; ?></span><br>
                                                <span class='badge <?php echo $p_bg; ?>' style='font-size:10px'><?php echo $row['payment_status']; ?></span>
                                            </td>
                                            <td><?php echo $rider_display; ?></td>
                                            <td class='text-center'>
                                                <div class='btn-group shadow-sm'>
                                                    <button class='btn btn-outline-primary btn-sm' data-bs-toggle='modal' data-bs-target='#updateModal<?php echo $row['id']; ?>'>
                                                        Edit
                                                    </button>
                                                    <a href='print_invoice.php?id=<?php echo $row['id']; ?>' class='btn btn-info btn-sm text-white' target='_blank'><i class='bi bi-file-text'></i></a>
                                                    <a href='print_label.php?id=<?php echo $row['id']; ?>' class='btn btn-outline-dark btn-sm' target='_blank'><i class='bi bi-printer'></i></a>
                                                    <a href='<?php echo $wa_link; ?>' target='_blank' class='btn btn-outline-success btn-sm'><i class='bi bi-whatsapp'></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <div class='modal fade' id='updateModal<?php echo $row['id']; ?>' tabindex='-1' aria-hidden='true'>
                                            <div class='modal-dialog modal-dialog-centered'>
                                                <div class='modal-content'>
                                                    <form action='../routes/web.php' method='POST'>
                                                        <input type='hidden' name='type' value='update_status_admin'>
                                                        <input type='hidden' name='shipment_id' value='<?php echo $row['id']; ?>'>
                                                        <div class='modal-header'>
                                                            <h5 class='modal-title fw-bold'>Manage Shipment #<?php echo $row['tracking_id']; ?></h5>
                                                            <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                                        </div>
                                                        <div class='modal-body text-start'>
                                                            <div class='mb-3'>
                                                                <label class='form-label fw-bold'>Assign Delivery Boy (Rider)</label>
                                                                <select name='delivery_boy_id' class='form-select border-primary' required>
                                                                    <option value='' disabled <?php echo ($row['delivery_boy_id'] == 0 ? 'selected' : ''); ?>>-- Select Rider --</option>
                                                                    <?php 
                                                                    $dboys = $conn->query("SELECT id, name FROM delivery_boys WHERE status = 'Active'");
                                                                    while($db = $dboys->fetch_assoc()){
                                                                        $sel = ($row['delivery_boy_id'] == $db['id']) ? 'selected' : '';
                                                                        echo "<option value='{$db['id']}' $sel>{$db['name']}</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class='mb-3'>
                                                                <label class='form-label fw-bold'>Delivery Status</label>
                                                                <select name='new_status' class='form-select' required>
                                                                    <option value='Pending' <?php echo ($row['status']=='Pending'?'selected':''); ?>>Pending</option>
                                                                    <option value='In Transit' <?php echo ($row['status']=='In Transit'?'selected':''); ?>>In Transit</option>
                                                                    <option value='Out for Delivery' <?php echo ($row['status']=='Out for Delivery'?'selected':''); ?>>Out for Delivery</option>
                                                                    <option value='Delivered' <?php echo ($row['status']=='Delivered'?'selected':''); ?>>Delivered</option>
                                                                </select>
                                                            </div>
                                                            <div class='mb-3'>
                                                                <label class='form-label fw-bold'>Payment Status</label>
                                                                <select name='new_payment_status' class='form-select' required>
                                                                    <option value='Pending' <?php echo ($row['payment_status']=='Pending'?'selected':''); ?>>Pending</option>
                                                                    <option value='Paid' <?php echo ($row['payment_status']=='Paid'?'selected':''); ?>>Mark as PAID</option>
                                                                    <option value='Pending (COD)' <?php echo ($row['payment_status']=='Pending (COD)'?'selected':''); ?>>Pending (COD)</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class='modal-footer'>
                                                            <button type='submit' class='btn btn-primary w-100 py-2 fw-bold'>Update Shipment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No Shipments Found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="analyticsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-4">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold text-primary"><i class="bi bi-pie-chart-fill me-2"></i>Data Analytics</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row">
            <div class="col-md-6 mb-4">
                <h6 class="text-center fw-bold text-secondary mb-3">Order Status Breakdown</h6>
                <div style="height: 250px;"><canvas id="orderStatusChart"></canvas></div>
                <div class="mt-3 text-center small">
                    <span class="badge bg-success">Delivered: <?php echo $delivered_count; ?></span>
                    <span class="badge bg-warning text-dark">Remaining: <?php echo $remaining_count; ?></span>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <h6 class="text-center fw-bold text-secondary mb-3">Revenue vs Profit (₹)</h6>
                <div style="height: 250px;"><canvas id="financeBarChart"></canvas></div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog"><form action="../routes/admin_actions.php" method="POST" class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add New Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="text" name="service_name" class="form-control mb-2" placeholder="Service Name" required>
            <textarea name="description" class="form-control" placeholder="Description"></textarea>
        </div>
        <div class="modal-footer"><button type="submit" name="add_service" class="btn btn-primary">Save Service</button></div>
    </form></div>
</div>

<div class="modal fade" id="addFAQModal" tabindex="-1">
    <div class="modal-dialog"><form action="../routes/admin_actions.php" method="POST" class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add New FAQ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="text" name="question" class="form-control mb-2" placeholder="Question" required>
            <textarea name="answer" class="form-control" placeholder="Answer" required></textarea>
        </div>
        <div class="modal-footer"><button type="submit" name="add_faq" class="btn btn-primary">Save FAQ</button></div>
    </form></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctxPie = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Delivered', 'Remaining/Pending'],
            datasets: [{
                data: [<?php echo $delivered_count; ?>, <?php echo $remaining_count; ?>],
                backgroundColor: ['#198754', '#ffc107'],
                hoverOffset: 15
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
    const ctxBar = document.getElementById('financeBarChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Revenue', 'Payouts', 'Net Profit'],
            datasets: [{
                label: 'Amount in ₹',
                data: [<?php echo $gross_revenue; ?>, <?php echo $total_payouts; ?>, <?php echo $net_profit; ?>],
                backgroundColor: ['#0d6efd', '#dc3545', '#ffc107'],
                borderRadius: 5
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
});
</script>
<?php include "footer.php"; ?>