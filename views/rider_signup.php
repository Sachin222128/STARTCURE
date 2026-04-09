<?php include "header.php"; ?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0" style="border-radius: 20px;">
                <div class="card-header bg-warning text-dark text-center py-4" style="border-radius: 20px 20px 0 0;">
                    <i class="bi bi-bicycle display-4"></i>
                    <h2 class="fw-bold mb-0">Rider Registration</h2>
                    <p class="mb-0 opacity-75">Join Startcure as a Delivery Partner</p>
                </div>    
                <div class="card-body p-5">
                    <form action="../routes/auth_web.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="rider_signup">
                        <h5 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="bi bi-person-fill me-2"></i>Personal Details
                        </h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mobile Number</label>
                                <input type="tel" name="phone" class="form-control" placeholder="" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email ID</label>
                                <input type="email" name="email" class="form-control" placeholder="" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Full Address</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="" required></textarea>
                            </div>    
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Pincode</label>
                                <input type="number" name="pincode" id="pincode" class="form-control" placeholder="" onblur="fetchLocation()" required>
                                <div id="pincode-loader" class="text-primary small mt-1" style="display:none;">Fetching details...</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">City / District</label>
                                <input type="text" name="city" id="city" class="form-control" placeholder="" required readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">State</label>
                                <input type="text" name="state" id="state" class="form-control" placeholder="" required readonly>
                            </div>
                        </div>
                        <h5 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="bi bi-card-checklist me-2"></i>Identity & Vehicle
                        </h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Aadhar Number</label>
                                <input type="number" name="aadhar_no" class="form-control" placeholder="1234 5678 9012" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Driving License Number</label>
                                <input type="text" name="license_no" class="form-control" placeholder="DL-XXXXXXXXXXXXX" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Vehicle Type</label>
                                <select name="vehicle_type" class="form-select" required>
                                    <option value="" selected disabled>Select Vehicle</option>
                                    <option value="Bike">Bike</option>
                                    <option value="Scooter">Scooter</option>
                                    <option value="Bicycle">Bicycle</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Vehicle Number</label>
                                <input type="text" name="vehicle_no" class="form-control" placeholder="MH-01-AB-1234">
                                <small class="text-muted">Not required for Bicycle</small>
                            </div>
                        </div>
                        <h5 class="fw-bold text-primary mb-3 border-bottom pb-2">
                            <i class="bi bi-bank me-2"></i>Payout Details (Bank)
                        </h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Bank Account Number</label>
                                <input type="password" name="bank_acc" class="form-control" placeholder="Enter Account Number" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">IFSC Code</label>
                                <input type="text" name="ifsc" class="form-control" placeholder="SBIN0001234" required>
                            </div>
                        </div>
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label small" for="terms">I agree to the Startcure <a href="#">Terms & Conditions</a> and Privacy Policy.</label>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold py-3 shadow-sm rounded-pill">
                            <i class="bi bi-person-plus-fill me-2"></i> REGISTER AS RIDER
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted">Already have a rider account? <a href="login.php?type=rider" class="fw-bold text-decoration-none">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function fetchLocation() {
    let pincode = document.getElementById('pincode').value;
    let cityInput = document.getElementById('city');
    let stateInput = document.getElementById('state');
    let loader = document.getElementById('pincode-loader');
    if (pincode.length == 6) {
        loader.style.display = 'block';    
        // India Post Pincode API (Open Source)
        fetch(`https://api.postalpincode.in/pincode/${pincode}`)
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none';
                if (data[0].Status === "Success") {
                    let details = data[0].PostOffice[0];
                    cityInput.value = details.District;
                    stateInput.value = details.State;
                    // Reset styling if it was red before
                    cityInput.classList.remove('is-invalid');
                } else {
                    alert("Invalid Pincode! Please check again.");
                    cityInput.value = "";
                    stateInput.value = "";
                }
            })
            .catch(error => {
                loader.style.display = 'none';
                console.error("Error fetching pincode data:", error);
            });
    }
}
</script>
<?php include "footer.php"; ?>