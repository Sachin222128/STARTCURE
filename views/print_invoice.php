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
    die("Invoice not found!");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice_<?php echo $row['tracking_id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .invoice-box {
            max-width: 850px; margin: 40px auto; padding: 40px;
            background: #fff; border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-top: 8px solid #0d6efd;
            position: relative; overflow: hidden;
        }
        /* Watermark */
        .invoice-box::after {
            content: "STARTCURE"; position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px; color: rgba(13, 110, 253, 0.03);
            font-weight: bold; z-index: 0; pointer-events: none;
        }
        .invoice-header { border-bottom: 1px solid #eee; padding-bottom: 25px; margin-bottom: 30px; position: relative; z-index: 1; }
        .company-name { color: #0d6efd; font-weight: 800; font-size: 32px; letter-spacing: -1px; }
        .table { position: relative; z-index: 1; }
        .table thead { background-color: #f8f9fa; border-top: 2px solid #eee; }
        .table thead th { text-transform: uppercase; font-size: 12px; letter-spacing: 1px; color: #666; border: none; }
        .section-title { font-size: 13px; font-weight: 700; color: #0d6efd; text-transform: uppercase; margin-bottom: 10px; display: block; border-left: 3px solid #0d6efd; padding-left: 10px; }
        .qr-code { border: 1px solid #eee; padding: 5px; border-radius: 8px; width: 100px; height: 100px; background: #fafafa; display: flex; align-items: center; justify-content: center; }
        .footer-note { border-top: 1px solid #eee; padding-top: 20px; margin-top: 40px; font-size: 11px; }
        
        @media print { 
            .no-print { display: none !important; } 
            body { background: #fff; padding: 0; }
            .invoice-box { box-shadow: none; margin: 0; width: 100%; max-width: 100%; border: none; }
        }
    </style>
</head>
<body>
<div class="invoice-box">
    <div class="invoice-header d-flex justify-content-between align-items-start">
        <div>
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-box-seam-fill text-primary fs-1 me-3"></i>
                <div>
                    <div class="company-name">STARTCURE LOGISTICS</div>
                    <p class="mb-0 text-muted small fw-medium">Fast & Secure Delivery Services • GSTIN: 27AAAAA0000A1Z5</p>
                </div>
            </div>
            <p class="small text-muted mb-0"><i class="bi bi-geo-alt-fill me-1"></i> Mumbai, Maharashtra, India - 400001</p>
        </div>
        <div class="text-end">
            <h2 class="fw-bold text-uppercase mb-1" style="letter-spacing: 2px; color: #444;">Invoice</h2>
            <div class="badge bg-primary px-3 py-2 mb-2">#<?php echo $row['tracking_id']; ?></div>
            <p class="small text-muted mb-0">Date: <strong><?php echo date('d M, Y'); ?></strong></p>
        </div>
    </div>
    <div class="row mb-5 position-relative" style="z-index: 1;">
        <div class="col-5">
            <span class="section-title">Sender Details</span>
            <div class="ps-3">
                <p class="mb-1 fw-bold fs-5"><?php echo $row['sender_name']; ?></p>
                <p class="mb-1 text-muted small"><i class="bi bi-telephone-fill me-1"></i> +91 <?php echo $row['sender_mobile']; ?></p>
                <p class="mb-0 text-muted small"><i class="bi bi-geo-fill me-1"></i> <?php echo $row['origin']; ?></p>
            </div>
        </div>
        <div class="col-2 text-center d-flex align-items-center justify-content-center">
            <i class="bi bi-arrow-right-circle-fill fs-2 text-light"></i>
        </div>
        <div class="col-5">
            <span class="section-title text-end" style="border-left:0; border-right:3px solid #0d6efd; padding-right:10px;">Receiver Details</span>
            <div class="pe-3 text-end">
                <p class="mb-1 fw-bold fs-5"><?php echo $row['receiver_name']; ?></p>
                <p class="mb-1 text-muted small"><?php echo $row['receiver_mobile']; ?> <i class="bi bi-telephone-fill ms-1"></i></p>
                <p class="mb-0 text-muted small"><?php echo $row['destination']; ?> <i class="bi bi-geo-fill ms-1"></i></p>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 50%;">Service Description</th>
                    <th class="text-center">Tracking ID</th>
                    <th class="text-end">Service Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr style="height: 80px;">
                    <td>
                        <div class="fw-bold text-dark">Courier Service - <?php echo $row['item_name']; ?></div>
                        <div class="small text-muted">Standard Logistics & Safe Handling Charges</div>
                    </td>
                    <td class="text-center">
                        <span class="font-monospace fw-bold text-primary"><?php echo $row['tracking_id']; ?></span>
                    </td>
                    <td class="text-end fw-bold">₹<?php echo number_format($row['amount'], 2); ?></td>
                </tr>
            </tbody>
            <tfoot class="border-top-0">
                <tr>
                    <td rowspan="3" class="border-0 pt-4">
                        <div class="qr-code">
                            <i class="bi bi-qr-code fs-1 text-muted"></i>
                        </div>
                        <p class="extra-small text-muted mt-2 mb-0" style="font-size: 10px;">Scan to Track Shipment</p>
                    </td>
                    <th class="text-end border-0 pt-4 fw-normal text-muted">Payment Status:</th>
                    <th class="text-end border-0 pt-4">
                        <span class="badge <?php echo ($row['payment_status'] == 'Paid' ? 'bg-success' : 'bg-warning text-dark'); ?> text-uppercase">
                            <?php echo $row['payment_status']; ?>
                        </span>
                    </th>
                </tr>
                <tr>
                    <th class="text-end border-0 fs-5 fw-bold text-dark">Grand Total:</th>
                    <th class="text-end border-0 fs-5 fw-bold text-primary">₹<?php echo number_format($row['amount'], 2); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="footer-note">
        <div class="row">
            <div class="col-8">
                <p class="fw-bold mb-1 text-dark">Terms & Conditions:</p>
                <ul class="text-muted ps-3 mb-0">
                    <li>This is a computer-generated invoice and does not require a physical signature.</li>
                    <li>Items are subject to StartCure Logistics' standard terms of carriage.</li>
                    <li>For any discrepancies, contact support@startcure.com within 24 hours.</li>
                </ul>
            </div>
            <div class="col-4 text-end align-self-end">
                <div class="mt-4">
                    <p class="mb-0 fw-bold text-primary">Thank You!</p>
                    <p class="small text-muted">For choosing StartCure</p>
                </div>
            </div>
        </div>
    </div>
    <div class="no-print mt-5 d-flex justify-content-center gap-3">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-5 shadow-sm rounded-pill">
            <i class="bi bi-printer-fill me-2"></i> Print Official Invoice
        </button>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-lg px-5 rounded-pill">
            <i class="bi bi-arrow-left me-2"></i> Dashboard
        </a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>