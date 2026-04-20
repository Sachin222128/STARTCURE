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
                    
                    <div class="mb-4">
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
                    
                    <button type="submit" name="calc" class="btn btn-primary w-100 py-2 fw-bold">Calculate Estimated Cost</button>
                </form>

                <?php if(isset($_POST['calc'])) {
                    $total = (float)$_POST['weight'] * (float)$_POST['rate'];
                    echo "<div class='alert alert-success mt-4 border-0 shadow-sm'>
                            <i class='bi bi-check-circle-fill me-2'></i> Estimated Cost: <strong>₹" . number_format($total, 2) . "</strong>
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