<?php 
include('../app/db_connection.php'); 
include('header.php');
$query = mysqli_query($conn, "SELECT * FROM services");
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold text-primary display-5">Our Logistics Services</h2>
        <p class="text-muted">Reliable, fast, and secure delivery solutions tailored for you.</p>
    </div>
    
    <div class="row">
        <?php 
        if (mysqli_num_rows($query) > 0) {
            while($row = mysqli_fetch_assoc($query)) { 
        ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 transition-card" style="transition: transform 0.3s ease;">
                <div class="card-body p-4">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-box-seam fs-1"></i>
                    </div>
                    <h4 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($row['service_name']); ?></h4>
                    <p class="card-text text-muted mt-3"><?php echo htmlspecialchars($row['description']); ?></p>
                </div>
                <div class="card-footer bg-white border-0 pb-4 px-4">
                    <a href="<?php echo $base_url; ?>views/book.php" class="btn btn-outline-primary rounded-pill px-4">Book Now</a>
                </div>
            </div>
        </div>
        <?php 
            } 
        } else {
            echo "<div class='col-12 text-center'><p class='text-muted'>Currently, no services are listed.</p></div>";
        }
        ?>
    </div>
</div>

<style>
    .transition-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important;
    }
</style>

<?php include('footer.php'); ?>