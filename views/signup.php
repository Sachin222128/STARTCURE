<?php include "header.php"; ?>
<style>
    .loader-inline { display: none; width: 14px; height: 14px; border: 2px solid #ccc; border-top: 2px solid #198754; border-radius: 50%; animation: spin 0.8s linear infinite; margin-left: 5px; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .form-control:focus { border-color: #198754; box-shadow: 0 0 0 0.25 laser rgba(25, 135, 84, 0.25); }
</style>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-body p-5">
                    <h3 class="fw-bold text-success mb-2 text-center">Customer Registration</h3>
                    <p class="text-muted small mb-4 text-center">Create your account and book your parcel.</p>                  
                    <form action="../routes/auth_web.php" method="POST" onsubmit="return validatePassword()">
                        <input type="hidden" name="action" value="customer_signup">       
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">First Name</label>
                                <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Last Name</label>
                                <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <option value="" selected disabled>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Mobile No.</label>
                                <input type="tel" name="phone" class="form-control" maxlength="10" placeholder="10 Digit Number" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Alternate Mobile No.</label>
                                <input type="tel" name="alt_phone" class="form-control" maxlength="10" placeholder="Optional" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Create Password</label>
                                <input type="password" id="pass" name="password" class="form-control" minlength="6" placeholder="Min. 6 chars" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Confirm Password</label>
                                <input type="password" id="confirm_pass" class="form-control" placeholder="Repeat Password" required>
                            </div>
                            <div class="col-md-12">
                                <label class="small fw-bold text-muted">Full Address</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="House No, Street, Landmark..." required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted">Pincode <span id="pin_loader" class="loader-inline"></span></label>
                                <input type="text" name="pincode" id="pincode" class="form-control" maxlength="6" placeholder="6 Digit" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted">District</label>
                                <input type="text" name="district" id="district" class="form-control bg-light" placeholder="Auto-filled" readonly required>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted">State</label>
                                <input type="text" name="state" id="state" class="form-control bg-light" placeholder="Auto-filled" readonly required>
                            </div>
                            <div class="col-md-12">
                                <label class="small fw-bold text-muted">Country</label>
                                <input type="text" name="country" class="form-control bg-light" value="India" readonly>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm mt-4">REGISTER NOW</button>
                    </form>
                    <div class="text-center mt-4">
                        <span class="text-muted small">Already a member?</span>
                        <a href="login.php" class="text-success fw-bold text-decoration-none small"> Login Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function validatePassword() {
    var pass = document.getElementById("pass").value;
    var confirmPass = document.getElementById("confirm_pass").value;
    if (pass != confirmPass) {
        Swal.fire({ title: 'Error!', text: 'Passwords do not match!', icon: 'error' });
        return false;
    }
    return true;
}
// Pincode API Logic
document.getElementById('pincode').addEventListener('input', function() {
    let pincode = this.value;
    let loader = document.getElementById('pin_loader');
    let distField = document.getElementById('district');
    let stateField = document.getElementById('state');
    if(pincode.length === 6) {
        loader.style.display = 'inline-block';
        fetch(`https://api.postalpincode.in/pincode/${pincode}`)
        .then(res => res.json())
        .then(data => {
            loader.style.display = 'none';
            if(data[0].Status === 'Success') {
                distField.value = data[0].PostOffice[0].District;
                stateField.value = data[0].PostOffice[0].State;
                distField.classList.remove('bg-light');
                stateField.classList.remove('bg-light');
            } else {
                Swal.fire({ title: 'Invalid Pincode', text: 'Please enter a valid Indian pincode.', icon: 'warning' });
                distField.value = '';
                stateField.value = '';
            }
        })
        .catch(err => {
            loader.style.display = 'none';
            console.error("API Error:", err);
        });
    } else {
        distField.value = '';
        stateField.value = '';
        distField.classList.add('bg-light');
        stateField.classList.add('bg-light');
    }
});
</script>
<?php include "footer.php"; ?>