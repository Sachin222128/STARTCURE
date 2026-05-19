<?php 
// 1. Header & DB
include "header.php"; 
include "../app/db_connection.php"; 

// Check if it is a Bulk AWB request
$is_bulk = isset($_GET['bulk_awb']) && !empty(trim($_GET['bulk_awb']));
$bulk_shipments = [];

if ($is_bulk) {
    $bulk_input = $_GET['bulk_awb'];
    // String ko comma ke basis par todd kar array banaya
    $awb_array = explode(',', $bulk_input);
    // Extra spaces hataye aur empty cells filter kiye
    $awb_array = array_filter(array_map('trim', $awb_array));

    if (!empty($awb_array)) {
        // Sanitize loop to completely neutralize SQL Injection
        $sanitized_awbs = array_map(function($awb) use ($conn) {
            return mysqli_real_escape_string($conn, $awb);
        }, $awb_array);
        
        $sql_in_clause = "'" . implode("','", $sanitized_awbs) . "'";
        // App ke table column structural constraints ke mutabik query execute ki
        $sql = "SELECT * FROM shipments WHERE tracking_id IN ($sql_in_clause) ORDER BY id DESC";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $bulk_shipments[] = $row;
            }
        }
    }
} else {
    // Input for single tracking (Untouched)
    $tid = isset($_GET['tid']) ? mysqli_real_escape_string($conn, trim($_GET['tid'])) : '';
    $shipment = null;
    if (!empty($tid)) {
        $sql = "SELECT * FROM shipments 
                WHERE tracking_id = '$tid' 
                OR sender_mobile = '$tid' 
                OR receiver_mobile = '$tid' 
                ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $shipment = $result->fetch_assoc();
        }
    }
}
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<style>
    /* Professional Tracking CSS */
    .track-line { display: flex; justify-content: space-between; align-items: center; position: relative; margin: 40px 0; }
    .track-line::before { content: ''; position: absolute; top: 15px; left: 5%; right: 5%; height: 4px; background: #e0e0e0; z-index: 1; }
    .step { position: relative; z-index: 2; text-align: center; width: 25%; }
    .dot { width: 30px; height: 30px; background: #fff; border: 4px solid #e0e0e0; border-radius: 50%; margin: 0 auto 10px; transition: 0.3s; }
    .step.completed .dot { border-color: #f17b21; background: #f17b21; box-shadow: 0 0 10px rgba(241, 123, 33, 0.5); }
    .track-line .step-text { font-size: 0.75rem; font-weight: 600; color: #888; text-transform: uppercase; }
    .step.completed .step-text { color: #f17b21; }
    .timeline-item { border-left: 3px solid #f17b21; position: relative; padding-left: 25px; padding-bottom: 25px; }
    .timeline-dot { position: absolute; left: -11px; top: 0; width: 18px; height: 18px; background: #f17b21; border-radius: 50%; border: 3px solid #fff; }
    
    /* Strict Layout Dynamic Map Viewport Rules */
    #startcure-tracking-map {
        height: 420px !important;
        width: 100% !important;
        border-radius: 0 0 12px 12px;
        background-color: #f4f4f4;
        position: relative;
        z-index: 1;
    }
    /* Zepto Style Live ETA Header Widget */
    .zepto-eta-bar {
        background: linear-gradient(90deg, #ff3f6c 0%, #f17b21 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 14px 20px;
    }
    /* Hide Leaflet Routing Text Control Overlay panel */
    .leaflet-routing-container { display: none !important; }
</style>
<div class="container mt-5 py-4">

    <?php if ($is_bulk): ?>
        <h3 class="mb-4 text-dark fw-bold"><i class="bi bi-boxes me-2 text-primary"></i>Bulk Tracking Results</h3>
        <?php if (!empty($bulk_shipments)): ?>
            <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 bg-white">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Tracking ID</th>
                                <th>Item Name</th>
                                <th>Destination</th>
                                <th>Payment Status</th>
                                <th>Current Status</th>
                                <th class="text-center pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bulk_shipments as $item): 
                                $b_color = ($item['payment_status'] == 'Paid') ? 'success' : 'danger';
                                $s_color = 'secondary';
                                if ($item['status'] == 'Booked') $s_color = 'info text-dark';
                                if ($item['status'] == 'In Transit') $s_color = 'warning text-dark';
                                if ($item['status'] == 'Out for Delivery') $s_color = 'primary';
                                if ($item['status'] == 'Delivered') $s_color = 'success';
                            ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">#<?php echo htmlspecialchars($item['tracking_id']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['destination']); ?></td>
                                    <td><span class="badge bg-light text-<?php echo $b_color; ?> border border-<?php echo $b_color; ?>"><?php echo htmlspecialchars($item['payment_status']); ?></span></td>
                                    <td><span class="badge bg-<?php echo $s_color; ?>"><?php echo htmlspecialchars($item['status']); ?></span></td>
                                    <td class="text-center pe-4">
                                        <a href="track_result.php?tid=<?php echo urlencode($item['tracking_id']); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger shadow-sm text-center" style="border-radius: 15px;">
                No shipments found for the entered AWB numbers. Please check and try again.
            </div>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="../index.php" class="btn btn-primary px-5 rounded-pill shadow-sm">Back to Home</a>
        </div>

    <?php else: ?>
        <?php if ($shipment): 
            $status = $shipment['status']; 
            $booking_date = !empty($shipment['created_at']) ? $shipment['created_at'] : date('Y-m-d H:i:s');
            $expected_date = date('d M Y', strtotime($booking_date . ' + 4 days'));
            $p_color = ($shipment['payment_status'] == 'Paid') ? 'success' : 'danger';
        ?>  
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h5 class="text-muted mb-1">Tracking ID: <span class="text-dark fw-bold">#<?php echo $shipment['tracking_id']; ?></span></h5>
                            <p class="mb-0">Item: <strong><?php echo $shipment['item_name']; ?></strong> | Mode: <span class="badge bg-light text-dark border"><?php echo $shipment['payment_mode'] ?? 'Standard'; ?></span></p>
                        </div>
                        <div class="col-md-5 text-md-end mt-3 mt-md-0">
                            <span class="badge bg-<?php echo $p_color; ?> p-2 px-3 fs-6">
                                 <i class="bi bi-wallet2 me-2"></i>Payment: <?php echo $shipment['payment_status']; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-lg border-0 mb-4" style="border-radius: 15px;">
                 <div class="card-body p-4">
                     <div class="track-line">
                        <?php 
                            $stages = ['Booked', 'In Transit', 'Out for Delivery', 'Delivered'];
                            $current_idx = array_search($status, $stages);
                            if($current_idx === false && ($status == 'Pending' || $status == 'Picked Up')) $current_idx = 1;
                        ?>
                        <div class="step <?php echo ($current_idx >= 0) ? 'completed' : ''; ?>">
                            <div class="dot"></div><div class="step-text">Ordered</div>
                        </div>
                        <div class="step <?php echo ($current_idx >= 1 || $status == 'Picked Up') ? 'completed' : ''; ?>">
                            <div class="dot"></div><div class="step-text">Picked Up</div>
                        </div>
                        <div class="step <?php echo ($current_idx >= 2) ? 'completed' : ''; ?>">
                            <div class="dot"></div><div class="step-text">Out for Delivery</div>
                        </div>
                        <div class="step <?php echo ($current_idx >= 3) ? 'completed' : ''; ?>">
                            <div class="dot"></div><div class="step-text">Delivered</div>
                        </div>
                     </div> 
                     <div class="row text-center mt-4 g-3">
                         <div class="col-6 col-md-4 border-end">
                             <small class="text-muted d-block">Current Status</small>
                             <span class="fw-bold text-primary"><?php echo $status; ?></span>
                         </div>
                         <div class="col-6 col-md-4 border-end">
                             <small class="text-muted d-block">Destination</small>
                             <span class="fw-bold"><?php echo $shipment['destination']; ?></span>
                         </div>
                         <div class="col-12 col-md-4">
                             <small class="text-muted d-block">Est. Delivery</small>
                             <span class="fw-bold text-success"><?php echo ($status == 'Delivered') ? 'Delivered ✅' : $expected_date; ?></span>
                         </div>
                     </div>
                 </div>
            </div>

            <?php if ($status == 'In Transit' || $status == 'Out for Delivery' || $status == 'Picked Up'): ?>
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                    <div class="zepto-eta-bar d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block small text-light text-uppercase fw-bold" style="letter-spacing: 1px;">Live Tracking</span>
                            <h4 class="mb-0 fw-bold" id="zepto-eta-timer"><i class="bi bi-clock-history me-2"></i>Calculating ETA...</h4>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold" id="zepto-distance-meter">0.0 km away</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="startcure-tracking-map"></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($shipment['status'] == 'Delivered' && empty($shipment['rating'])): ?>
            <div class="card mt-4 border-0 shadow-sm mb-4" style="border-radius: 15px; background: #f8f9fa;">
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold text-dark mb-3">Rate Your Delivery Experience</h6>
                    <form action="../routes/auth_web.php" method="POST"> 
                        <input type="hidden" name="action" value="submit_feedback">
                        <input type="hidden" name="shipment_id" value="<?= $shipment['id'] ?>">
                        <input type="hidden" name="tid" value="<?= $shipment['tracking_id'] ?>">   
                        <div class="mb-3">
                            <select name="rating" class="form-select rounded-pill border-primary" required style="max-width: 300px; margin: 0 auto;">
                                <option value="">Select Stars ⭐</option>
                                <option value="5">5 Stars - Excellent</option>
                                <option value="4">4 Stars - Good</option>
                                <option value="3">3 Stars - Average</option>
                                <option value="2">2 Stars - Poor</option>
                                <option value="1">1 Star - Very Bad</option>
                            </select>
                        </div>
                        <textarea name="review" class="form-control mb-3 shadow-sm" rows="2" placeholder="Write a quick review..." style="border-radius: 10px; max-width: 500px; margin: 0 auto;"></textarea>
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow">Submit Feedback</button>
                    </form>
                </div>
            </div>
            <?php elseif (!empty($shipment['rating'])): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4 text-center" style="border-radius: 15px;">
                    <h6 class="mb-1 fw-bold">Your Rating: <?= $shipment['rating'] ?>/5 ⭐</h6>
                    <p class="mb-0 small italic text-muted">"<?= htmlspecialchars($shipment['review']) ?>"</p>
                </div>
            <?php endif; ?>
            <div class="card shadow-sm border-0 p-4 mb-4" style="border-radius: 15px;">
                <h5 class="fw-bold mb-4"><i class="bi bi-clock-history me-2 text-primary"></i>Detailed Logs</h5>
                <div class="timeline-container ms-2">
                    <?php
                    $logs = $conn->query("SELECT * FROM shipment_logs WHERE tracking_id = '".$shipment['tracking_id']."' ORDER BY id DESC");
                    if($logs && $logs->num_rows > 0):
                        while($log = $logs->fetch_assoc()):
                    ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="fw-bold text-dark"><?php echo $log['status']; ?></div>
                            <small class="text-muted"><?php echo date('d M Y, h:i A', strtotime($log['created_at'] ?? $log['updated_at'])); ?></small>
                            <p class="mb-0 mt-1 text-secondary small"><?php echo $log['description']; ?></p>
                        </div>
                    <?php endwhile; else: ?>
                        <p class='text-muted small italic'>Shipment process started.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-center">
                <button onclick="window.print()" class="btn btn-dark px-4 me-2"><i class="bi bi-printer me-2"></i>Print Status</button>
                <a href="../index.php" class="btn btn-outline-primary px-4">New Search</a>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="display-1 text-muted mb-4">🔍</div>
                <h3 class="text-danger fw-bold">No Record Found</h3>
                <p class="text-muted">Aapka entered ID "<?php echo htmlspecialchars($tid); ?>" It could be wrong.</p>
                <a href="../index.php" class="btn btn-primary mt-3 px-5 rounded-pill">Try Again</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php if (!$is_bulk && $shipment && ($status == 'In Transit' || $status == 'Out for Delivery' || $status == 'Picked Up')): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const trackingId = "<?php echo $shipment['tracking_id']; ?>";
    
    // Mumbai standard map center fallback coordinates
    var map = L.map('startcure-tracking-map').setView([19.0760, 72.8777], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    var riderMarker = null;
    var customerMarker = null;
    var routingControl = null;

    // Custom PNG Assets icons mapping
    var riderIcon = L.icon({
        iconUrl: 'https://cdn-icons-png.flaticon.com/512/2912/2912255.png', // Bike Icon
        iconSize: [40, 40], iconAnchor: [20, 40], popupAnchor: [0, -35]
    });

    var homeIcon = L.icon({
        iconUrl: 'https://cdn-icons-png.flaticon.com/512/619/619153.png', // Home Icon
        iconSize: [35, 35], iconAnchor: [17, 35]
    });

    // Static Customer Drop Location (Aapki shipment destination ke mutabik update hoga, abhi standard fallback test coords diye hain)
    // Real project me aap isko dynamic address parser se geocode kar sakte hain ya user coordinates use karein.
    const customerLat = 19.1155; 
    const customerLng = 72.8622;

    // Set permanent Home/Customer marker once
    customerMarker = L.marker([customerLat, customerLng], {icon: homeIcon})
        .addTo(map)
        .bindPopup("<b>Your Delivery Location</b>")
        .openPopup();

    function pollRiderLiveLocation() {
        fetch(`../routes/get_rider_location.php?tracking_id=${trackingId}`)
            .then(res => res.json())
            .then(data => {
                if(data.error || !data.current_lat || !data.current_lng) {
                    console.warn("Location packets streaming...");
                    return;
                }

                let riderLat = parseFloat(data.current_lat);
                let riderLng = parseFloat(data.current_lng);

                // Update or Create Rider Bike Marker position
                if (riderMarker) {
                    riderMarker.setLatLng([riderLat, riderLng]);
                } else {
                    riderMarker = L.marker([riderLat, riderLng], {icon: riderIcon})
                        .addTo(map)
                        .bindPopup(`<b>Rider: ${data.rider_name}</b><br>📞 ${data.rider_phone}`);
                }

                // Zepto Routing Polyline Engine: Draws path between Rider and Customer
                if (routingControl) {
                    routingControl.setWaypoints([
                        L.latLng(riderLat, riderLng),
                        L.latLng(customerLat, customerLng)
                    ]);
                } else {
                    routingControl = L.Routing.control({
                        waypoints: [
                            L.latLng(riderLat, riderLng),
                            L.latLng(customerLat, customerLng)
                        ],
                        createMarker: function() { return null; }, // Hide default routing engine flags
                        lineOptions: {
                            styles: [{ color: '#ff3f6c', opacity: 0.8, weight: 6 }] // Zepto pink route polyline track
                        },
                        addWaypoints: false,
                        draggableWaypoints: false
                    }).addTo(map);

                    // Capture real distance matrix route data to update ETA
                    routingControl.on('routesfound', function(e) {
                        var routes = e.routes;
                        var summary = routes[0].summary;
                        
                        // Rounding off Time and Distance
                        var distanceKm = (summary.totalDistance / 1000).toFixed(1);
                        var travelTimeMins = Math.round(summary.totalTime / 60);

                        // Zepto UI Widgets real-time updates
                        document.getElementById('zepto-eta-timer').innerHTML = `<i class="bi bi-bicycle me-2"></i>Arriving in ${travelTimeMins} mins`;
                        document.getElementById('zepto-distance-meter').innerText = `${distanceKm} km away`;
                    });
                }
            })
            .catch(err => console.error("Zepto dynamic routing engine failed:", err));
    }

    setInterval(pollRiderLiveLocation, 12000); // Pool coordinates packet loop every 12 seconds
    pollRiderLiveLocation();
});
</script>
<?php endif; ?>

<?php include "footer.php"; ?>