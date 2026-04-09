<?php 
session_start(); 
include "header.php"; 
$logged_name = $_SESSION['customer_name'] ?? '';
$logged_phone = $_SESSION['customer_phone'] ?? '';
$logged_email = $_SESSION['customer_email'] ?? ''; 
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    body { background-color: #f8f9fc; font-family: 'Inter', -apple-system, sans-serif; color: #334155; }
    .logistics-step { position: relative; padding-left: 45px; margin-bottom: 30px; cursor: pointer; }
    .logistics-step::before { content: ""; position: absolute; left: 17px; top: 35px; height: 100%; width: 2px; background: #e2e8f0; z-index: 1; }
    .logistics-step:last-child::before { display: none; }
    .step-icon { position: absolute; left: 0; top: 0; width: 36px; height: 36px; background: #fff; border: 2px solid #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 2; transition: 0.3s; font-weight: bold; }
    .logistics-step.active .step-icon { border-color: #0061ff; background: #0061ff; color: #fff; box-shadow: 0 0 0 5px rgba(0,97,255,0.1); }
    .logistics-step.completed .step-icon { border-color: #10b981; background: #10b981; color: #fff; }
    .logistics-step.active h6 { color: #0061ff; font-weight: 700; }
    .ship-card { border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); background: #fff; }
    .instruction-card { background: #fff; border-radius: 12px; border-left: 4px solid #0061ff; padding: 15px; margin-top: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .instruction-card h6 { font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 10px; }
    .instruction-list { list-style: none; padding: 0; margin: 0; }
    .instruction-list li { font-size: 0.8rem; margin-bottom: 8px; color: #64748b; display: flex; align-items: flex-start; }
    .instruction-list li i { color: #0061ff; margin-right: 8px; margin-top: 2px; }  
    .card-title { font-size: 1.25rem; font-weight: 700; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; }
    .form-label { font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 6px; }
    .form-control, .form-select { border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; font-size: 0.95rem; }
    .form-control:focus { border-color: #0061ff; box-shadow: 0 0 0 3px rgba(0,97,255,0.05); }
    .form-control.is-invalid { border-color: #dc3545; }
    .service-option { border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; cursor: pointer; transition: 0.2s; position: relative; }
    .service-option.active { border-color: #0061ff; background: #f0f7ff; border-width: 2px; }
    .btn-logistics { background: #0061ff; color: #fff; padding: 12px 25px; border-radius: 8px; font-weight: 600; border: none; transition: 0.3s; }
    .btn-logistics:disabled { background: #94a3b8; cursor: not-allowed; }
    .step-content { display: none; }
    .step-content.active { display: block; animation: fadeIn 0.4s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .loader-inline { display: none; width: 14px; height: 14px; border: 2px solid #ccc; border-top: 2px solid #0061ff; border-radius: 50%; animation: spin 0.8s linear infinite; margin-left: 5px; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .search-container { position: relative; }
    .search-results { position: absolute; top: 100%; left: 0; right: 0; background: white; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 8px; display: none; max-height: 200px; overflow-y: auto; border: 1px solid #e2e8f0; }
    .search-item { padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; }
    .search-item:hover { background: #f0f7ff; color: #0061ff; }
    .success-overlay { display: none; text-align: center; padding: 40px; }
</style>
<div class="container py-5">
    <div id="successCard" class="success-overlay">
        <div class="card ship-card p-5 shadow-lg">
            <div class="mb-3"><i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i></div>
            <h2 class="fw-bold">Booking Successful!</h2>
            <div class="alert alert-info py-3 my-4">
                <h4 class="mb-0 fw-bold" id="tracking_id_text"></h4>
            </div>
            <p class="text-muted">Please save this Tracking ID for your reference.</p>
            <div class="mt-4">
                <button class="btn-logistics px-4" onclick="window.location.reload()">Book Another Shipment</button>
            </div>
        </div>
    </div>
    <div class="row" id="formRow">
        <div class="col-lg-3 d-none d-lg-block">
            <div class="logistics-step active" id="l-step-1"><div class="step-icon">1</div><h6>Sender Details</h6></div>
            <div class="logistics-step" id="l-step-2"><div class="step-icon">2</div><h6>Receiver Details</h6></div>
            <div class="logistics-step" id="l-step-3"><div class="step-icon">3</div><h6>Consignment Info</h6></div>
            <div class="logistics-step" id="l-step-4"><div class="step-icon">4</div><h6>Billing</h6></div>
            <div class="instruction-card">
                <h6><i class="bi bi-info-circle-fill me-2"></i>Instructions</h6>
                <ul class="instruction-list">
                    <li><i class="bi bi-check2-circle"></i> Correct pincode helps in faster delivery.</li>
                    <li><i class="bi bi-check2-circle"></i> Use GPS for accurate pickup coordinates.</li>
                    <li><i class="bi bi-check2-circle"></i> Cash payment during pickup only.</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-9">
            <form id="shipmentForm">
                <input type="hidden" name="type" value="add_shipment">
                <input type="hidden" name="payment_mode" value="COD">
                <div class="step-content active" id="step-1">
                    <div class="card ship-card p-4">
                        <h5 class="card-title"><i class="bi bi-box-arrow-in-right me-2 text-primary"></i>1. Pickup Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Sender Name</label><input type="text" name="s_name" class="form-control" value="<?= $logged_name ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Contact Number</label><input type="tel" name="s_mobile" class="form-control" value="<?= $logged_phone ?>" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="10 Digit Mobile" required></div>
                            <div class="col-12"><label class="form-label">Sender Email</label><input type="email" name="s_email" class="form-control" value="<?= $logged_email ?>" required></div>
                            <div class="col-md-3">
                                <label class="form-label">Pincode <span id="s_loader" class="loader-inline"></span></label>
                                <input type="text" name="s_pincode" id="s_pincode" class="form-control" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, ''); fetchPinData(this.value, 's_district', 's_state', 's_loader')" placeholder="6 Digits" required>
                            </div>
                            <div class="col-md-3"><label class="form-label">District</label><input type="text" name="s_district" id="s_district" class="form-control bg-light" readonly required></div>
                            <div class="col-md-3"><label class="form-label">State</label><input type="text" name="s_state" id="s_state" class="form-control bg-light" readonly required></div>
                            <div class="col-md-3"><label class="form-label">Country</label><input type="text" class="form-control bg-light" value="India" readonly></div>
                            <div class="col-12 search-container">
                                <label class="form-label d-flex justify-content-between">Full Pickup Address <span class="text-primary" style="cursor:pointer; font-size: 0.75rem;" onclick="detectGPS('s_addr', 's_pincode', 's_district', 's_state', 's_loader')"><i class="bi bi-geo-alt-fill"></i> Use Free GPS</span></label>
                                <textarea name="origin" id="s_addr" class="form-control" rows="2" placeholder="Start typing address..." oninput="freeSearch(this, 's_results', 's_pincode', 's_district', 's_state')" required></textarea>
                                <div id="s_results" class="search-results"></div>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn-logistics px-5" onclick="nextStep(1, 2)">Next: Receiver <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
                <div class="step-content" id="step-2">
                    <div class="card ship-card p-4">
                        <h5 class="card-title"><i class="bi bi-geo-alt me-2 text-success"></i>2. Delivery Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Receiver Name</label><input type="text" name="r_name" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label">Receiver Mobile</label><input type="tel" name="r_mobile" class="form-control" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="10 Digit Mobile" required></div>
                            <div class="col-12"><label class="form-label text-success">Receiver Email</label><input type="email" name="r_email" class="form-control" required></div>
                            <div class="col-md-3">
                                <label class="form-label">Pincode <span id="r_loader" class="loader-inline"></span></label>
                                <input type="text" name="r_pincode" id="r_pincode" class="form-control" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, ''); fetchPinData(this.value, 'r_district', 'r_state', 'r_loader')" placeholder="6 Digits" required>
                            </div>
                            <div class="col-md-3"><label class="form-label">District</label><input type="text" name="r_district" id="r_district" class="form-control bg-light" readonly required></div>
                            <div class="col-md-3"><label class="form-label">State</label><input type="text" name="r_state" id="r_state" class="form-control bg-light" readonly required></div>
                            <div class="col-md-3"><label class="form-label">Country</label><input type="text" class="form-control bg-light" value="India" readonly></div>
                            <div class="col-12 search-container">
                                <label class="form-label d-flex justify-content-between">Full Delivery Address <span class="text-success" style="cursor:pointer; font-size: 0.75rem;" onclick="detectGPS('r_addr', 'r_pincode', 'r_district', 'r_state', 'r_loader')"><i class="bi bi-geo-alt-fill"></i> Use Free GPS</span></label>
                                <textarea name="dest" id="r_addr" class="form-control" rows="2" placeholder="Search delivery location..." oninput="freeSearch(this, 'r_results', 'r_pincode', 'r_district', 'r_state')" required></textarea>
                                <div id="r_results" class="search-results"></div>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4" onclick="history.back()"><i class="bi bi-arrow-left"></i> Back</button>
                            <button type="button" class="btn-logistics" onclick="nextStep(2, 3)">Next: Package <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
                <div class="step-content" id="step-3">
                    <div class="card ship-card p-4">
                        <h5 class="card-title"><i class="bi bi-box-seam me-2 text-warning"></i>3. Shipment Content</h5>
                        <div class="row g-4">
                            <div class="col-md-8"><label class="form-label">Item Description</label><input type="text" name="item_name" class="form-control" placeholder="e.g. Clothes, Electronics" required></div>
                            <div class="col-md-4"><label class="form-label">Weight (kg)</label><input type="number" name="weight" id="weight" class="form-control" value="1" min="0.5" step="0.5" oninput="calculatePrice()" required></div>
                            <div class="col-12">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="service-option active" id="opt-Small" onclick="selectService('Small')">
                                            <input type="radio" name="item_size" value="Small" checked style="display:none"> Standard
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="service-option" id="opt-Medium" onclick="selectService('Medium')">
                                            <input type="radio" name="item_size" value="Medium" style="display:none"> Express
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="service-option" id="opt-Large" onclick="selectService('Large')">
                                            <input type="radio" name="item_size" value="Large" style="display:none"> Heavy Cargo
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4" onclick="history.back()"><i class="bi bi-arrow-left"></i> Back</button>
                            <button type="button" class="btn-logistics" onclick="nextStep(3, 4)">Next: Billing <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
                <div class="step-content" id="step-4">
                    <div class="card ship-card p-4">
                        <h5 class="card-title"><i class="bi bi-credit-card me-2 text-danger"></i>4. Finalize Booking</h5>
                        <div class="row">
                            <div class="col-md-6 border-end">
                                <label class="form-label mb-3">Payment Method</label>
                                <div class="service-option active">
                                    <i class="bi bi-cash-stack me-2 text-success"></i> <strong>Cash on Pickup</strong>
                                    <p class="small text-muted mb-0 mt-1">Pay to the rider during pickup.</p>
                                </div>
                            </div>
                            <div class="col-md-6 text-center pt-4 pt-md-0">
                                <div class="p-4 bg-light rounded h-100 d-flex flex-column justify-content-center">
                                    <small class="text-muted">TOTAL PAYABLE</small>
                                    <h1 class="fw-bold text-primary">₹<span id="display_amount">0</span></h1>
                                    <input type="hidden" name="amount" id="final_amount" value="0">
                                    <button type="submit" id="submitBtn" class="btn-logistics w-100 mt-4 shadow-sm">
                                        <span id="btnText">CONFIRM BOOKING</span>
                                    </button>
                                    <p class="small text-muted mt-3"><i class="bi bi-lock-fill"></i> Secure Booking Portal</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="history.back()"><i class="bi bi-arrow-left"></i> Back to Content</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// --- History & Step Navigation Logic ---
window.addEventListener('popstate', function(event) {
    if (event.state && event.state.step) {
        updateStepUI(event.state.step);
    }
});
window.onload = function() {
    calculatePrice();
    if (!history.state) {
        history.replaceState({step: 1}, "Step 1");
    }
};
function updateStepUI(step) {
    document.querySelectorAll('.step-content').forEach(s => s.classList.remove('active'));
    document.getElementById('step-' + step).classList.add('active');
    document.querySelectorAll('.logistics-step').forEach((el, index) => {
        let stepNum = index + 1;
        if (stepNum < step) {
            el.className = "logistics-step completed";
        } else if (stepNum == step) {
            el.className = "logistics-step active";
        } else {
            el.className = "logistics-step";
        }
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function nextStep(current, next) {
    const stepDiv = document.getElementById('step-' + current);
    const inputs = stepDiv.querySelectorAll('[required]');
    let valid = true;
    inputs.forEach(i => { 
        if(!i.value.trim()) { 
            i.classList.add('is-invalid'); 
            valid = false; 
        } else { 
            i.classList.remove('is-invalid'); 
            // --- STRICT VALIDATION LOGIC ---
            if(i.name.includes('mobile') && i.value.length !== 10) {
                Swal.fire('Error', 'The mobile number should be of 10 digits.', 'error');
                i.classList.add('is-invalid');
                valid = false;
            }
            // Pincode Check
            if(i.name.includes('pincode') && i.value.length !== 6) {
                Swal.fire('Error', 'Pincode should be of 6 digits.', 'error');
                i.classList.add('is-invalid');
                valid = false;
            }
        }
    });
    if (!valid) { 
        if(!Swal.isVisible()) Swal.fire('Incomplete', 'Please fill in all the fields correctly.', 'warning'); 
        return; 
    }
    history.pushState({step: next}, "Step " + next);
    updateStepUI(next);
}
//  Functions (Pincode, GPS, Search) ---
function fetchPinData(pin, dId, sId, loaderId) {
    if (pin.length === 6) {
        document.getElementById(loaderId).style.display = 'inline-block';
        fetch(`https://api.postalpincode.in/pincode/${pin}`)
        .then(r => r.json()).then(data => {
            document.getElementById(loaderId).style.display = 'none';
            if (data[0].Status === "Success") {
                document.getElementById(dId).value = data[0].PostOffice[0].District;
                document.getElementById(sId).value = data[0].PostOffice[0].State;
                document.getElementById(dId).classList.remove('is-invalid');
            } else {
                Swal.fire('Error', 'Invalid Pincode', 'error');
                document.getElementById(dId).value = ''; document.getElementById(sId).value = '';
            }
        }).catch(() => document.getElementById(loaderId).style.display = 'none');
    }
}
function detectGPS(addrId, pId, dId, sId, loaderId) {
    if (navigator.geolocation) {
        Swal.fire({ title: 'Detecting...', text: 'Fetching coordinates...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        navigator.geolocation.getCurrentPosition(pos => {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${pos.coords.latitude}&lon=${pos.coords.longitude}`)
            .then(r => r.json()).then(data => {
                Swal.close();
                document.getElementById(addrId).value = data.display_name;
                let pin = data.address.postcode || '';
                // Clean pin if it contains range
                pin = pin.split(' ')[0].replace(/[^0-9]/g, '');
                document.getElementById(pId).value = pin.substring(0,6);
                if(pin.length >= 6) fetchPinData(pin.substring(0,6), dId, sId, loaderId);
                else {
                    document.getElementById(dId).value = data.address.city || data.address.county || '';
                    document.getElementById(sId).value = data.address.state || '';
                }
            });
        }, () => { Swal.close(); Swal.fire('Error', 'GPS Denied', 'error'); });
    }
}
let searchTimer;
function freeSearch(input, resId, pId, dId, sId) {
    clearTimeout(searchTimer);
    const box = document.getElementById(resId);
    if (input.value.length < 4) { box.style.display = 'none'; return; }
    searchTimer = setTimeout(() => {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&q=${input.value}&countrycodes=in&limit=5`)
        .then(r => r.json()).then(data => {
            box.innerHTML = ''; box.style.display = 'block';
            data.forEach(item => {
                const d = document.createElement('div'); d.className = 'search-item';
                d.innerText = item.display_name;
                d.onclick = () => {
                    input.value = item.display_name;
                    let pin = item.address.postcode || '';
                    pin = pin.split(' ')[0].replace(/[^0-9]/g, '');
                    document.getElementById(pId).value = pin.substring(0,6);
                    if(pin.length >= 6) fetchPinData(pin.substring(0,6), dId, sId, pId.startsWith('s') ? 's_loader' : 'r_loader');
                    box.style.display = 'none';
                };
                box.appendChild(d);
            });
        });
    }, 500);
}
function calculatePrice() {
    let w = parseFloat(document.getElementById('weight').value) || 0;
    let size = document.querySelector('input[name="item_size"]:checked')?.value || 'Small';
    let base = size === 'Small' ? 60 : (size === 'Medium' ? 120 : 300);
    let total = Math.round(base + (w * 45));
    document.getElementById('display_amount').innerText = total;
    document.getElementById('final_amount').value = total;
}
function selectService(val) {
    document.querySelectorAll('[id^="opt-"]').forEach(el => el.classList.remove('active'));
    document.getElementById('opt-' + val).classList.add('active');
    document.querySelector(`input[value="${val}"]`).checked = true;
    calculatePrice();
}
document.getElementById('shipmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true; btn.innerText = "Processing...";
    fetch('../routes/web.php', { method: 'POST', body: new FormData(this) })
    .then(r => r.text()).then((res) => {
        Swal.fire({
            title: 'Success!',
            text: 'Your Tracking ID: ' + res,
            icon: 'success',
            confirmButtonColor: '#0061ff'
        }).then(() => {
            document.getElementById('formRow').style.display = 'none';
            document.getElementById('successCard').style.display = 'block';
            document.getElementById('tracking_id_text').innerText = res;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }).catch(err => {
        Swal.fire('Error', 'Something went wrong!', 'error');
        btn.disabled = false; btn.innerText = "CONFIRM BOOKING";
    });
});
</script>
<?php include "footer.php"; ?>