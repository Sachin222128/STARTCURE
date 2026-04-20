<?php 
include('../app/db_connection.php'); 
include('header.php');
$query = mysqli_query($conn, "SELECT * FROM faqs");
?>

<div class="container py-5">
    <h2 class="text-center mb-5 fw-bold text-primary">Frequently Asked Questions</h2>
    <div class="accordion shadow-sm" id="faqAccordion">
        <?php 
        if (mysqli_num_rows($query) > 0) {
            $i = 1; 
            while($row = mysqli_fetch_assoc($query)) { 
        ?>
        <div class="accordion-item border-0 mb-3 rounded shadow-sm">
            <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                <button class="accordion-button <?php echo ($i == 1) ? '' : 'collapsed'; ?> fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>" aria-expanded="<?php echo ($i == 1) ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $i; ?>">
                    <?php echo htmlspecialchars($row['question']); ?>
                </button>
            </h2>
            <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse <?php echo ($i == 1) ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $i; ?>" data-bs-parent="#faqAccordion">
                <div class="accordion-body text-muted bg-light">
                    <?php echo htmlspecialchars($row['answer']); ?>
                </div>
            </div>
        </div>
        <?php 
            $i++; 
            } 
        } else {
            echo "<p class='text-center text-muted'>No FAQs available at the moment.</p>";
        }
        ?>
    </div>
</div>

<?php include('footer.php'); ?>