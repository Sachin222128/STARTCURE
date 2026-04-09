<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include "../app/db_connection.php";

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// HEADER INCLUDE 
include "header.php"; 
$isAdmin = isset($_SESSION['admin_logged_in']);
?>

<div class="main-content flex-grow-1">
    <div class="container py-4 py-md-5">    
        <?php if ($isAdmin): ?>
            <div class="row mb-4 align-items-center">
                <div class="col-12 col-md-6 text-center text-md-start">
                    <h2 class="fw-bold mb-1"><i class="bi bi-headset text-primary me-2"></i>Admin Support</h2>
                    <p class="text-muted small">Manage and resolve customer queries</p>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive"> 
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Ticket ID</th>
                                <th>Customer</th>
                                <th>Issue Details</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // UPDATED SQL: Fetching 'phone' from customers table
                            $sql = "SELECT st.*, c.name, c.phone FROM support_tickets st JOIN customers c ON st.customer_id = c.id ORDER BY st.status DESC, st.created_at DESC";
                            $res = mysqli_query($conn, $sql);
                            while($row = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $row['id'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['name']) ?></div>
                                    <a href="tel:<?= $row['phone'] ?>" class="btn btn-sm btn-outline-success border-0 p-0 small shadow-none">
                                        <i class="bi bi-telephone-fill"></i> Call Now
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold small text-primary"><?= htmlspecialchars($row['category']) ?></div>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($row['subject']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($row['message']) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= ($row['status'] == 'Open') ? 'bg-danger' : 'bg-success' ?> rounded-pill px-3">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td class="text-center" style="min-width: 200px;">
                                    <div class="d-grid gap-2 p-2">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill mb-1" data-bs-toggle="modal" data-bs-target="#chatModal<?= $row['id'] ?>">
                                            <i class="bi bi-chat-left-text"></i> View Chat
                                        </button>

                                        <?php if($row['status'] == 'Open'): ?>
                                            <form action="../routes/auth_web.php" method="POST" class="p-2 bg-light rounded-3 border">
                                                <input type="hidden" name="action" value="resolve_ticket">
                                                <input type="hidden" name="ticket_id" value="<?= $row['id'] ?>">
                                                <textarea name="admin_remarks" class="form-control form-control-sm mb-2" rows="2" placeholder="Final resolution..." required></textarea>   
                                                <button type="submit" class="btn btn-sm btn-success w-100 rounded-pill shadow-sm">
                                                    Resolve & Close
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <div class="text-start p-2 bg-light rounded-3 border small">
                                                <span class="text-success fw-bold d-block"><i class="bi bi-check2-all"></i> Resolved</span>
                                                <small class="text-muted italic">"<?= htmlspecialchars($row['admin_remarks'] ?? 'No remarks') ?>"</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="chatModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title small fw-bold">Ticket #<?= $row['id'] ?> - Chat History</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body bg-light" style="max-height: 400px; overflow-y: auto;">
                                            <div class="mb-3 text-start">
                                                <div class="d-inline-block p-2 rounded-3 bg-white shadow-sm small border">
                                                    <strong>User:</strong> <?= htmlspecialchars($row['message']) ?>
                                                </div>
                                            </div>
                                            <?php 
                                            $t_id = $row['id'];
                                            $replies = mysqli_query($conn, "SELECT * FROM ticket_replies WHERE ticket_id = '$t_id' ORDER BY created_at ASC");
                                            while($rep = mysqli_fetch_assoc($replies)): 
                                                $isMe = ($rep['sender_type'] == 'Admin');
                                            ?>
                                                <div class="mb-2 <?= $isMe ? 'text-end' : 'text-start' ?>">
                                                    <div class="d-inline-block p-2 rounded-3 shadow-sm small <?= $isMe ? 'bg-primary text-white' : 'bg-white border' ?>">
                                                        <?= htmlspecialchars($rep['message']) ?>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                        <div class="modal-footer border-0 p-2">
                                            <form action="../routes/auth_web.php" method="POST" class="w-100 d-flex gap-2">
                                                <input type="hidden" name="action" value="send_ticket_reply">
                                                <input type="hidden" name="ticket_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="sender_type" value="Admin">
                                                <input type="text" name="reply_msg" class="form-control form-control-sm rounded-pill" placeholder="Type a message..." required>
                                                <button type="submit" class="btn btn-primary btn-sm rounded-circle"><i class="bi bi-send"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-7">
                    
                    <div class="alert bg-primary-subtle border-0 rounded-4 p-4 mb-4 shadow-sm text-center">
                        <h6 class="fw-bold mb-2">Need Urgent Help?</h6>
                        <p class="small text-muted mb-3">Speak directly with our support team for immediate assistance.</p>
                        <a href="tel:+919876543210" class="btn btn-primary rounded-pill px-4 fw-bold">
                            <i class="bi bi-telephone-fill me-2"></i> Call Support Now
                        </a>
                    </div>

                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5">
                        <div class="card-header bg-dark py-4 text-center border-0">
                            <h3 class="mb-0 text-warning fw-bold"><i class="bi bi-chat-dots me-2"></i>Help & Support</h3>
                        </div>    
                        <div class="card-body p-4 p-md-5">
                            <form action="../routes/auth_web.php" method="POST">
                                <input type="hidden" name="action" value="submit_ticket">
                                <div class="row g-3"> 
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Issue Category</label>
                                        <select name="category" class="form-select rounded-pill bg-light border-0" required>
                                            <option value="" selected disabled>Select Category</option>
                                            <option value="Damaged Item">Damaged Item</option>
                                            <option value="Delayed Delivery">Delayed Delivery</option>
                                            <option value="Rider Behavior">Rider Behavior</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Order ID (Optional)</label>
                                        <input type="text" name="shipment_id" class="form-control rounded-pill bg-light border-0" placeholder="e.g. STR-1234">
                                    </div>
                                    <div class="col-12 mt-4">
                                        <label class="form-label fw-bold small">Subject</label>
                                        <input type="text" name="subject" class="form-control rounded-pill bg-light border-0" placeholder="Summarize your issue" required>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <label class="form-label fw-bold small">Detailed Message</label>
                                        <textarea name="message" class="form-control rounded-4 bg-light border-0" rows="5" placeholder="Tell us more about the problem..." required></textarea>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-warning w-100 rounded-pill py-3 fw-bold shadow">
                                            Submit Ticket <i class="bi bi-send-fill ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Your Previous Tickets</h5>
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light text-muted small">
                                        <tr>
                                            <th class="ps-3">ID</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $customer_id = $_SESSION['customer_id'];
                                        $h_sql = "SELECT * FROM support_tickets WHERE customer_id = '$customer_id' ORDER BY created_at DESC";
                                        $h_res = mysqli_query($conn, $h_sql);
                                        if(mysqli_num_rows($h_res) > 0):
                                            while($h_row = mysqli_fetch_assoc($h_res)): ?>
                                            <tr>
                                                <td class="ps-3 fw-bold">#<?= $h_row['id'] ?></td>
                                                <td><?= htmlspecialchars($h_row['subject']) ?></td>
                                                <td>
                                                    <span class="badge <?= ($h_row['status']=='Open') ? 'bg-danger':'bg-success' ?> rounded-pill">
                                                        <?= $h_row['status'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-light border rounded-pill" data-bs-toggle="modal" data-bs-target="#chatModalUser<?= $h_row['id'] ?>">
                                                        View Chat
                                                    </button>
                                                </td>
                                            </tr>

                                            <div class="modal fade" id="chatModalUser<?= $h_row['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0 shadow">
                                                        <div class="modal-header bg-dark text-white">
                                                            <h5 class="modal-title small fw-bold">Support Chat #<?= $h_row['id'] ?></h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body bg-light" style="max-height: 400px; overflow-y: auto;">
                                                            <div class="mb-3 text-end">
                                                                <div class="d-inline-block p-2 rounded-3 bg-warning text-dark shadow-sm small">
                                                                    <strong>Me:</strong> <?= htmlspecialchars($h_row['message']) ?>
                                                                </div>
                                                            </div>
                                                            <?php 
                                                            $t_id_u = $h_row['id'];
                                                            $replies_u = mysqli_query($conn, "SELECT * FROM ticket_replies WHERE ticket_id = '$t_id_u' ORDER BY created_at ASC");
                                                            while($rep_u = mysqli_fetch_assoc($replies_u)): 
                                                                $isAdminMsg = ($rep_u['sender_type'] == 'Admin');
                                                            ?>
                                                                <div class="mb-2 <?= $isAdminMsg ? 'text-start' : 'text-end' ?>">
                                                                    <div class="d-inline-block p-2 rounded-3 shadow-sm small <?= $isAdminMsg ? 'bg-white border' : 'bg-warning text-dark' ?>">
                                                                        <?= htmlspecialchars($rep_u['message']) ?>
                                                                    </div>
                                                                </div>
                                                            <?php endwhile; ?>
                                                        </div>
                                                        <div class="modal-footer border-0 p-2">
                                                            <form action="../routes/auth_web.php" method="POST" class="w-100 d-flex gap-2">
                                                                <input type="hidden" name="action" value="send_ticket_reply">
                                                                <input type="hidden" name="ticket_id" value="<?= $h_row['id'] ?>">
                                                                <input type="hidden" name="sender_type" value="Customer">
                                                                <input type="text" name="reply_msg" class="form-control form-control-sm rounded-pill" placeholder="Reply..." required>
                                                                <button type="submit" class="btn btn-dark btn-sm rounded-circle"><i class="bi bi-send"></i></button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; else: ?>
                                            <tr><td colspan="4" class="text-center py-4 text-muted small">No tickets found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>