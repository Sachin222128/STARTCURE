<?php
include('db_connection.php'); // Ensure path is correct

if(isset($_POST['pincode'])) {
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $query = mysqli_query($conn, "SELECT price_per_kg, location FROM rate_chart WHERE pincode = '$pincode'");
    
    if(mysqli_num_rows($query) > 0) {
        $rate_data = mysqli_fetch_assoc($query);
        
        // Senior Dev Injection: Dynamic API computation for Multi-Currency & GST Support
        // Agar dynamic checkout request ke dauran weight aur currency variables aate hain
        if (isset($_POST['weight'])) {
            $weight = (float)$_POST['weight'];
            $rate = (float)$rate_data['price_per_kg'];
            $currency = isset($_POST['currency']) ? $_POST['currency'] : 'INR';
            
            $base_total_inr = $weight * $rate;
            $gst_amount_inr = $base_total_inr * 0.18; // 18% GST Support Baseline
            $final_total_inr = $base_total_inr + $gst_amount_inr;
            
            // Appending corporate pricing block configurations to existing JSON array response
            $rate_data['base_price_inr'] = $base_total_inr;
            $rate_data['gst_amount_inr'] = $gst_amount_inr;
            $rate_data['final_total_inr'] = $final_total_inr;
            $rate_data['currency_used'] = $currency;
            
            if ($currency === 'USD') {
                $rate_data['final_total_converted'] = $final_total_inr / 85.00; // 1 USD = 85 INR
            } else {
                $rate_data['final_total_converted'] = $final_total_inr;
            }
        }
        
        echo json_encode(['status' => 'found', 'data' => $rate_data]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
}
?>