<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\TimeTracking;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    /**
     * Generate Financial Report PDF
     */
    public function generateFinancialReport(Request $request)
    {
        $period = $request->input('period', 'all'); // all, today, week, month
        
        // Calculate date range
        $startDate = null;
        $endDate = Carbon::now();
        
        switch ($period) {
            case 'today':
                $startDate = Carbon::today();
                break;
            case 'week':
                $startDate = Carbon::now()->subWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->subMonth();
                break;
            default:
                $startDate = Carbon::create(2024, 1, 1); // Platform launch date
        }
        
        // Get financial data
        $data = $this->getFinancialData($startDate, $endDate);
        
        // Generate HTML
        $html = $this->generateFinancialReportHtml($data, $startDate, $endDate);
        
        // Generate PDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = "Financial_Report_" . now()->format('Y-m-d') . ".pdf";
        
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
    
    private function getFinancialData($startDate, $endDate)
    {
        // Get payments within date range
        $payments = Payment::whereBetween('created_at', [$startDate, $endDate])->get();
        
        $totalRevenue = $payments->where('status', 'completed')->sum('amount');
        $pendingPayments = $payments->where('status', 'pending')->sum('amount');
        $completedPayments = $payments->where('status', 'completed')->count();
        
        // Get bookings data
        $bookings = Booking::whereBetween('created_at', [$startDate, $endDate])->get();
        $totalBookings = $bookings->count();
        $activeBookings = $bookings->whereIn('status', ['approved', 'confirmed', 'in_progress'])->count();
        
        // Get time tracking data to calculate caregiver earnings
        $timeTrackings = TimeTracking::whereBetween('clock_in_time', [$startDate, $endDate])
            ->whereNotNull('clock_out_time')
            ->get();
        
        $caregiverEarnings = 0;
        foreach ($timeTrackings as $tracking) {
            $hours = $tracking->clock_in_time->diffInMinutes($tracking->clock_out_time) / 60;
            $caregiverEarnings += $hours * 28; // $28/hour for caregivers
        }
        
        // Calculate processing fees (2.5% of completed payments)
        $processingFees = $totalRevenue * 0.025;
        
        // Calculate net revenue
        $netRevenue = $totalRevenue - $caregiverEarnings - $processingFees;
        
        // Get recent transactions
        $recentTransactions = Payment::with(['booking.client'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function($payment) {
                return [
                    'date' => $payment->created_at->format('M d, Y'),
                    'time' => $payment->created_at->format('g:i A'),
                    'client' => $payment->booking->client->name ?? 'Unknown',
                    'amount' => $payment->amount,
                    'status' => ucfirst($payment->status),
                    'payment_method' => ucfirst($payment->payment_method ?? 'card')
                ];
            });
        
        // Get user statistics
        $totalUsers = User::count();
        $totalClients = User::where('role', 'client')->count();
        $totalCaregivers = Caregiver::count();
        
        return [
            'totalRevenue' => $totalRevenue,
            'pendingPayments' => $pendingPayments,
            'caregiverEarnings' => $caregiverEarnings,
            'processingFees' => $processingFees,
            'netRevenue' => $netRevenue,
            'totalBookings' => $totalBookings,
            'activeBookings' => $activeBookings,
            'completedPayments' => $completedPayments,
            'recentTransactions' => $recentTransactions,
            'totalUsers' => $totalUsers,
            'totalClients' => $totalClients,
            'totalCaregivers' => $totalCaregivers,
        ];
    }
    
    private function generateFinancialReportHtml($data, $startDate, $endDate)
    {
        // Get logo
        $logoPath = public_path('logo.png');
        $logoHtml = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoHtml = '<img src="data:image/png;base64,' . $logoData . '" alt="CAS Logo">';
        }
        
        $reportNumber = 'RPT-' . now()->format('Ymd-His');
        $reportDate = now()->format('M d, Y');
        $reportTime = now()->format('g:i A');
        $periodText = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
        
        // Build transactions table rows
        $transactionsRows = '';
        foreach ($data['recentTransactions'] as $transaction) {
            $transactionsRows .= '
                <tr>
                    <td>' . $transaction['date'] . '</td>
                    <td>' . $transaction['time'] . '</td>
                    <td>' . $transaction['client'] . '</td>
                    <td class="text-right">$' . number_format($transaction['amount'], 2) . '</td>
                    <td class="text-center">' . $transaction['status'] . '</td>
                    <td>' . $transaction['payment_method'] . '</td>
                </tr>';
        }
        
        if (empty($transactionsRows)) {
            $transactionsRows = '<tr><td colspan="6" class="text-center">No transactions in this period</td></tr>';
        }
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CAS Private Care - Financial Report ' . $reportNumber . '</title>
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
        .report-info .label { font-weight: bold; width: 120px; }
        .summary-section { margin-bottom: 20px; padding: 12px; border: 1px solid #000; background: #f9f9f9; }
        .summary-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #ccc; }
        .summary-table { width: 100%; }
        .summary-table td { padding: 8px; width: 25%; text-align: center; border-right: 1px solid #ddd; }
        .summary-table td:last-child { border-right: none; }
        .stat-value { font-size: 16pt; font-weight: bold; }
        .stat-label { font-size: 7pt; text-transform: uppercase; color: #555; margin-top: 2px; }
        .data-section { margin-bottom: 20px; }
        .section-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 2px solid #000; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        .data-table th { background-color: #e0e0e0; border: 1px solid #000; padding: 6px 4px; text-align: left; font-weight: bold; text-transform: uppercase; font-size: 7pt; }
        .data-table td { border: 1px solid #000; padding: 5px 4px; }
        .data-table tr:nth-child(even) { background-color: #f5f5f5; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .totals-section { margin-top: 20px; }
        .totals-table { width: 300px; margin-left: auto; border-collapse: collapse; }
        .totals-table td { padding: 6px 12px; font-size: 9pt; border-bottom: 1px solid #ddd; }
        .totals-table .total td { border-top: 2px solid #000; border-bottom: 3px double #000; font-weight: bold; font-size: 11pt; padding-top: 8px; background: #f0f0f0; }
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
                    <strong>Report Date:</strong><br>
                    ' . $reportDate . '<br>
                    ' . $reportTime . '
                    <div class="doc-id">Doc ID: ' . $reportNumber . '</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        <h1>Financial Report <span class="status-badge">ADMIN</span></h1>
        <div class="subtitle">Comprehensive Financial Overview</div>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td class="label">Report Period:</td>
                <td>' . $periodText . '</td>
                <td class="label">Total Transactions:</td>
                <td>' . $data['completedPayments'] . '</td>
            </tr>
            <tr>
                <td class="label">Total Users:</td>
                <td>' . $data['totalUsers'] . '</td>
                <td class="label">Total Bookings:</td>
                <td>' . $data['totalBookings'] . '</td>
            </tr>
        </table>
    </div>

    <div class="summary-section">
        <div class="summary-title">Financial Summary</div>
        <table class="summary-table">
            <tr>
                <td>
                    <div class="stat-value">$' . number_format($data['totalRevenue'], 0) . '</div>
                    <div class="stat-label">Total Revenue</div>
                </td>
                <td>
                    <div class="stat-value">$' . number_format($data['pendingPayments'], 0) . '</div>
                    <div class="stat-label">Pending Payments</div>
                </td>
                <td>
                    <div class="stat-value">$' . number_format($data['caregiverEarnings'], 0) . '</div>
                    <div class="stat-label">Caregiver Earnings</div>
                </td>
                <td>
                    <div class="stat-value">$' . number_format($data['processingFees'], 0) . '</div>
                    <div class="stat-label">Processing Fees</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-section">
        <div class="summary-title">Platform Statistics</div>
        <table class="summary-table">
            <tr>
                <td>
                    <div class="stat-value">' . $data['totalClients'] . '</div>
                    <div class="stat-label">Total Clients</div>
                </td>
                <td>
                    <div class="stat-value">' . $data['totalCaregivers'] . '</div>
                    <div class="stat-label">Total Caregivers</div>
                </td>
                <td>
                    <div class="stat-value">' . $data['activeBookings'] . '</div>
                    <div class="stat-label">Active Bookings</div>
                </td>
                <td>
                    <div class="stat-value">' . $data['completedPayments'] . '</div>
                    <div class="stat-label">Completed Payments</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="data-section">
        <div class="section-title">Recent Transactions</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Client</th>
                    <th class="text-right">Amount</th>
                    <th class="text-center">Status</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                ' . $transactionsRows . '
            </tbody>
        </table>
    </div>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td>Gross Revenue:</td>
                <td style="text-align: right;">$' . number_format($data['totalRevenue'], 2) . '</td>
            </tr>
            <tr>
                <td>Caregiver Payouts:</td>
                <td style="text-align: right;">-$' . number_format($data['caregiverEarnings'], 2) . '</td>
            </tr>
            <tr>
                <td>Processing Fees (2.5%):</td>
                <td style="text-align: right;">-$' . number_format($data['processingFees'], 2) . '</td>
            </tr>
            <tr class="total">
                <td>Net Revenue:</td>
                <td style="text-align: right;">$' . number_format($data['netRevenue'], 2) . '</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    <strong>CAS Private Care LLC</strong><br>
                    Licensed Healthcare Provider<br>
                    New York, NY
                </td>
                <td class="footer-center">
                    <strong>Contact</strong><br>
                    support@casprivatecare.com<br>
                    (646) 282-8282
                </td>
                <td class="footer-right">
                    <strong>Report Generated</strong><br>
                    ' . $reportDate . '<br>
                    ' . $reportTime . '
                </td>
            </tr>
        </table>
    </div>

    <div class="confidential">
        Confidential Financial Report - For Internal Use Only
    </div>
</body>
</html>';
    }
}
