<?php
// Get parameters from URL
$receiptId = $_GET['id'] ?? 'N/A';
$service = $_GET['service'] ?? 'N/A';
$caregiver = $_GET['caregiver'] ?? 'N/A';
$hours = $_GET['hours'] ?? '0';
$amount = $_GET['amount'] ?? '0.00';
$date = $_GET['date'] ?? date('M d, Y');
$clientName = $_GET['client'] ?? 'John Doe';

// Calculate totals
$subtotal = floatval($amount);
$tax = 0; // Healthcare services are tax-exempt in NY
$total = $subtotal + $tax;
$rate = $hours > 0 ? $subtotal / floatval($hours) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt_<?php echo htmlspecialchars($receiptId); ?>.pdf</title>
    <meta charset="UTF-8">
    <style>
        @page { size: A4; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: white; margin: 0; padding: 0; color: #000; }
        .pdf-container { width: 210mm; min-height: 297mm; margin: 20px auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); position: relative; }
        .pdf-header { padding: 50px 50px 30px; border-bottom: 2px solid #000; }
        .company-header { margin-bottom: 15px; }
        .company-name { font-size: 24px; font-weight: 700; letter-spacing: 2px; color: #000; margin-bottom: 5px; }
        .company-tagline { font-size: 11px; color: #666; letter-spacing: 1px; text-transform: uppercase; }
        .company-info { font-size: 10px; line-height: 1.6; color: #666; margin-top: 15px; }
        .pdf-body { padding: 50px; }
        .receipt-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; padding-bottom: 15px; border-bottom: 1px solid #000; }
        .receipt-title { font-size: 20px; font-weight: 700; color: #000; letter-spacing: 3px; }
        .status-badge { border: 2px solid #000; color: #000; padding: 6px 16px; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; }
        .receipt-info { display: flex; justify-content: space-between; margin-bottom: 40px; gap: 40px; }
        .info-column h3 { font-size: 10px; color: #000; margin-bottom: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border-bottom: 1px solid #000; padding-bottom: 5px; }
        .info-column p { font-size: 11px; margin: 5px 0; color: #333; line-height: 1.6; }
        .info-column p strong { color: #000; font-weight: 600; }
        .services-section { margin: 40px 0; }
        .services-table { width: 100%; border-collapse: collapse; margin: 20px 0; border: 1px solid #000; }
        .services-table th { background: white; padding: 12px 16px; text-align: left; border-bottom: 2px solid #000; font-size: 10px; font-weight: 700; color: #000; text-transform: uppercase; letter-spacing: 1px; }
        .services-table td { padding: 14px 16px; border-bottom: 1px solid #ddd; font-size: 11px; color: #000; }
        .service-desc { font-weight: 600; color: #000; font-size: 12px; }
        .service-details { font-size: 10px; color: #666; margin-top: 3px; }
        .totals-section { margin-top: 40px; }
        .totals-table { width: 300px; margin-left: auto; }
        .totals-table td { padding: 8px 0; font-size: 12px; color: #000; }
        .totals-table .subtotal { border-top: 1px solid #ddd; padding-top: 10px; }
        .totals-table .total { border-top: 2px solid #000; padding-top: 12px; margin-top: 8px; font-weight: 700; font-size: 14px; color: #000; }
        .pdf-footer { padding: 30px 50px; text-align: center; font-size: 9px; color: #666; border-top: 1px solid #ddd; line-height: 1.8; }
        @media print { 
            body { background: white; padding: 0; } 
            .pdf-container { box-shadow: none; margin: 0; } 
            .no-print { display: none; } 
        }
        .print-btn { position: fixed; top: 20px; right: 20px; padding: 16px 32px; background: #007bff; color: white; border: none; border-radius: 12px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 1000; font-size: 16px; }
        .print-btn:hover { background: #0056b3; }
        .print-btn i { font-size: 18px; margin-right: 8px; }
    </style>
</head>
<body>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <button class="print-btn no-print" onclick="window.print();"><i class="bi bi-printer-fill"></i> Print Receipt</button>
    
    <div class="pdf-container">
        <div class="pdf-header">
            <div class="company-header">
                <div class="company-name">CAS PRIVATE CARE LLC</div>
                <div class="company-tagline">Comfort and Support</div>
            </div>
            <div class="company-info">
                123 Healthcare Avenue, New York, NY 10001<br>
                Phone: (646) 282-8282 | Email: billing@casprivatecare.com<br>
                License: NYC-PC-2024-001 | Tax ID: 12-3456789
            </div>
        </div>
        
        <div class="pdf-body">
            <div class="receipt-header">
                <div class="receipt-title">RECEIPT</div>
                <div class="status-badge">PAID</div>
            </div>
            
            <div class="receipt-info">
                <div class="info-column">
                    <h3>BILL TO:</h3>
                    <p><strong><?php echo htmlspecialchars($clientName); ?></strong></p>
                    <p>456 Client Street</p>
                    <p>New York, NY 10002</p>
                    <p><?php echo strtolower(str_replace(' ', '.', $clientName)); ?>@email.com</p>
                </div>
                <div class="info-column" style="text-align: right;">
                    <h3>RECEIPT DETAILS:</h3>
                    <p><strong>Receipt #:</strong> <?php echo htmlspecialchars($receiptId); ?></p>
                    <p><strong>Service Date:</strong> <?php echo htmlspecialchars($date); ?></p>
                    <p><strong>Issue Date:</strong> <?php echo date('M d, Y'); ?></p>
                    <p><strong>Payment Method:</strong> Visa ****4532</p>
                    <p><strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">PAID IN FULL</span></p>
                </div>
            </div>
            
            <div class="services-section">
                <table class="services-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">DESCRIPTION</th>
                            <th style="width: 15%; text-align: center;">HOURS</th>
                            <th style="width: 15%; text-align: right;">RATE</th>
                            <th style="width: 20%; text-align: right;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="service-desc"><?php echo htmlspecialchars($service); ?></div>
                                <div class="service-details">Caregiver: <?php echo htmlspecialchars($caregiver); ?></div>
                                <div class="service-details">Professional healthcare services</div>
                            </td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($hours); ?>h</td>
                            <td style="text-align: right;">$<?php echo number_format($rate, 2); ?>/hr</td>
                            <td style="text-align: right; font-weight: bold;">$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="totals-section">
                <table class="totals-table">
                    <tr class="subtotal">
                        <td>Subtotal:</td>
                        <td style="text-align: right;">$<?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Sales Tax:</td>
                        <td style="text-align: right;">$0.00</td>
                    </tr>
                    <tr class="total">
                        <td><strong>TOTAL PAID:</strong></td>
                        <td style="text-align: right;"><strong>$<?php echo number_format($total, 2); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="pdf-footer">
            <p><strong>Thank you for choosing CAS Private Care LLC!</strong></p>
            <p>This document serves as your official receipt for services rendered.</p>
            <p>For questions regarding this receipt, please contact billing@casprivatecare.com</p>
            <p style="margin-top: 10px;">© 2026 CAS Private Care LLC. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
