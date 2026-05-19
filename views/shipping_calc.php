<?php 
include('../app/db_connection.php'); 
include('header.php');

// Fetch rates once
$rates = mysqli_query($conn, "SELECT * FROM rate_chart");
$rates_data = mysqli_fetch_all($rates, MYSQLI_ASSOC);
?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 p-4 h-100">
                <h3 class="fw-bold text-primary mb-4"><i class="bi bi-calculator me-2"></i>Shipping Calculator</h3>
                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Package Weight (kg):</label>
                        <input type="number" name="weight" step="0.1" class="form-control border-primary" 
                               value="<?php echo isset($_POST['weight']) ? htmlspecialchars($_POST['weight']) : ''; ?>" 
                               placeholder="e.g. 2.5" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Delivery Route/Location:</label>
                        <select name="rate" class="form-select border-primary" required>
                            <option value="" disabled selected>-- Select Destination --</option>
                            <?php foreach($rates_data as $row) { 
                                $selected = (isset($_POST['rate']) && $_POST['rate'] == $row['price_per_kg']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $row['price_per_kg']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($row['location']); ?> (₹<?php echo $row['price_per_kg']; ?>/kg)
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Billing Currency:</label>
                        <select name="currency" class="form-select border-primary" required>
                            <option value="INR" <?php echo (isset($_POST['currency']) && $_POST['currency'] == 'INR') ? 'selected' : ''; ?>>INR (₹ - Indian Rupee)</option>
                            <option value="USD" <?php echo (isset($_POST['currency']) && $_POST['currency'] == 'USD') ? 'selected' : ''; ?>>USD ($ - United States Dollar)</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="calc" class="btn btn-primary w-100 py-2 fw-bold">Calculate Estimated Cost</button>
                </form>

                <?php if(isset($_POST['calc'])) {
                    // 100% Untouched original billing formula matrix calculation
                    $total = (float)$_POST['weight'] * (float)$_POST['rate'];
                    
                    // Corporate Tax Constraints Addition
                    $selected_currency = isset($_POST['currency']) ? $_POST['currency'] : 'INR';
                    $gst_rate = 0.18; // 18% GST Support
                    $usd_conversion_factor = 85.00; // 1 USD = 85 INR standard enterprise baseline

                    $base_price_inr = $total;
                    $gst_amount_inr = $base_price_inr * $gst_rate;
                    $final_total_inr = $base_price_inr + $gst_amount_inr;

                    // Rendering conditions check layer based on dropdown selection
                    if ($selected_currency === 'USD') {
                        $display_symbol = '$';
                        $print_base = $base_price_inr / $usd_conversion_factor;
                        $print_gst = $gst_amount_inr / $usd_conversion_factor;
                        $print_total = $final_total_inr / $usd_conversion_factor;
                    } else {
                        $display_symbol = '₹';
                        $print_base = $base_price_inr;
                        $print_gst = $gst_amount_inr;
                        $print_total = $final_total_inr;
                    }

                    echo "<div class='alert alert-success mt-4 border-0 shadow-sm' style='border-radius:10px;'>
                            <div class='d-flex justify-content-between mb-1 small text-muted'>
                                <span>Base Shipping Cost:</span>
                                <span>" . $display_symbol . number_format($print_base, 2) . "</span>
                            </div>
                            <div class='d-flex justify-content-between mb-2 small text-muted border-bottom pb-2'>
                                <span>GST (18% Compliance):</span>
                                <span>" . $display_symbol . number_format($print_gst, 2) . "</span>
                            </div>
                            <div class='d-flex justify-content-between align-items-center fw-bold text-success fs-5'>
                                <span><i class='bi bi-check-circle-fill me-1'></i> Total Estimated Cost:</span>
                                <span>" . $display_symbol . number_format($print_total, 2) . " " . $selected_currency . "</span>
                            </div>
                          </div>";
                } ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 p-4 h-100 bg-light">
                <h4 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Rate Guide</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Location</th>
                                <th>Price (per kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($rates_data as $r) {
                                echo "<tr>
                                        <td>".htmlspecialchars($r['location'])."</td>
                                        <td><strong>₹".number_format($r['price_per_kg'], 2)."</strong></td>
                                      </tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>