<?php
// app/email_helper.php

function sendAutoEmailReceipt($customer_email, $customer_name, $booking_details) {
    // ⚠️ Settings: Apne Gmail ki details dalo
    $smtp_user = 'YOUR_GMAIL_ID@gmail.com'; 
    $smtp_pass = 'YOUR_16_DIGIT_APP_PASSWORD'; // Google se nikala hua App Password (bina space ke)
    
    $to = $customer_email;
    $subject = "Booking Confirmed! Order #" . $booking_details['tracking_id'];

    // HTML Email Layout Design
    $message = "
    <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; border: 1px solid #ddd; border-radius: 8px; padding: 20px;'>
        <h2 style='color: #f17b21; text-align: center;'>STARTCURE Logistics</h2>
        <p>Hi <b>$customer_name</b>,</p>
        <p>Aapka order successfully book ho gaya hai! Details niche hain:</p>
        <hr style='border: 0; border-top: 1px solid #eee;'>
        <p><b>Tracking ID:</b> #" . $booking_details['tracking_id'] . "</p>
        <p><b>Item Name:</b> " . $booking_details['item_name'] . "</p>
        <p><b>Total Amount Paid:</b> ₹" . number_format($booking_details['total_price'], 2) . "</p>
        <hr style='border: 0; border-top: 1px solid #eee;'>
        <p style='text-align: center; color: #888;'>Thank you for using STARTCURE.</p>
    </div>";

    // --- Pure PHP Dynamic Socket Programming (Direct SMTP Protocol) ---
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'To: <' . $to . '>',
        'From: STARTCURE Logistics <' . $smtp_user . '>',
        'Subject: ' . $subject
    ];

    // Connect to Gmail Server directly on secure port 465
    $socket = fsockopen("ssl://smtp.gmail.com", 465, $errno, $errstr, 15);
    if (!$socket) return false;

    // Helper functions commands handle karne ke liye
    function get_response($socket) {
        $response = "";
        while ($str = fgets($socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $response;
    }

    get_response($socket);
    
    // Server ko Hello bolo aur login karo
    fwrite($socket, "EHLO localhost\r\n"); get_response($socket);
    fwrite($socket, "AUTH LOGIN\r\n"); get_response($socket);
    fwrite($socket, base64_encode($smtp_user) . "\r\n"); get_response($socket);
    fwrite($socket, base64_encode($smtp_pass) . "\r\n"); get_response($socket);

    // Sender aur Receiver protocols setup karo
    fwrite($socket, "MAIL FROM: <$smtp_user>\r\n"); get_response($socket);
    fwrite($socket, "RCPT TO: <$to>\r\n"); get_response($socket);
    
    // Data stream start karo aur email body bhejo
    fwrite($socket, "DATA\r\n"); get_response($socket);
    fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $message . "\r\n.\r\n"); get_response($socket);
    
    // Connection close karo
    fwrite($socket, "QUIT\r\n"); get_response($socket);
    fclose($socket);

    return true; 
}
?>