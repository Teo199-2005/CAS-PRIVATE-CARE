<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\NYLocationService;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\BookingAssignment;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

// Profile avatar upload
Route::post('/user/{id}/avatar', function ($id, Request $request) {
    try {
        $user = User::findOrFail($id);
        
        if (!$request->hasFile('avatar')) {
            return response()->json(['error' => 'No avatar file provided'], 400);
        }
        
        $file = $request->file('avatar');
        
        // Validate file
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        // Store new avatar
        $path = $file->store('avatars', 'public');
        
        // Update user
        $user->update(['avatar' => $path]);
        
        return response()->json([
            'success' => true,
            'avatar' => '/storage/' . $path,
            'message' => 'Avatar uploaded successfully'
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['error' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to upload avatar: ' . $e->getMessage()], 500);
    }
});

// Application Status Endpoints (for checking approval status)
Route::get('/caregiver/application-status', function (Request $request) {
    try {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Return user's approval status
        $status = $user->status ?? 'pending';
        // Normalize: 'Active' or 'approved' = approved, otherwise pending
        $approvalStatus = (strtolower($status) === 'active' || strtolower($status) === 'approved') ? 'approved' : 'pending';
        
        return response()->json([
            'success' => true,
            'status' => $approvalStatus,
            'application' => [
                'status' => $approvalStatus
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to check application status: ' . $e->getMessage()], 500);
    }
});

Route::get('/marketing/application-status', function (Request $request) {
    try {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Return user's approval status
        $status = $user->status ?? 'pending';
        // Normalize: 'Active' or 'approved' = approved, otherwise pending
        $approvalStatus = (strtolower($status) === 'active' || strtolower($status) === 'approved') ? 'approved' : 'pending';
        
        return response()->json([
            'success' => true,
            'status' => $approvalStatus
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to check application status: ' . $e->getMessage()], 500);
    }
});

Route::get('/training/application-status', function (Request $request) {
    try {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Return user's approval status
        $status = $user->status ?? 'pending';
        // Normalize: 'Active' or 'approved' = approved, otherwise pending
        $approvalStatus = (strtolower($status) === 'active' || strtolower($status) === 'approved') ? 'approved' : 'pending';
        
        return response()->json([
            'success' => true,
            'status' => $approvalStatus
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to check application status: ' . $e->getMessage()], 500);
    }
});

// Update user profile
Route::put('/user/{id}/profile', function ($id, Request $request) {
    try {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'borough' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:50',
            'zip_code' => 'sometimes|string|max:20',
            'date_of_birth' => 'sometimes|date',
        ]);
        
        $user->update($validated);
        
        return response()->json([
            'success' => true,
            'user' => $user->fresh(),
            'message' => 'Profile updated successfully'
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['error' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to update profile: ' . $e->getMessage()], 500);
    }
});

// Get user profile
Route::get('/profile', function (Request $request) {
    // Check if user_id query parameter is provided (for demo purposes)
    $userId = $request->query('user_id');
    $userType = $request->query('user_type'); // 'client', 'caregiver', 'admin', 'marketing', 'training'
    
    if ($userId) {
        $user = User::find($userId);
    } else {
        // Try to get authenticated user
        $user = auth('web')->user();
        
        // Fallback based on user_type for demo purposes
        if (!$user) {
            if ($userType === 'caregiver') {
                $user = User::where('name', 'Demo Caregiver')->first();
            } elseif ($userType === 'admin') {
                $user = User::where('user_type', 'admin')->first();
            } elseif ($userType === 'marketing') {
                $user = User::where('user_type', 'marketing')->first();
            } elseif ($userType === 'training') {
                $user = User::where('user_type', 'training')->first();
            } else {
                $user = User::where('name', 'Demo Client')->first();
            }
        }
    }
    
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    
    // Get caregiver data if user is a caregiver
    $caregiver = null;
    if ($user->user_type === 'caregiver') {
        $caregiver = \App\Models\Caregiver::where('user_id', $user->id)->first();
    }
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'borough' => $user->borough,
            'state' => $user->state,
            'zip_code' => $user->zip_code,
            'date_of_birth' => $user->date_of_birth,
            'avatar' => $user->avatar,
            'user_type' => $user->user_type,
            'created_at' => $user->created_at
        ],
        'caregiver' => $caregiver ? [
            'id' => $caregiver->id,
            'years_experience' => $caregiver->years_experience,
            'specializations' => $caregiver->specializations,
            'bio' => $caregiver->bio
        ] : null
    ]);
});

Route::get('/ny-counties', function () {
    try {
        $counties = NYLocationService::getCounties();
        return response()->json($counties);
    } catch (\Exception $e) {
        // Fallback: read JSON directly
        try {
            $jsonData = Storage::get('data/ny_accurate_counties.json');
            $data = json_decode($jsonData, true);
            return response()->json(array_keys($data));
        } catch (\Exception $e2) {
            return response()->json(['error' => 'Failed to load counties'], 500);
        }
    }
});

Route::get('/ny-cities/{county}', function (string $county) {
    try {
        $cities = NYLocationService::getCitiesForCounty($county);
        return response()->json($cities);
    } catch (\Exception $e) {
        // Fallback: read JSON directly
        try {
            $jsonData = Storage::get('data/ny_accurate_counties.json');
            $data = json_decode($jsonData, true);
            return response()->json($data[$county] ?? []);
        } catch (\Exception $e2) {
            return response()->json(['error' => 'Failed to load cities'], 500);
        }
    }
});

Route::get('/caregiver/{id}/stats', [\App\Http\Controllers\DashboardController::class, 'caregiverStats']);
Route::get('/admin/stats', [\App\Http\Controllers\DashboardController::class, 'adminStats']);
Route::get('/admin/quick-caregivers', [\App\Http\Controllers\DashboardController::class, 'quickCaregivers']);
// Admin: get all bookings (full details)
Route::get('/admin/bookings', [\App\Http\Controllers\AdminController::class, 'getAllBookings']);

Route::post('/bookings/{id}/unassign', function ($id, Request $request) {
    try {
        $caregiverId = $request->input('caregiver_id');
        
        $deleted = DB::table('booking_assignments')
            ->where('booking_id', $id)
            ->where('caregiver_id', $caregiverId)
            ->delete();
            
        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'Caregiver unassigned successfully']);
        } else {
            return response()->json(['error' => 'Assignment not found'], 404);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to unassign caregiver: ' . $e->getMessage()], 500);
    }
});

Route::post('/reports/time-tracking-pdf', function (Request $request) {
    try {
        $data = $request->all();
        
        // Extract data with defaults
        $dateFilter = $data['dateFilter'] ?? 'This Week';
        $statusFilter = $data['statusFilter'] ?? 'All';
        $totalSessions = $data['totalSessions'] ?? '0';
        $totalHours = $data['totalHours'] ?? '0';
        $activeCaregivers = $data['activeCaregivers'] ?? '0';
        $avgHoursPerDay = $data['avgHoursPerDay'] ?? '0';
        $timeHistory = $data['timeHistory'] ?? [];
        
        // Build HTML directly instead of using include
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CAS Private Care - Time Tracking Report</title>
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
    </style>
</head>
<body>';

        // Logo
        $logoPath = public_path('logo.png');
        $logoHtml = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoHtml = '<img src="data:image/png;base64,' . $logoData . '" alt="CAS Logo">';
        }

        $html .= '
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
                    <div class="doc-id">Doc ID: TTR-' . date('Ymd-His') . '</div>
                </td>
            </tr>
        </table>
    </div>

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
                
                // Format hours
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
    </div>

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
                    Ref: TTR-' . date('Ymd') . '
                </td>
            </tr>
        </table>
        <div class="confidential">Confidential - For Internal Use Only</div>
    </div>
</body>
</html>';

        // Generate PDF with DomPDF
        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="CAS-TimeTracking-Report-' . date('Y-m-d') . '.pdf"'
            ]);
        } else {
            return response($html, 200, [
                'Content-Type' => 'text/html'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
    }
});

// Client Analytics PDF Export
Route::post('/reports/client-analytics-pdf', function (Request $request) {
    try {
        $data = $request->all();
        
        // Extract data with defaults
        $clientName = $data['clientName'] ?? 'Client';
        $totalSpent = $data['totalSpent'] ?? 0;
        $thisMonth = $data['thisMonth'] ?? 0;
        $avgPerMonth = $data['avgPerMonth'] ?? 0;
        $totalBookings = $data['totalBookings'] ?? 0;
        $totalHours = $data['totalHours'] ?? 0;
        $activeCaregivers = $data['activeCaregivers'] ?? 0;
        $selectedYear = $data['selectedYear'] ?? date('Y');
        $period = $data['period'] ?? 'month';
        
        // Get logo
        $logoPath = public_path('logo.png');
        $logoHtml = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoHtml = '<img src="data:image/png;base64,' . $logoData . '" alt="CAS Logo">';
        }
        
        // Build HTML for PDF
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CAS Private Care - Client Analytics Report</title>
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
        .report-info { margin-bottom: 15px; font-size: 9pt; }
        .report-info table { width: 100%; }
        .report-info td { padding: 2px 0; }
        .report-info .label { font-weight: bold; width: 100px; }
        .summary-section { margin-bottom: 20px; padding: 12px; border: 1px solid #000; }
        .summary-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #ccc; }
        .summary-table { width: 100%; }
        .summary-table td { text-align: center; padding: 8px; width: 33%; }
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
        .spending-breakdown { margin-top: 20px; }
        .spending-breakdown td { padding: 8px 12px; }
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
                    ' . date('F j, Y') . '<br>
                    ' . date('g:i A') . '
                    <div class="doc-id">Doc ID: CAR-' . date('Ymd-His') . '</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        <h1>Client Analytics Report</h1>
        <div class="subtitle">Financial Summary & Service Statistics</div>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td class="label">Client Name:</td>
                <td>' . htmlspecialchars($clientName) . '</td>
                <td class="label" style="padding-left: 20px;">Report Period:</td>
                <td>' . htmlspecialchars($selectedYear) . '</td>
            </tr>
            <tr>
                <td class="label">Generated By:</td>
                <td>Client Portal</td>
                <td class="label" style="padding-left: 20px;">Report Type:</td>
                <td>Analytics Summary</td>
            </tr>
        </table>
    </div>

    <div class="summary-section">
        <div class="summary-title">Financial Summary</div>
        <table class="summary-table">
            <tr>
                <td>
                    <div class="stat-value">$' . number_format($totalSpent, 2) . '</div>
                    <div class="stat-label">Total Spent</div>
                </td>
                <td>
                    <div class="stat-value">$' . number_format($thisMonth, 2) . '</div>
                    <div class="stat-label">This Month</div>
                </td>
                <td>
                    <div class="stat-value">$' . number_format($avgPerMonth, 2) . '</div>
                    <div class="stat-label">Avg per Month</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-section">
        <div class="summary-title">Service Statistics</div>
        <table class="summary-table">
            <tr>
                <td>
                    <div class="stat-value">' . htmlspecialchars($totalBookings) . '</div>
                    <div class="stat-label">Total Bookings</div>
                </td>
                <td>
                    <div class="stat-value">' . htmlspecialchars($totalHours) . ' hrs</div>
                    <div class="stat-label">Total Hours</div>
                </td>
                <td>
                    <div class="stat-value">' . htmlspecialchars($activeCaregivers) . '</div>
                    <div class="stat-label">Active Caregivers</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="data-section">
        <div class="section-title">Spending Breakdown</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Category</th>
                    <th style="width: 30%;" class="text-center">Value</th>
                    <th style="width: 30%;" class="text-center">Notes</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Amount Spent</td>
                    <td class="text-center font-bold">$' . number_format($totalSpent, 2) . '</td>
                    <td class="text-center">All Time</td>
                </tr>
                <tr>
                    <td>Current Month Spending</td>
                    <td class="text-center font-bold">$' . number_format($thisMonth, 2) . '</td>
                    <td class="text-center">' . date('F Y') . '</td>
                </tr>
                <tr>
                    <td>Average Monthly Spending</td>
                    <td class="text-center font-bold">$' . number_format($avgPerMonth, 2) . '</td>
                    <td class="text-center">Based on history</td>
                </tr>
                <tr>
                    <td>Total Care Hours Received</td>
                    <td class="text-center font-bold">' . htmlspecialchars($totalHours) . ' hours</td>
                    <td class="text-center">Completed services</td>
                </tr>
                <tr>
                    <td>Number of Bookings</td>
                    <td class="text-center font-bold">' . htmlspecialchars($totalBookings) . '</td>
                    <td class="text-center">All bookings</td>
                </tr>
                <tr>
                    <td>Caregivers Worked With</td>
                    <td class="text-center font-bold">' . htmlspecialchars($activeCaregivers) . '</td>
                    <td class="text-center">Unique caregivers</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td><div class="signature-line">Client Acknowledgment</div></td>
                <td style="width: 10%;"></td>
                <td><div class="signature-line">Prepared By</div></td>
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
                    This is an official document<br>
                    Generated: ' . date('M j, Y g:i A') . '
                </td>
                <td class="footer-right">
                    Page 1 of 1<br>
                    Ref: CAR-' . date('Ymd') . '
                </td>
            </tr>
        </table>
        <div class="confidential">Confidential - Client Financial Information</div>
    </div>
</body>
</html>';

        // Generate PDF with DomPDF
        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="CAS-Client-Analytics-' . date('Y-m-d') . '.pdf"'
            ]);
        } else {
            return response($html, 200, [
                'Content-Type' => 'text/html'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
    }
});

Route::get('/time-tracking/history', function () {
    try {
        // Join through caregivers table to get caregiver user name
        // Join through clients table to get client user name
        $timeTrackings = DB::table('time_trackings')
            ->leftJoin('caregivers', 'time_trackings.caregiver_id', '=', 'caregivers.id')
            ->leftJoin('users as caregiver_users', 'caregivers.user_id', '=', 'caregiver_users.id')
            ->leftJoin('clients', 'time_trackings.client_id', '=', 'clients.id')
            ->leftJoin('users as client_users', 'clients.user_id', '=', 'client_users.id')
            ->select(
                'time_trackings.*',
                'caregiver_users.name as caregiver_name',
                'client_users.name as client_name'
            )
            ->orderBy('time_trackings.work_date', 'desc')
            ->orderBy('time_trackings.clock_in_time', 'desc')
            ->get();
        
        $history = $timeTrackings->map(function ($record) {
            $clockIn = $record->clock_in_time ? date('g:i A', strtotime($record->clock_in_time)) : 'N/A';
            $clockOut = $record->clock_out_time ? date('g:i A', strtotime($record->clock_out_time)) : 'N/A';
            $workDate = $record->work_date ? date('m/d/Y', strtotime($record->work_date)) : 'N/A';
            
            return [
                'id' => $record->id,
                'date' => $workDate,
                'caregiver' => $record->caregiver_name ?? 'Unknown Caregiver',
                'client' => $record->client_name ?? 'Unknown Client',
                'clockIn' => $clockIn,
                'clockOut' => $clockOut,
                'hoursWorked' => (float) $record->hours_worked,
                'status' => $record->status === 'active' ? 'Active' : 'Completed'
            ];
        });
        
        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to load time tracking history: ' . $e->getMessage()], 500);
    }
});

// Payment Methods API Routes
Route::prefix('payment-methods')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\PaymentMethodController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\PaymentMethodController::class, 'store']);
    Route::put('/{id}', [App\Http\Controllers\Api\PaymentMethodController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\PaymentMethodController::class, 'destroy']);
    Route::post('/{id}/set-default', [App\Http\Controllers\Api\PaymentMethodController::class, 'setDefault']);
});

// Reviews & Ratings API Routes
Route::prefix('reviews')->group(function () {
    Route::get('/', [App\Http\Controllers\ReviewController::class, 'index']); // Admin only
    Route::get('/my-reviews', [App\Http\Controllers\ReviewController::class, 'getClientReviews']);
    Route::get('/caregiver/{caregiverId}', [App\Http\Controllers\ReviewController::class, 'getCaregiverReviews']);
    Route::get('/booking/{bookingId}/can-review', [App\Http\Controllers\ReviewController::class, 'canReview']);
    Route::post('/', [App\Http\Controllers\ReviewController::class, 'store']);
    Route::put('/{id}', [App\Http\Controllers\ReviewController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\ReviewController::class, 'destroy']);
});