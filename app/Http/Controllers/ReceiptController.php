<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReceiptController extends Controller
{
    public function generate($bookingId)
    {
        $booking = Booking::with(['client', 'assignedCaregiver', 'assignments.caregiver'])->find($bookingId);
        
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        
        // Calculate pricing
        $hoursPerDay = $this->extractHours($booking->duty_type);
        $duration = $booking->duration_days ?? 15;
        $totalHours = $hoursPerDay * $duration;
        $hourlyRate = $booking->hourly_rate ?? 45;
        $subtotal = $totalHours * $hourlyRate;
        $tax = 0; // No tax for healthcare services
        $total = $subtotal + $tax;
        
        // Client info
        $clientName = $booking->client ? $booking->client->name : 'Client';
        $clientAddress = $booking->street_address ?? '456 Client Street';
        $clientCity = $booking->borough ?? 'New York';
        $clientEmail = $booking->client ? $booking->client->email : 'client@email.com';
        
        // Caregiver info - get from assignedCaregiver or first assignment
        $caregiverName = 'Assigned Caregiver';
        if ($booking->assignedCaregiver) {
            $caregiverName = $booking->assignedCaregiver->name;
        } elseif ($booking->assignments->count() > 0 && $booking->assignments->first()->caregiver) {
            $caregiverName = $booking->assignments->first()->caregiver->name;
        }
        
        // Receipt details
        $receiptNumber = 'RCP-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT);
        $serviceDate = $booking->service_date ? date('M d, Y', strtotime($booking->service_date)) : date('M d, Y');
        $issueDate = date('M d, Y');
        $completedDate = $booking->updated_at ? $booking->updated_at->format('M d, Y') : date('M d, Y');
        
        // Check for referral discount
        $hasDiscount = $hourlyRate < 45;
        $originalRate = 45;
        $savedAmount = $hasDiscount ? ($originalRate - $hourlyRate) * $totalHours : 0;
        
        // Generate HTML
        $html = $this->generateReceiptHtml([
            'receiptNumber' => $receiptNumber,
            'clientName' => $clientName,
            'clientAddress' => $clientAddress,
            'clientCity' => $clientCity,
            'clientEmail' => $clientEmail,
            'caregiverName' => $caregiverName,
            'serviceType' => $booking->service_type ?? 'Caregiver Service',
            'serviceDate' => $serviceDate,
            'issueDate' => $issueDate,
            'completedDate' => $completedDate,
            'hoursPerDay' => $hoursPerDay,
            'duration' => $duration,
            'totalHours' => $totalHours,
            'hourlyRate' => $hourlyRate,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'processingFee' => 0,
            'total' => $total,
            'hasDiscount' => $hasDiscount,
            'originalRate' => $originalRate,
            'savedAmount' => $savedAmount,
            'dutyType' => $booking->duty_type ?? '8 Hours per Day',
        ]);
        
        // Generate PDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = "Receipt_{$receiptNumber}.pdf";
        
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
    
    public function download($bookingId)
    {
        $booking = Booking::with(['client', 'assignedCaregiver', 'assignments.caregiver'])->find($bookingId);
        
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        
        // Same logic as generate but with attachment header
        $hoursPerDay = $this->extractHours($booking->duty_type);
        $duration = $booking->duration_days ?? 15;
        $totalHours = $hoursPerDay * $duration;
        $hourlyRate = $booking->hourly_rate ?? 45;
        $subtotal = $totalHours * $hourlyRate;
        $tax = 0;
        $total = $subtotal + $tax;
        
        $clientName = $booking->client ? $booking->client->name : 'Client';
        $clientAddress = $booking->street_address ?? '456 Client Street';
        $clientCity = $booking->borough ?? 'New York';
        $clientEmail = $booking->client ? $booking->client->email : 'client@email.com';
        
        // Caregiver info - get from assignedCaregiver or first assignment
        $caregiverName = 'Assigned Caregiver';
        if ($booking->assignedCaregiver) {
            $caregiverName = $booking->assignedCaregiver->name;
        } elseif ($booking->assignments->count() > 0 && $booking->assignments->first()->caregiver) {
            $caregiverName = $booking->assignments->first()->caregiver->name;
        }
        
        $receiptNumber = 'RCP-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT);
        $serviceDate = $booking->service_date ? date('M d, Y', strtotime($booking->service_date)) : date('M d, Y');
        $issueDate = date('M d, Y');
        $completedDate = $booking->updated_at ? $booking->updated_at->format('M d, Y') : date('M d, Y');
        
        $hasDiscount = $hourlyRate < 45;
        $originalRate = 45;
        $savedAmount = $hasDiscount ? ($originalRate - $hourlyRate) * $totalHours : 0;
        
        $html = $this->generateReceiptHtml([
            'receiptNumber' => $receiptNumber,
            'clientName' => $clientName,
            'clientAddress' => $clientAddress,
            'clientCity' => $clientCity,
            'clientEmail' => $clientEmail,
            'caregiverName' => $caregiverName,
            'serviceType' => $booking->service_type ?? 'Caregiver Service',
            'serviceDate' => $serviceDate,
            'issueDate' => $issueDate,
            'completedDate' => $completedDate,
            'hoursPerDay' => $hoursPerDay,
            'duration' => $duration,
            'totalHours' => $totalHours,
            'hourlyRate' => $hourlyRate,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'processingFee' => 0,
            'total' => $total,
            'hasDiscount' => $hasDiscount,
            'originalRate' => $originalRate,
            'savedAmount' => $savedAmount,
            'dutyType' => $booking->duty_type ?? '8 Hours per Day',
        ]);
        
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = "Receipt_{$receiptNumber}.pdf";
        
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    private function extractHours($dutyType)
    {
        if ($dutyType && is_string($dutyType)) {
            preg_match('/(\d+)\s*Hours?/i', $dutyType, $matches);
            return isset($matches[1]) ? (int)$matches[1] : 8;
        }
        return 8;
    }
    
    private function generateReceiptHtml($data)
    {
        // Get logo
        $logoPath = public_path('logo.png');
        $logoHtml = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoHtml = '<img src="data:image/png;base64,' . $logoData . '" alt="CAS Logo">';
        }
        
        $discountRow = '';
        $savingsRow = '';
        if ($data['hasDiscount']) {
            $discountRow = '
                <tr>
                    <td>
                        <div class="service-desc">Referral Discount</div>
                        <div class="service-details">Rate reduced from $' . number_format($data['originalRate'], 2) . '/hr to $' . number_format($data['hourlyRate'], 2) . '/hr</div>
                    </td>
                    <td class="text-center">-</td>
                    <td class="text-center">-$' . number_format($data['originalRate'] - $data['hourlyRate'], 2) . '/hr</td>
                    <td class="text-center font-bold">-$' . number_format($data['savedAmount'], 2) . '</td>
                </tr>';
            $savingsRow = '<tr><td>Referral Savings:</td><td style="text-align: right;">-$' . number_format($data['savedAmount'], 2) . '</td></tr>';
        }

        $processingFeeRow = '';
        if (!empty($data['processingFee']) && (float) $data['processingFee'] > 0) {
            $processingFeeRow = '<tr><td>Processing Fee:</td><td style="text-align: right;">$' . number_format((float) $data['processingFee'], 2) . '</td></tr>';
        }
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CAS Private Care - Receipt ' . $data['receiptNumber'] . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10pt; line-height: 1.4; color: #000; background: #fff; padding: 30px 40px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header-table { width: 100%; }
        .header-table td { vertical-align: top; }
        .logo-cell { width: 100px; }
        .logo-cell img { width: 90px; height: auto; }
        .company-name { font-size: 16pt; font-weight: bold; letter-spacing: 1px; }
        .company-tagline { font-size: 9pt; font-style: italic; color: #333; }
        .company-address { font-size: 8pt; color: #555; margin-top: 3px; }
        .date-cell { text-align: right; font-size: 9pt; }
        .doc-id { font-size: 7pt; color: #666; margin-top: 5px; }
        .report-title { text-align: center; margin: 20px 0; padding: 12px 0; border-top: 1px solid #000; border-bottom: 1px solid #000; }
        .report-title h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 3px; }
        .report-title .subtitle { font-size: 9pt; color: #333; }
        .status-badge { border: 2px solid #000; display: inline-block; padding: 4px 12px; font-size: 8pt; font-weight: bold; letter-spacing: 1px; margin-left: 15px; }
        .report-info { margin-bottom: 15px; font-size: 9pt; }
        .report-info table { width: 100%; }
        .report-info td { padding: 2px 0; }
        .report-info .label { font-weight: bold; width: 100px; }
        .summary-section { margin-bottom: 20px; padding: 12px; border: 1px solid #000; }
        .summary-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #ccc; }
        .summary-table { width: 100%; }
        .summary-table td { text-align: center; padding: 8px; width: 25%; }
        .stat-value { font-size: 16pt; font-weight: bold; }
        .stat-label { font-size: 7pt; text-transform: uppercase; color: #555; }
        .data-section { margin-bottom: 20px; }
        .section-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 2px solid #000; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        .data-table th { background-color: #e0e0e0; border: 1px solid #000; padding: 6px 4px; text-align: left; font-weight: bold; text-transform: uppercase; font-size: 7pt; }
        .data-table td { border: 1px solid #000; padding: 5px 4px; }
        .data-table tr:nth-child(even) { background-color: #f5f5f5; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .service-desc { font-weight: 600; font-size: 9pt; }
        .service-details { font-size: 7pt; color: #555; margin-top: 2px; }
        .totals-section { margin-top: 20px; }
        .totals-table { width: 250px; margin-left: auto; border-collapse: collapse; }
        .totals-table td { padding: 4px 8px; font-size: 9pt; border-bottom: 1px solid #ddd; }
        .totals-table .total td { border-top: 2px solid #000; border-bottom: none; font-weight: bold; font-size: 11pt; padding-top: 8px; }
        .signature-section { margin-top: 25px; }
        .signature-table { width: 100%; }
        .signature-table td { width: 45%; padding-top: 35px; }
        .signature-line { border-top: 1px solid #000; padding-top: 4px; font-size: 8pt; }
        .footer { margin-top: 25px; padding-top: 12px; border-top: 2px solid #000; font-size: 7pt; }
        .footer-table { width: 100%; }
        .footer-table td { vertical-align: top; }
        .footer-left { text-align: left; width: 33%; }
        .footer-center { text-align: center; width: 34%; }
        .footer-right { text-align: right; width: 33%; }
        .confidential { text-align: center; font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-top: 12px; padding: 4px; border: 1px solid #000; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">' . $logoHtml . '</td>
                <td>
                    <div class="company-name">CAS PRIVATE CARE LLC</div>
                    <div class="company-tagline">Comfort & Support Healthcare Services</div>
                    <div class="company-address">Licensed Healthcare Provider | New York</div>
                </td>
                <td class="date-cell">
                    <strong>Issue Date:</strong><br>
                    ' . $data['issueDate'] . '<br>
                    ' . date('g:i A') . '
                    <div class="doc-id">Doc ID: ' . $data['receiptNumber'] . '</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        <h1>Official Receipt <span class="status-badge">PAID</span></h1>
        <div class="subtitle">Service Payment Confirmation</div>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td class="label">Client:</td>
                <td>' . htmlspecialchars($data['clientName']) . '</td>
                <td class="label" style="padding-left: 20px;">Receipt #:</td>
                <td>' . htmlspecialchars($data['receiptNumber']) . '</td>
            </tr>
            <tr>
                <td class="label">Address:</td>
                <td>' . htmlspecialchars($data['clientAddress']) . ', ' . htmlspecialchars($data['clientCity']) . ', NY</td>
                <td class="label" style="padding-left: 20px;">Service Date:</td>
                <td>' . htmlspecialchars($data['serviceDate']) . '</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td>' . htmlspecialchars($data['clientEmail']) . '</td>
                <td class="label" style="padding-left: 20px;">Completed:</td>
                <td>' . htmlspecialchars($data['completedDate']) . '</td>
            </tr>
        </table>
    </div>

    <div class="summary-section">
        <div class="summary-title">Payment Summary</div>
        <table class="summary-table">
            <tr>
                <td>
                    <div class="stat-value">' . $data['duration'] . '</div>
                    <div class="stat-label">Service Days</div>
                </td>
                <td>
                    <div class="stat-value">' . number_format($data['totalHours']) . '</div>
                    <div class="stat-label">Total Hours</div>
                </td>
                <td>
                    <div class="stat-value">$' . number_format($data['hourlyRate'], 2) . '</div>
                    <div class="stat-label">Rate per Hour</div>
                </td>
                <td>
                    <div class="stat-value">$' . number_format($data['total'], 2) . '</div>
                    <div class="stat-label">Total Paid</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="data-section">
        <div class="section-title">Service Details</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Description</th>
                    <th style="width: 15%;" class="text-center">Hours</th>
                    <th style="width: 20%;" class="text-center">Rate</th>
                    <th style="width: 25%;" class="text-center">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="service-desc">' . htmlspecialchars($data['serviceType']) . '</div>
                        <div class="service-details">Caregiver: ' . htmlspecialchars($data['caregiverName']) . '</div>
                        <div class="service-details">' . htmlspecialchars($data['dutyType']) . ' × ' . $data['duration'] . ' days</div>
                    </td>
                    <td class="text-center">' . number_format($data['totalHours']) . 'h</td>
                    <td class="text-center">$' . number_format($data['hourlyRate'], 2) . '/hr</td>
                    <td class="text-center font-bold">$' . number_format($data['subtotal'], 2) . '</td>
                </tr>
                ' . $discountRow . '
            </tbody>
        </table>
    </div>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">$' . number_format($data['subtotal'], 2) . '</td>
            </tr>
            <tr>
                <td>Tax:</td>
                <td class="text-right">$' . number_format($data['tax'], 2) . '</td>
            </tr>
            ' . $savingsRow . '
            ' . $processingFeeRow . '
            <tr class="total">
                <td><strong>TOTAL PAID:</strong></td>
                <td class="text-right"><strong>$' . number_format($data['total'], 2) . '</strong></td>
            </tr>
        </table>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td><div class="signature-line">Client Signature</div></td>
                <td style="width: 10%;"></td>
                <td><div class="signature-line">Authorized By</div></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    <strong>CAS Private Care LLC</strong><br>
                    &copy; ' . date('Y') . ' All Rights Reserved
                </td>
                <td class="footer-center">
                    This is an official receipt<br>
                    Generated: ' . date('M j, Y g:i A') . '
                </td>
                <td class="footer-right">
                    Page 1 of 1<br>
                    Ref: ' . $data['receiptNumber'] . '
                </td>
            </tr>
        </table>
        <div class="confidential">Thank You For Choosing CAS Private Care</div>
    </div>
</body>
</html>';
    }
    
    // New method for payment receipts using the booking-receipt template
    public function generatePaymentReceipt($bookingId)
    {
        // If a payment exists, we prefer using it for accurate totals (includes processing fee)
        $payment = \App\Models\Payment::where('booking_id', $bookingId)
            ->where('status', 'completed')
            ->latest('paid_at')
            ->first();

        // Load booking with all relationships
        $booking = Booking::with(['client', 'assignments.caregiver.user'])
            ->findOrFail($bookingId);
        
        // Verify user has access to this receipt
        if (auth()->check()) {
            if (auth()->user()->user_type === 'client' && $booking->client_id !== auth()->id()) {
                abort(403, 'Unauthorized access to receipt');
            }
        }
        
        // Check if booking is paid
        if ($booking->payment_status !== 'paid') {
            return redirect()->back()->with('error', 'Receipt is only available for paid bookings');
        }
        
        // Calculate booking details
        $durationDays = $booking->duration_days ?? 15;
        $dutyType = $booking->duty_type ?? '8 Hours';
        $hourlyRate = $booking->hourly_rate ?? 40;
        
        // Extract hours per day from duty type
        preg_match('/(\d+)/', $dutyType, $matches);
        $hoursPerDay = isset($matches[1]) ? (int)$matches[1] : 8;
        
        $totalHours = $durationDays * $hoursPerDay;
    $subtotal = $totalHours * $hourlyRate;
        $taxRate = 0; // Healthcare services are tax-exempt in NY
        $tax = 0;
    $processingFee = $payment && $payment->processing_fee ? (float) $payment->processing_fee : 0.0;
    $total = $subtotal + $tax + $processingFee;
        
        // Get assigned caregivers
        $caregivers = $booking->assignments->map(function($assignment) {
            return $assignment->caregiver->user->name ?? 'Not Assigned';
        })->join(', ');
        
        if (empty($caregivers)) {
            $caregivers = 'Not Assigned Yet';
        }
        
        // Check for referral discount
        $originalRate = $booking->original_hourly_rate ?? $hourlyRate;
        $hasDiscount = $originalRate > $hourlyRate;
        $savedAmount = $hasDiscount ? ($originalRate - $hourlyRate) * $totalHours : 0;
        
        // Generate receipt HTML using the same template as time tracking
        $html = $this->generateReceiptHtml([
            'receiptNumber' => 'RCP-' . str_pad($bookingId, 6, '0', STR_PAD_LEFT),
            'issueDate' => date('M d, Y'),
            'clientName' => $booking->client->name ?? 'N/A',
            'clientEmail' => $booking->client->email ?? 'N/A',
            'clientAddress' => $booking->street_address ?? 'N/A',
            'clientCity' => $booking->city ?? 'New York',
            'serviceType' => $booking->service_type ?? 'Caregiving Service',
            'serviceDate' => $booking->service_date ? \Carbon\Carbon::parse($booking->service_date)->format('M d, Y') : 'N/A',
            'completedDate' => $booking->updated_at->format('M d, Y'),
            'duration' => $durationDays,
            'dutyType' => $dutyType,
            'totalHours' => $totalHours,
            'hourlyRate' => $hourlyRate,
            'originalRate' => $originalRate,
            'hasDiscount' => $hasDiscount,
            'savedAmount' => $savedAmount,
            'caregiverName' => $caregivers,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'processingFee' => $processingFee,
            'total' => $total,
        ]);
        
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        // Initialize Dompdf
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Output PDF
        $filename = 'Receipt_' . $booking->id . '_' . date('Y-m-d') . '.pdf';
        return $dompdf->stream($filename, ['Attachment' => false]); // false = display in browser
    }
    
    public function downloadPaymentReceipt($bookingId)
    {
        // Same as generatePaymentReceipt but force download
        $booking = Booking::with(['client', 'assignments.caregiver.user'])
            ->findOrFail($bookingId);
        
        if (auth()->check()) {
            if (auth()->user()->user_type === 'client' && $booking->client_id !== auth()->id()) {
                abort(403, 'Unauthorized access to receipt');
            }
        }
        
        if ($booking->payment_status !== 'paid') {
            return redirect()->back()->with('error', 'Receipt is only available for paid bookings');
        }
        
        $durationDays = $booking->duration_days ?? 15;
        $dutyType = $booking->duty_type ?? '8 Hours';
        $hourlyRate = $booking->hourly_rate ?? 40;
        
        preg_match('/(\d+)/', $dutyType, $matches);
        $hoursPerDay = isset($matches[1]) ? (int)$matches[1] : 8;
        
        $totalHours = $durationDays * $hoursPerDay;
        $subtotal = $totalHours * $hourlyRate;
        $taxRate = 0; // Healthcare services are tax-exempt in NY
        $tax = 0;
        $total = $subtotal + $tax;
        
        $caregivers = $booking->assignments->map(function($assignment) {
            return $assignment->caregiver->user->name ?? 'Not Assigned';
        })->join(', ');
        
        if (empty($caregivers)) {
            $caregivers = 'Not Assigned Yet';
        }
        
        // Check for referral discount
        $originalRate = $booking->original_hourly_rate ?? $hourlyRate;
        $hasDiscount = $originalRate > $hourlyRate;
        $savedAmount = $hasDiscount ? ($originalRate - $hourlyRate) * $totalHours : 0;
        
        // Generate receipt HTML using the same template as time tracking
        $html = $this->generateReceiptHtml([
            'receiptNumber' => 'RCP-' . str_pad($bookingId, 6, '0', STR_PAD_LEFT),
            'issueDate' => date('M d, Y'),
            'clientName' => $booking->client->name ?? 'N/A',
            'clientEmail' => $booking->client->email ?? 'N/A',
            'clientAddress' => $booking->street_address ?? 'N/A',
            'clientCity' => $booking->city ?? 'New York',
            'serviceType' => $booking->service_type ?? 'Caregiving Service',
            'serviceDate' => $booking->service_date ? \Carbon\Carbon::parse($booking->service_date)->format('M d, Y') : 'N/A',
            'completedDate' => $booking->updated_at->format('M d, Y'),
            'duration' => $durationDays,
            'dutyType' => $dutyType,
            'totalHours' => $totalHours,
            'hourlyRate' => $hourlyRate,
            'originalRate' => $originalRate,
            'hasDiscount' => $hasDiscount,
            'savedAmount' => $savedAmount,
            'caregiverName' => $caregivers,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);
        
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = 'Receipt_' . $booking->id . '_' . date('Y-m-d') . '.pdf';
        return $dompdf->stream($filename, ['Attachment' => true]); // Force download
    }
}
