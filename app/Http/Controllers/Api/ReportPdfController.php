<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Controller for generating PDF reports via API.
 * Extracted from inline route closures as part of the codebase refactoring.
 *
 * @see AUDIT_COMPLIANCE.md Task 2.2
 */
class ReportPdfController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get the logo HTML for PDF reports.
     */
    private function getLogoHtml(): string
    {
        $logoPath = public_path('logo.png');
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            return '<img src="data:image/png;base64,' . $logoData . '" alt="CAS Logo">';
        }
        return '';
    }

    /**
     * Get the base CSS styles for PDF reports.
     */
    private function getBaseStyles(): string
    {
        return '
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
        .font-bold { font-weight: bold; }
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
        ';
    }

    /**
     * Generate the PDF header HTML.
     */
    private function getHeaderHtml(string $documentId): string
    {
        $logoHtml = $this->getLogoHtml();
        
        return '
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
                        ' . date('F j, Y') . '<br>
                        ' . date('g:i A') . '
                        <div class="doc-id">Doc ID: ' . $documentId . '</div>
                    </td>
                </tr>
            </table>
        </div>';
    }

    /**
     * Generate the PDF footer HTML.
     */
    private function getFooterHtml(string $refPrefix): string
    {
        return '
        <div class="footer">
            <table class="footer-table">
                <tr>
                    <td class="footer-left">
                        <strong>CAS Private Care LLC</strong><br>
                        &copy; ' . date('Y') . ' All Rights Reserved
                    </td>
                    <td class="footer-center">
                        This is an official document<br>
                        Generated: ' . date('M j, Y g:i A') . '
                    </td>
                    <td class="footer-right">
                        Page 1 of 1<br>
                        Ref: ' . $refPrefix . '-' . date('Ymd') . '
                    </td>
                </tr>
            </table>
            <div class="confidential">Confidential - For Internal Use Only</div>
        </div>';
    }

    /**
     * Render HTML to PDF and return response.
     */
    private function renderPdf(string $html, string $filename): mixed
    {
        if (class_exists('Dompdf\Dompdf')) {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Helvetica');
            
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } else {
            return response($html, 200, [
                'Content-Type' => 'text/html'
            ]);
        }
    }

    /**
     * Generate Time Tracking PDF Report.
     * 
     * @param Request $request
     * @return mixed
     */
    public function timeTrackingPdf(Request $request)
    {
        try {
            $data = $request->all();
            
            $dateFilter = $data['dateFilter'] ?? 'This Week';
            $statusFilter = $data['statusFilter'] ?? 'All';
            $totalSessions = $data['totalSessions'] ?? '0';
            $totalHours = $data['totalHours'] ?? '0';
            $activeCaregivers = $data['activeCaregivers'] ?? '0';
            $avgHoursPerDay = $data['avgHoursPerDay'] ?? '0';
            $timeHistory = $data['timeHistory'] ?? [];
            
            $docId = 'TTR-' . date('Ymd-His');
            
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CAS Private Care - Time Tracking Report</title>
    <style>' . $this->getBaseStyles() . '</style>
</head>
<body>';

            $html .= $this->getHeaderHtml($docId);
            
            $html .= '
    <div class="report-title">
        <h1>Time Tracking Report</h1>
        <div class="subtitle">Official Employee Work Hours Documentation</div>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td class="label">Report Period:</td>
                <td>' . htmlspecialchars($dateFilter) . '</td>
                <td class="label" style="padding-left: 20px;">Status Filter:</td>
                <td>' . htmlspecialchars($statusFilter) . '</td>
            </tr>
            <tr>
                <td class="label">Generated By:</td>
                <td>System Administrator</td>
                <td class="label" style="padding-left: 20px;">Report Type:</td>
                <td>Time Tracking Summary</td>
            </tr>
        </table>
    </div>

    <div class="summary-section">
        <div class="summary-title">Executive Summary</div>
        <table class="summary-table">
            <tr>
                <td>
                    <div class="stat-value">' . htmlspecialchars($totalSessions) . '</div>
                    <div class="stat-label">Total Sessions</div>
                </td>
                <td>
                    <div class="stat-value">' . htmlspecialchars($totalHours) . '</div>
                    <div class="stat-label">Total Hours</div>
                </td>
                <td>
                    <div class="stat-value">' . htmlspecialchars($activeCaregivers) . '</div>
                    <div class="stat-label">Active Caregivers</div>
                </td>
                <td>
                    <div class="stat-value">' . htmlspecialchars($avgHoursPerDay) . '</div>
                    <div class="stat-label">Avg Hours/Day</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="data-section">
        <div class="section-title">Detailed Time Records</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 20%;">Employee</th>
                    <th style="width: 20%;">Client</th>
                    <th style="width: 12%;" class="text-center">Clock In</th>
                    <th style="width: 12%;" class="text-center">Clock Out</th>
                    <th style="width: 10%;" class="text-center">Hours</th>
                    <th style="width: 14%;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>';

            if (empty($timeHistory)) {
                $html .= '<tr><td colspan="7" class="text-center" style="padding: 15px;">No time tracking records found for the selected period.</td></tr>';
            } else {
                foreach ($timeHistory as $row) {
                    $date = is_array($row) ? ($row['date'] ?? '') : ($row->date ?? '');
                    $caregiver = is_array($row) ? ($row['caregiver'] ?? '') : ($row->caregiver ?? '');
                    $client = is_array($row) ? ($row['client'] ?? '') : ($row->client ?? '');
                    $clockIn = is_array($row) ? ($row['clockIn'] ?? '') : ($row->clockIn ?? '');
                    $clockOut = is_array($row) ? ($row['clockOut'] ?? 'N/A') : ($row->clockOut ?? 'N/A');
                    $hoursWorked = is_array($row) ? ($row['hoursWorked'] ?? 0) : ($row->hoursWorked ?? 0);
                    $status = is_array($row) ? ($row['status'] ?? '') : ($row->status ?? '');
                    
                    $totalHrs = floor($hoursWorked);
                    $minutes = round(($hoursWorked - $totalHrs) * 60);
                    $formattedHours = $totalHrs . 'h ' . $minutes . 'm';
                    
                    $html .= '<tr>
                        <td>' . htmlspecialchars($date) . '</td>
                        <td>' . htmlspecialchars($caregiver) . '</td>
                        <td>' . htmlspecialchars($client) . '</td>
                        <td class="text-center">' . htmlspecialchars($clockIn) . '</td>
                        <td class="text-center">' . htmlspecialchars($clockOut) . '</td>
                        <td class="text-center font-bold">' . $formattedHours . '</td>
                        <td class="text-center">' . htmlspecialchars(strtoupper($status)) . '</td>
                    </tr>';
                }
            }

            $html .= '</tbody>
        </table>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td><div class="signature-line">Prepared By</div></td>
                <td style="width: 10%;"></td>
                <td><div class="signature-line">Approved By</div></td>
            </tr>
        </table>
    </div>';

            $html .= $this->getFooterHtml('TTR');
            $html .= '</body></html>';

            return $this->renderPdf($html, 'CAS-TimeTracking-Report-' . date('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Failed to generate time tracking PDF: ' . $e->getMessage());
            return $this->errorResponse('Failed to generate PDF: ' . $e->getMessage(), 500);
        }
    }

    // TODO: Add paymentPdf and clientAnalyticsPdf methods
    // These are large methods that should be migrated when time permits
    // See api.php lines 524-750 and 754-1010 for the original implementations
}
