<?php
include "../app/db_connection.php";
// Dashboard se ID aayegi - Secure way using Prepared Statement
$id = $_GET['id'] ?? 0; 
$stmt = $conn->prepare("SELECT * FROM shipments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
} else {
    die("Label not found!");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Label_<?php echo $row['tracking_id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        .label-container {
            width: 100mm; 
            min-height: 150mm;
            margin: 20px auto;
            background: #fff;
            border: 2px solid #000;
            padding: 0;
            position: relative;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .label-header {
            border-bottom: 2px solid #000;
            padding: 10px;
            background: #000;
            color: #fff;
            text-align: center;
        }
        .barcode-section {
            text-align: center;
            padding: 15px 10px;
            border-bottom: 2px solid #000;
        }
        .barcode-font {
            font-family: 'Libre Barcode 128', cursive;
            font-size: 65px;
            line-height: 1;
            margin-bottom: 5px;
        }
        .awb-text {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 2px;
        }
        .address-grid {
            display: grid;
            grid-template-columns: 1fr;
        }
        .address-block {
            padding: 12px;
            border-bottom: 1px solid #000;
        }
        .receiver-block {
            background-color: #fff;
            min-height: 120px;
        }
        .label-type {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            position: absolute;
            right: 0;
            top: 100px;
            background: #000;
            color: #fff;
            padding: 10px 2px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .footer-info {
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
        }
        .cut-line {
            border-top: 1px dashed #666;
            margin-top: 20px;
            position: relative;
            text-align: center;
        }
        .cut-line::after {
            content: "✂ Scissors Cut Here";
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #f8f9fa;
            padding: 0 10px;
            font-size: 10px;
            color: #666;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; margin: 0; }
            .label-container { margin: 0; border: 2px solid #000; box-shadow: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="label-container">
        <div class="label-header">
            <h4 class="fw-bold mb-0" style="letter-spacing: 1px;">STARTCURE LOGISTICS</h4>
            <span class="small">Priority Surface Shipping</span>
        </div>
        <div class="label-type">Prepaid Shipment</div>
        <div class="barcode-section">
            <div class="barcode-font"><?php echo $row['tracking_id']; ?></div>
            <div class="awb-text">AWB: <?php echo strtoupper($row['tracking_id']); ?></div>
        </div>
        <div class="address-block receiver-block">
            <small class="fw-bold text-muted" style="font-size: 10px;">DELIVER TO:</small><br>
            <h5 class="fw-bold mb-1"><?php echo strtoupper($row['receiver_name']); ?></h5>
            <div style="font-size: 14px; line-height: 1.3;">
                <?php echo $row['destination']; ?><br>
                <strong>Phone: <?php echo $row['receiver_mobile']; ?></strong>
            </div>
        </div>
        <div class="address-block bg-light">
            <small class="fw-bold text-muted" style="font-size: 10px;">RETURN ADDRESS (SENDER):</small><br>
            <div style="font-size: 12px;">
                <strong><?php echo $row['sender_name']; ?></strong><br>
                <?php echo $row['origin']; ?><br>
                Ph: <?php echo $row['sender_mobile']; ?>
            </div>
        </div>
        <div class="footer-info">
            <div>
                <strong>ITEM:</strong> <?php echo strtoupper($row['item_name']); ?><br>
                <strong>DATE:</strong> <?php echo date('d-m-Y'); ?>
            </div>
            <div class="text-end">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=50x50&data=<?php echo $row['tracking_id']; ?>" alt="QR" style="width: 50px;">
            </div>
        </div>
    </div>
    <div class="cut-line no-print"></div>
    <div class="text-center no-print mt-4 pb-5">
        <button onclick="window.print()" class="btn btn-dark btn-lg px-5 shadow">
            <i class="bi bi-printer"></i> Print Shipping Label
        </button>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-lg px-4 ms-2">Back</a>
    </div>
</body>
</html>