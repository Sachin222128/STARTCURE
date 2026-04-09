<?php 
session_start();
// Security Check: Sirf Admin hi dekh sake
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}
include "../app/db_connection.php";
include "header.php"; // Aapka purana header reuse ho raha hai
?>
<div class="container mt-5" style="min-height: 80vh;">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h3 class="fw-bold text-primary"><i class="bi bi-wallet2 me-2"></i> Payout Requests</h3>
        <span class="badge bg-danger rounded-pill px-3">Pending Action</span>
    </div>
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Rider Details</th>
                        <th>Amount</th>
                        <th>UPI ID</th>
                        <th>Request Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Naye table 'withdrawal_requests' se data fetch kar rahe hain
                    $sql = "SELECT wr.*, d.name as rider_name 
                            FROM withdrawal_requests wr 
                            JOIN delivery_boys d ON wr.rider_id = d.id 
                            WHERE wr.status = 'Pending' 
                            ORDER BY wr.request_date DESC";
                    
                    $result = mysqli_query($conn, $sql);

                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?php echo $row['rider_name']; ?></div>
                                    <small class="text-muted">Rider ID: #<?php echo $row['rider_id']; ?></small>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">₹<?php echo number_format($row['amount'], 2); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-upc-scan me-1"></i> <?php echo $row['upi_id']; ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?php echo date('d M, h:i A', strtotime($row['request_date'])); ?></small>
                                </td>
                                <td class="text-center">
                                    <form action="../routes/admin_actions.php" method="POST" onsubmit="return confirm('Confirm Payout for ₹<?php echo $row['amount']; ?>?');">
                                        <input type="hidden" name="req_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="rider_id" value="<?php echo $row['rider_id']; ?>">
                                        <input type="hidden" name="amount" value="<?php echo $row['amount']; ?>">
                                        
                                        <button type="submit" name="approve_payout" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                                            APPROVE & PAY
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-5 text-muted'>
                                <i class='bi bi-emoji-smile display-4 d-block mb-2'></i>
                                No pending payout requests right now.
                              </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include "footer.php"; // Aapka purana footer reuse ho raha hai ?>