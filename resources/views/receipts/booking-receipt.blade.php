<!DOCTYPE html>
<html>
<head>
    <title>Receipt_{{ $receiptNumber }}.pdf</title>
    <meta charset="UTF-8">
    <style>
        @page { size: A4; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: white; margin: 0; padding: 0; color: #000; }
        .pdf-container { width: 210mm; min-height: 297mm; margin: 0 auto; background: white; position: relative; }
        
        /* Header */
        .pdf-header { padding: 50px 50px 30px; border-bottom: 3px solid #000; }
        .company-header { margin-bottom: 15px; }
        .company-name { font-size: 28px; font-weight: 700; letter-spacing: 3px; color: #000; margin-bottom: 5px; }
        .company-tagline { font-size: 12px; color: #666; letter-spacing: 1.5px; text-transform: uppercase; }
        .company-info { font-size: 10px; line-height: 1.8; color: #666; margin-top: 15px; }
        
        /* Body */
        .pdf-body { padding: 50px; }
        
        /* Receipt Header */
        .receipt-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 2px solid #000; }
        .receipt-title { font-size: 24px; font-weight: 700; color: #000; letter-spacing: 4px; }
        .status-badge { border: 2px solid #28a745; background: #28a745; color: white; padding: 8px 20px; font-size: 11px; font-weight: 700; letter-spacing: 2px; border-radius: 4px; }
        
        /* Receipt Info */
        .receipt-info { display: flex; justify-content: space-between; margin-bottom: 40px; gap: 40px; }
        .info-column { flex: 1; }
        .info-column h3 { font-size: 11px; color: #000; margin-bottom: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; border-bottom: 2px solid #000; padding-bottom: 6px; }
        .info-column p { font-size: 11px; margin: 6px 0; color: #333; line-height: 1.6; }
        .info-column p strong { color: #000; font-weight: 600; }
        .info-right { text-align: right; }
        
        /* Services Section */
        .services-section { margin: 40px 0; }
        .section-title { font-size: 12px; font-weight: 700; color: #000; margin-bottom: 15px; letter-spacing: 1.5px; text-transform: uppercase; }
        .services-table { width: 100%; border-collapse: collapse; margin: 20px 0; border: 2px solid #000; }
        .services-table th { background: #f8f9fa; padding: 14px 16px; text-align: left; border-bottom: 2px solid #000; font-size: 10px; font-weight: 700; color: #000; text-transform: uppercase; letter-spacing: 1.2px; }
        .services-table td { padding: 16px; border-bottom: 1px solid #ddd; font-size: 11px; color: #333; }
        .services-table tbody tr:last-child td { border-bottom: none; }
        .service-desc { font-weight: 600; color: #000; font-size: 12px; margin-bottom: 6px; }
        .service-details { font-size: 10px; color: #666; margin-top: 4px; line-height: 1.6; }
        
        /* Totals Section */
        .totals-section { margin-top: 40px; padding-top: 20px; border-top: 2px solid #ddd; }
        .totals-table { width: 350px; margin-left: auto; }
        .totals-table td { padding: 10px 0; font-size: 12px; color: #000; }
        .totals-table .subtotal { border-top: 1px solid #ddd; padding-top: 12px; margin-top: 8px; }
        .totals-table .total { border-top: 3px solid #000; padding-top: 15px; margin-top: 10px; font-weight: 700; font-size: 16px; color: #000; }
        
        /* Payment Info Box */
        .payment-info-box { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 30px 0; }
        .payment-info-box h3 { font-size: 11px; font-weight: 700; color: #000; margin-bottom: 12px; letter-spacing: 1.5px; text-transform: uppercase; }
        .payment-info-box p { font-size: 11px; color: #333; margin: 6px 0; line-height: 1.6; }
        .payment-info-box p strong { color: #000; }
        
        /* Footer */
        .pdf-footer { padding: 30px 50px; text-align: center; font-size: 9px; color: #666; border-top: 2px solid #ddd; line-height: 2; margin-top: 40px; }
        .pdf-footer strong { color: #000; font-size: 10px; }
        
        /* Thank You Note */
        .thank-you-note { background: #f8f9fa; border-left: 4px solid #28a745; padding: 20px; margin: 30px 0; }
        .thank-you-note p { font-size: 11px; color: #333; line-height: 1.8; margin: 5px 0; }
        .thank-you-note p:first-child { font-weight: 700; color: #000; font-size: 12px; }
        
        @media print { 
            body { background: white; padding: 0; } 
            .pdf-container { box-shadow: none; margin: 0; } 
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        <!-- Header -->
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
        
        <!-- Body -->
        <div class="pdf-body">
            <!-- Receipt Header -->
            <div class="receipt-header">
                <div class="receipt-title">PAYMENT RECEIPT</div>
                <div class="status-badge">✓ PAID</div>
            </div>
            
            <!-- Receipt Info -->
            <div class="receipt-info">
                <div class="info-column">
                    <h3>BILL TO:</h3>
                    <p><strong>{{ $clientName }}</strong></p>
                    <p>{{ $clientAddress }}</p>
                    <p>{{ $clientCity }}, NY {{ $clientZip }}</p>
                    <p>{{ $clientEmail }}</p>
                </div>
                <div class="info-column info-right">
                    <h3>RECEIPT DETAILS:</h3>
                    <p><strong>Receipt #:</strong> {{ $receiptNumber }}</p>
                    <p><strong>Booking ID:</strong> #{{ $booking->id }}</p>
                    <p><strong>Service Date:</strong> {{ $serviceDate }}</p>
                    <p><strong>Payment Date:</strong> {{ $paymentDate }}</p>
                    <p><strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">PAID IN FULL</span></p>
                </div>
            </div>
            
            <!-- Services Section -->
            <div class="services-section">
                <div class="section-title">SERVICE DETAILS</div>
                <table class="services-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">DESCRIPTION</th>
                            <th style="width: 15%; text-align: center;">DURATION</th>
                            <th style="width: 15%; text-align: center;">HOURS</th>
                            <th style="width: 12%; text-align: right;">RATE</th>
                            <th style="width: 13%; text-align: right;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="service-desc">{{ $serviceType }}</div>
                                <div class="service-details">
                                    <strong>Duty Type:</strong> {{ $dutyType }}<br>
                                    <strong>Caregiver(s):</strong> {{ $caregivers }}<br>
                                    <strong>Schedule:</strong> {{ $hoursPerDay }} hours/day × {{ $durationDays }} days<br>
                                    <strong>Location:</strong> {{ $clientCity }}, NY
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: 600;">{{ $durationDays }} days</td>
                            <td style="text-align: center; font-weight: 600;">{{ $totalHours }}h</td>
                            <td style="text-align: right;">${{ number_format($hourlyRate, 2) }}/hr</td>
                            <td style="text-align: right; font-weight: bold;">${{ number_format($subtotal, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Totals Section -->
            <div class="totals-section">
                <table class="totals-table">
                    <tr class="subtotal">
                        <td><strong>Subtotal:</strong></td>
                        <td style="text-align: right;">${{ number_format($subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Sales Tax ({{ number_format($taxRate, 3) }}%):</td>
                        <td style="text-align: right;">${{ number_format($tax, 2) }}</td>
                    </tr>
                    <tr class="total">
                        <td><strong>TOTAL PAID:</strong></td>
                        <td style="text-align: right;"><strong>${{ number_format($total, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
            
            <!-- Payment Info Box -->
            <div class="payment-info-box">
                <h3>PAYMENT INFORMATION</h3>
                <p><strong>Payment Method:</strong> {{ $paymentMethod }}</p>
                <p><strong>Transaction ID:</strong> {{ $paymentIntentId }}</p>
                <p><strong>Payment Date:</strong> {{ $paymentDate }}</p>
                <p><strong>Payment Status:</strong> <span style="color: #28a745; font-weight: bold;">✓ Confirmed & Processed</span></p>
            </div>
            
            <!-- Thank You Note -->
            <div class="thank-you-note">
                <p>Thank you for choosing CAS Private Care LLC!</p>
                <p>This document serves as your official receipt for services rendered. Your trust in our professional caregiving services is greatly appreciated.</p>
                <p>For questions regarding this receipt or your booking, please contact us at billing@casprivatecare.com or call (646) 282-8282.</p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="pdf-footer">
            <p><strong>CAS PRIVATE CARE LLC - Professional Caregiving Services</strong></p>
            <p>This is a computer-generated receipt and serves as proof of payment.</p>
            <p>All services are provided by licensed and certified caregiving professionals.</p>
            <p style="margin-top: 15px;">© 2024-{{ date('Y') }} CAS Private Care LLC. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
