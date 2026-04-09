<?php 
include "header.php"; 
include "../app/db_connection.php";
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}
$cid = $_SESSION['customer_id'];
// Saare columns fetch ho rahe hain database se
$res = $conn->query("SELECT * FROM customers WHERE id = '$cid'");
$user = $res->fetch_assoc();
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    /* Aapke premium green design jaisa header */
    .profile-header {
        background-color: #4a8762; /* Aapke screenshot se matching green */
        border-radius: 20px 20px 0 0;
        padding: 40px;
    }
    .profile-card {
        border-radius: 20px;
        overflow: hidden;
        border: none;
        background: #fff;
    }
    .detail-label {
        color: #6c757d;
        font-weight: 600;
        font-size: 0.85rem;
    } 
    .detail-value {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0;
    }
    .status-badge {
        font-size: 0.8rem;
        font-weight: 600;
        background-color: rgba(255,255,255,0.2);
        color: #fff;
        padding: 5px 15px;
        border-radius: 50px;
        display: inline-block;
    }
    .btn-edit {
        background-color: #fff;
        color: #333;
        font-weight: 600;
        border-radius: 50px;
        padding: 10px 25px;
        border: none;
        transition: 0.3s;
    }
    .btn-edit:hover {
        background-color: #f8f9fa;
        color: #000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .section-title {
        color: #4a8762;
        font-weight: 700;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f5f9;
    }
    /* Modal Styling */
    .modal-content { border-radius: 15px; border: none; }
    .modal-header { border-radius: 15px 15px 0 0; border: none; }
    .modal-footer { border: none; }
</style>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-11 col-lg-10"> 
            <div class="card shadow-lg profile-card">   
                <div class="profile-header d-flex justify-content-between align-items-center text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-circle display-3 me-4 opacity-75"></i>
                        <div>
                            <h1 class="mb-1 fw-bold text-capitalize"><?php echo $user['name']; ?></h1>
                            <span class="status-badge">Verified Customer Account</span>
                        </div>
                    </div>
                    <button class="btn btn-edit shadow-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil-square me-2"></i>Edit Profile
                    </button>
                </div>
                <div class="card-body p-5">    
                    <h5 class="section-title">
                        <i class="bi bi-card-list me-2"></i>Personal & Contact Details
                    </h5>    
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <label class="detail-label">Full Name</label>
                            <p class="detail-value border-start border-success border-3 ps-2 text-capitalize"><?php echo $user['name']; ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="detail-label">Email Address</label>
                            <p class="detail-value border-start border-success border-3 ps-2"><?php echo $user['email']; ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="detail-label">Phone Number</label>
                            <p class="detail-value border-start border-success border-3 ps-2"><?php echo $user['phone']; ?></p>
                        </div>
                    </div>
                    <h5 class="section-title">
                        <i class="bi bi-geo-alt-fill me-2"></i>Address & Location
                    </h5>    
                    <div class="row g-4 mb-5">
                        <div class="col-md-12">
                            <label class="detail-label">Full Address</label>
                            <p class="detail-value"><?php echo $user['address'] ?: '<em>Address not provided. Click Edit to add.</em>'; ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="detail-label">District</label>
                            <p class="detail-value text-capitalize"><?php echo $user['district'] ?: 'N/A'; ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="detail-label">State</label>
                            <p class="detail-value text-capitalize"><?php echo $user['state'] ?: 'N/A'; ?></p>
                        </div>
                        <div class="col-md-4">
                            <label class="detail-label">Pincode</label>
                            <p class="detail-value"><?php echo $user['pincode'] ?: 'N/A'; ?></p>
                        </div>
                    </div>
                    <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center bg-light p-4 rounded-3">
                        <p class="text-muted small mb-0 fw-semibold">
                            Member since: <span class="fw-bold text-dark"><?php echo date('d M, Y', strtotime($user['created_at'])); ?></span>
                        </p>
                        <div class="d-flex gap-2">
                            <a href="book.php" class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-plus-circle me-2"></i>New Booking
                            </a>
                            <a href="../routes/logout.php" class="btn btn-outline-danger fw-bold px-4 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="editProfileModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Update Account Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>    
            <form id="updateProfileForm">
                <div class="modal-body p-4 p-md-5">
                    <input type="hidden" name="type" value="update_profile">    
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary small">FULL NAME</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-person text-success"></i></span>
                                <input type="text" name="name" class="form-control bg-light border-0 p-3" value="<?php echo $user['name']; ?>" required>
                            </div>
                        </div>    
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">EMAIL (CANNOT CHANGE)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control bg-light border-0 p-3 text-muted" value="<?php echo $user['email']; ?>" readonly>
                            </div>
                        </div>   
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">PHONE NUMBER</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-telephone text-success"></i></span>
                                <input type="text" name="phone" class="form-control bg-light border-0 p-3" value="<?php echo $user['phone']; ?>" maxlength="10" required>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <h6 class="text-success fw-bold mb-3 border-bottom pb-2">Location Details</h6>
                        </div>   
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary small">FULL STREET ADDRESS</label>
                            <textarea name="address" class="form-control bg-light border-0 p-3" rows="3"><?php echo $user['address']; ?></textarea>
                        </div>    
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">DISTRICT</label>
                            <input type="text" name="district" class="form-control bg-light border-0 p-3" value="<?php echo $user['district']; ?>">
                        </div>    
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">STATE</label>
                            <input type="text" name="state" class="form-control bg-light border-0 p-3" value="<?php echo $user['state']; ?>">
                        </div>    
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">PINCODE</label>
                            <input type="text" name="pincode" class="form-control bg-light border-0 p-3" value="<?php echo $user['pincode']; ?>" maxlength="6">
                        </div>
                    </div>
                </div>    
                <div class="modal-footer p-4 border-top">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Discard Changes</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm" id="submitBtn">
                        <i class="bi bi-check-circle me-2"></i>Save & Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// AJAX form submission logic
document.getElementById('updateProfileForm').onsubmit = function(e) {
    e.preventDefault(); // Default submission
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Updating...';
    // FormData create karein jisme saare inputs hon
    const formData = new FormData(this);
    // Aapke routes/auth_web.php par data bhej rahe hain
    fetch('../routes/auth_web.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text())
    .then(res => {
        // Agar PHP success lautaata hai
        if(res.trim() === "success") {
            Swal.fire({
                title: 'Success!',
                text: 'Profile details updated successfully.',
                icon: 'success',
                confirmButtonColor: '#4a8762'
            }).then(() => {
                // Page reload karein taaki naye changes dikhein
                location.reload(); 
            });
        } else {
            // Error hone par PHP ne kya kaha dikhaein
            Swal.fire('Update Failed', res, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Save & Update';
        }
    })
    .catch(() => {
        // Network or fetch error
        Swal.fire('Error', 'Network error. Try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Save & Update';
    });
};
</script>
<?php include "footer.php"; ?>