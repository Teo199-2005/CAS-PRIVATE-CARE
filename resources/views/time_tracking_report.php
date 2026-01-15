<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CAS Private Care - Time Tracking Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            padding: 40px 50px;
        }
        
        /* Header */
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .logo-section {
            display: table-cell;
            width: 80px;
            vertical-align: top;
        }
        
        .logo-section img {
            width: 70px;
            height: auto;
        }
        
        .company-section {
            display: table-cell;
            vertical-align: top;
            padding-left: 15px;
        }
        
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        
        .company-tagline {
            font-size: 10pt;
            font-style: italic;
            color: #333;
        }
        
        .company-address {
            font-size: 9pt;
            color: #555;
            margin-top: 3px;
        }
        
        .date-section {
            display: table-cell;
            text-align: right;
            vertical-align: top;
            font-size: 9pt;
        }
        
        .doc-id {
            font-size: 8pt;
            color: #666;
            margin-top: 5px;
        }
        
        /* Title */
        .report-title {
            text-align: center;
            margin: 25px 0;
            padding: 15px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        
        .report-title h1 {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        
        .report-title .subtitle {
            font-size: 10pt;
            color: #333;
        }
        
        /* Report Info */
        .report-info {
            margin-bottom: 20px;
            font-size: 10pt;
        }
        
        .report-info table {
            width: 100%;
        }
        
        .report-info td {
            padding: 3px 0;
        }
        
        .report-info .label {
            font-weight: bold;
            width: 120px;
        }
        
        /* Summary Section */
        .summary-section {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #000;
        }
        
        .summary-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }
        
        .summary-stats {
            display: table;
            width: 100%;
        }
        
        .summary-stat {
            display: table-cell;
            text-align: center;
            padding: 10px;
            width: 25%;
        }
        
        .stat-value {
            font-size: 18pt;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 8pt;
            text-transform: uppercase;
            color: #555;
            margin-top: 3px;
        }
        
        /* Data Table */
        .data-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #000;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        
        .data-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }
        
        .data-table td {
            border: 1px solid #000;
            padding: 6px 5px;
        }
        
        .data-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #000;
            font-size: 8pt;
        }
        
        .footer-content {
            display: table;
            width: 100%;
        }
        
        .footer-left {
            display: table-cell;
            width: 33%;
            text-align: left;
        }
        
        .footer-center {
            display: table-cell;
            width: 34%;
            text-align: center;
        }
        
        .footer-right {
            display: table-cell;
            width: 33%;
            text-align: right;
        }
        
        .confidential {
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 15px;
            padding: 5px;
            border: 1px solid #000;
        }
        
        .signature-section {
            margin-top: 30px;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            width: 45%;
            padding-top: 40px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 9pt;
        }
        
        @media print {
            body {
                padding: 20px 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                <?php
                $logoPath = public_path('logo.png');
                if (file_exists($logoPath)) {
                    $logoData = base64_encode(file_get_contents($logoPath));
                    echo '<img src="data:image/png;base64,' . $logoData . '" alt="CAS Logo">';
                }
                ?>
            </div>
            <div class="company-section">
                <div class="company-name">CAS PRIVATE CARE LLC</div>
                <div class="company-tagline">Comfort & Support Healthcare Services</div>
                <div class="company-address">Licensed Healthcare Provider | New York</div>
            </div>
            <div class="date-section">
                <strong>Report Date:</strong><br>
                <?= date('F j, Y') ?><br>
                <?= date('g:i A') ?>
                <div class="doc-id">Doc ID: TTR-<?= date('Ymd-His') ?></div>
            </div>
        </div>
    </div>

    <!-- Report Title -->
    <div class="report-title">
        <h1>Time Tracking Report</h1>
        <div class="subtitle">Official Employee Work Hours Documentation</div>
    </div>

    <!-- Report Info -->
    <div class="report-info">
        <table>
            <tr>
                <td class="label">Report Period:</td>
                <td><?= $dateFilter ?? 'This Week' ?></td>
                <td class="label" style="padding-left: 30px;">Status Filter:</td>
                <td><?= $statusFilter ?? 'All' ?></td>
            </tr>
            <tr>
                <td class="label">Generated By:</td>
                <td>System Administrator</td>
                <td class="label" style="padding-left: 30px;">Report Type:</td>
                <td>Time Tracking Summary</td>
            </tr>
        </table>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-title">Executive Summary</div>
        <div class="summary-stats">
            <div class="summary-stat">
                <div class="stat-value"><?= $totalSessions ?? '0' ?></div>
                <div class="stat-label">Total Sessions</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value"><?= $totalHours ?? '0' ?>h</div>
                <div class="stat-label">Total Hours</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value"><?= $activeCaregivers ?? '0' ?></div>
                <div class="stat-label">Active Caregivers</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value"><?= $avgHoursPerDay ?? '0' ?>h</div>
                <div class="stat-label">Avg Hours/Day</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
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
            <tbody>
                <?php
                $reportData = isset($timeHistory) && !empty($timeHistory) ? $timeHistory : [];
                
                function formatHoursClean($hours) {
                    if (!$hours || $hours == 0) return '0h 0m';
                    $totalHours = floor($hours);
                    $minutes = round(($hours - $totalHours) * 60);
                    return $totalHours . 'h ' . $minutes . 'm';
                }
                
                if (empty($reportData)): ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">No time tracking records found for the selected period.</td>
                </tr>
                <?php else:
                    foreach ($reportData as $row): 
                        $date = is_array($row) ? $row['date'] : $row->date;
                        $caregiver = is_array($row) ? $row['caregiver'] : $row->caregiver;
                        $client = is_array($row) ? $row['client'] : $row->client;
                        $clockIn = is_array($row) ? $row['clockIn'] : $row->clockIn;
                        $clockOut = is_array($row) ? ($row['clockOut'] ?? 'N/A') : ($row->clockOut ?? 'N/A');
                        $hoursWorked = is_array($row) ? $row['hoursWorked'] : $row->hoursWorked;
                        $status = is_array($row) ? $row['status'] : $row->status;
                ?>
                <tr>
                    <td><?= htmlspecialchars($date) ?></td>
                    <td><?= htmlspecialchars($caregiver) ?></td>
                    <td><?= htmlspecialchars($client) ?></td>
                    <td class="text-center"><?= htmlspecialchars($clockIn) ?></td>
                    <td class="text-center"><?= htmlspecialchars($clockOut) ?></td>
                    <td class="text-center font-bold"><?= formatHoursClean($hoursWorked) ?></td>
                    <td class="text-center"><?= htmlspecialchars(strtoupper($status)) ?></td>
                </tr>
                <?php 
                    endforeach;
                endif;
                ?>
            </tbody>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Prepared By</div>
        </div>
        <div style="display: table-cell; width: 10%;"></div>
        <div class="signature-box">
            <div class="signature-line">Approved By</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <strong>CAS Private Care LLC</strong><br>
                &copy; <?= date('Y') ?> All Rights Reserved
            </div>
            <div class="footer-center">
                This is an official document<br>
                Generated: <?= date('M j, Y g:i A') ?>
            </div>
            <div class="footer-right">
                Page 1 of 1<br>
                Ref: TTR-<?= date('Ymd') ?>
            </div>
        </div>
        <div class="confidential">
            Confidential - For Internal Use Only
        </div>
    </div>
</body>
</html>
