<?php
// routes/get_rider_location.php
header('Content-Type: application/json');
include "../app/db_connection.php";

if (!isset($_GET['tracking_id']) || empty(trim($_GET['tracking_id']))) {
    echo json_encode(['error' => 'Missing Tracking ID parameter']);
    exit;
}
$tracking_id = mysqli_real_escape_string($conn, trim($_GET['tracking_id']));

// Strict Join Query: Location packet sync regardless of intermediate state
$sql = "SELECT s.status, d.name AS rider_name, d.phone AS rider_phone, d.current_lat, d.current_lng 
        FROM shipments s
        JOIN delivery_boys d ON s.delivery_boy_id = d.id
        WHERE s.tracking_id = '$tracking_id' LIMIT 1";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    // Strict Verification: Coordinates validation checkpoint
    if (empty($data['current_lat']) || empty($data['current_lng'])) {
        echo json_encode(['error' => 'Rider location data packets are currently empty']);
    } else {
        echo json_encode([
            'rider_name' => $data['rider_name'],
            'rider_phone' => $data['rider_phone'],
            'current_lat' => $data['current_lat'],
            'current_lng' => $data['current_lng'],
            'status' => $data['status']
        ]);
    }
} else {
    echo json_encode(['error' => 'Rider assignment profile context mismatch']);
}
exit;
?>