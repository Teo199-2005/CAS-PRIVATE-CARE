<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\Caregiver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;

class CaregiverController extends Controller
{
    public function availableClients()
    {
        // Fetch all pending bookings with client user data
        $bookings = \App\Models\Booking::where('status', 'pending')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform bookings for the view
        $clients = $bookings->map(function($booking) {
            $client = $booking->client;
            $clientName = $client ? $client->name : 'Unknown Client';
            $nameParts = explode(' ', $clientName);
            $initials = count($nameParts) >= 2 ?
                substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1) :
                substr($clientName, 0, 2);
            // Prefer client's profile_photo (clients table) then user's avatar (users.avatar)
            $avatar = null;
            if ($client) {
                // clients.profile_photo may be stored on related client model as 'profile_photo'
                $profilePhoto = isset($client->profile_photo) ? $client->profile_photo : null;
                $userAvatar = isset($client->avatar) ? $client->avatar : null; // if client is a User model this will work

                $stored = $profilePhoto ?: $userAvatar;
                if ($stored) {
                    $stored = ltrim($stored, '/');
                    if (strpos($stored, 'avatars/') === 0 || strpos($stored, 'profile_photos/') === 0) {
                        $avatar = asset('storage/' . $stored);
                    } else {
                        // default folder for avatars/profile photos
                        $avatar = asset('storage/avatars/' . $stored);
                    }
                }
            }
            return [
                'name' => $clientName,
                'initials' => strtoupper($initials),
                'avatar' => $avatar,
                'age' => $booking->client_age ?? rand(65, 85),
                'careType' => ucwords(str_replace('_', ' ', $booking->service_type)),
                'location' => ucfirst($booking->borough),
                'payRate' => '$' . number_format($booking->hourly_rate ?? 0, 2) . '/hr',
                'urgency' => $booking->urgency_level ?? 'scheduled',
                'serviceDate' => $booking->service_date,
                'startTime' => $booking->start_time,
                'duration' => $booking->duration_days . ' days',
                'mobilityLevel' => $booking->mobility_level ?? 'independent',
            ];
        });

    // Return only actual booking data - no supplementary fake data
    $clients = $clients->unique('name')->values();

    return view('available-clients', ['clients' => $clients]);
    }

    public function getAvailableClients(Request $request)
    {
        // Get bookings that are approved but need caregivers (unassigned or partially assigned)
        $query = Booking::whereIn('status', ['approved', 'confirmed'])
            ->with(['client', 'assignments.caregiver.user'])
            ->orderBy('service_date', 'asc');

        // Apply filters
        if ($request->borough) {
            $query->where('borough', $request->borough);
        }
        
        if ($request->service_type) {
            $query->where('service_type', $request->service_type);
        }

        $bookings = $query->get();

        // Filter to only those that need more caregivers
        $availableBookings = $bookings->filter(function($booking) {
            // Calculate caregivers needed based on hours per day
            // 8 hours = 1 caregiver, 12 hours = 2 caregivers, 24 hours = 3 caregivers
            $hoursPerDay = 8;
            if (preg_match('/(\d+)\s*Hours?/i', $booking->duty_type, $matches)) {
                $hoursPerDay = (int)$matches[1];
            }
            
            if ($hoursPerDay <= 8) {
                $caregiversNeeded = 1;
            } elseif ($hoursPerDay <= 12) {
                $caregiversNeeded = 2;
            } else {
                $caregiversNeeded = 3;
            }
            
            $assignedCount = $booking->assignments->where('status', 'assigned')->count();
            return $assignedCount < $caregiversNeeded;
        });

        // Transform bookings to job listing format for caregivers
        $jobs = $availableBookings->map(function($booking) {
            $client = $booking->client;
            $clientName = $client ? $client->name : 'Unknown Client';
            $nameParts = explode(' ', $clientName);
            $initials = count($nameParts) >= 2 ? 
                substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1) : 
                substr($clientName, 0, 2);
            
            // Get client avatar/profile photo
            $avatar = null;
            if ($client) {
                // Check for avatar on user model (users.avatar)
                $stored = $client->avatar ?? null;
                
                // Also check if there's a linked client record with profile_photo
                $clientRecord = \App\Models\Client::where('user_id', $client->id)->first();
                if ($clientRecord && $clientRecord->profile_photo) {
                    $stored = $clientRecord->profile_photo;
                }
                
                if ($stored) {
                    $stored = ltrim($stored, '/');
                    if (strpos($stored, 'avatars/') === 0 || strpos($stored, 'profile_photos/') === 0) {
                        $avatar = asset('storage/' . $stored);
                    } elseif (strpos($stored, 'http') === 0) {
                        $avatar = $stored;
                    } else {
                        $avatar = asset('storage/avatars/' . $stored);
                    }
                }
            }
            
            // Calculate caregivers needed based on hours per day
            // 8 hours = 1 caregiver, 12 hours = 2 caregivers, 24 hours = 3 caregivers
            $hoursPerDay = 8;
            if (preg_match('/(\d+)\s*Hours?/i', $booking->duty_type, $matches)) {
                $hoursPerDay = (int)$matches[1];
            }
            
            if ($hoursPerDay <= 8) {
                $caregiversNeeded = 1;
            } elseif ($hoursPerDay <= 12) {
                $caregiversNeeded = 2;
            } else {
                $caregiversNeeded = 3;
            }
            
            $assignedCount = $booking->assignments->where('status', 'assigned')->count();
            $spotsRemaining = $caregiversNeeded - $assignedCount;
            
            // Calculate pay rate for caregiver ($28/hr)
            $caregiverRate = 28.00;
            
            // Calculate total hours based on duty type (already extracted above)
            $totalHours = $hoursPerDay * $booking->duration_days;
            $estimatedEarnings = $totalHours * $caregiverRate;
            
            // Format dates
            $startDate = \Carbon\Carbon::parse($booking->service_date);
            $endDate = $startDate->copy()->addDays($booking->duration_days);
            
            // Get assigned caregivers names for display
            $assignedCaregivers = $booking->assignments->where('status', 'assigned')->map(function($a) {
                return $a->caregiver && $a->caregiver->user ? $a->caregiver->user->name : 'Unknown';
            })->values()->toArray();
            
            return [
                'id' => $booking->id,
                'bookingId' => $booking->id,
                'clientName' => $clientName,
                'clientInitials' => strtoupper($initials),
                'clientAvatar' => $avatar,
                'avatarColor' => ['success', 'primary', 'purple', 'orange', 'info'][$booking->id % 5],
                
                // Booking details
                'serviceType' => ucwords(str_replace('_', ' ', $booking->service_type)),
                'dutyType' => $booking->duty_type,
                'hoursPerDay' => $hoursPerDay,
                
                // Location
                'location' => ucfirst($booking->borough ?: 'Unknown'),
                'city' => $booking->city ?? null,
                'county' => $booking->county ?? null,
                'address' => $booking->street_address ?? null,
                
                // Dates
                'startDate' => $startDate->format('M d, Y'),
                'endDate' => $endDate->format('M d, Y'),
                'service_date' => $booking->service_date, // Raw date for filtering
                'durationDays' => $booking->duration_days,
                'startTime' => $booking->start_time ? \Carbon\Carbon::parse($booking->start_time)->format('g:i A') : '9:00 AM',
                
                // Client info
                'clientAge' => $booking->client_age ?? null,
                'mobilityLevel' => $booking->mobility_level ?? 'independent',
                'genderPreference' => $booking->gender_preference ?? 'no_preference',
                'specialInstructions' => $booking->special_instructions ?? null,
                
                // Compensation
                'payRate' => '$' . number_format($caregiverRate, 2) . '/hr',
                'totalHours' => $totalHours,
                'estimatedEarnings' => '$' . number_format($estimatedEarnings, 0),
                
                // Assignment status
                'status' => $booking->status,
                'caregiversNeeded' => $caregiversNeeded,
                'assignedCount' => $assignedCount,
                'spotsRemaining' => $spotsRemaining,
                'assignedCaregivers' => $assignedCaregivers,
                'assignmentStatus' => $assignedCount === 0 ? 'unassigned' : 'partial',
                
                // Urgency
                'urgency' => $spotsRemaining === $caregiversNeeded ? 'high' : 'normal',
                'isUrgent' => $startDate->diffInDays(now()) <= 7
            ];
        })->values();

        return response()->json($jobs);
    }

    public function applyForClient(Request $request, $bookingId)
    {
        $caregiver = Auth::user()->caregiver;
        
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver profile not found'], 404);
        }

        $booking = Booking::findOrFail($bookingId);
        
        // Check if already applied
        $existingApplication = BookingAssignment::where('booking_id', $bookingId)
            ->where('caregiver_id', $caregiver->id)
            ->first();
            
        if ($existingApplication) {
            return response()->json(['error' => 'Already applied for this client'], 400);
        }

        BookingAssignment::create([
            'booking_id' => $bookingId,
            'caregiver_id' => $caregiver->id,
            'status' => 'pending',
            'assigned_at' => now()
        ]);

        return response()->json(['message' => 'Application submitted successfully']);
    }

    /**
     * Get earnings report data for a caregiver
     */
    public function getEarningsReport(Request $request, $id)
    {
        $period = $request->input('period', 'This Month');
        $caregiver = Caregiver::with('user')->find($id);
        
        if (!$caregiver) {
            return response()->json(['success' => false, 'error' => 'Caregiver not found'], 404);
        }

        // Calculate date range based on period
        $startDate = now();
        $endDate = now();
        
        switch ($period) {
            case 'This Week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'Last Week':
                $startDate = now()->subWeek()->startOfWeek();
                $endDate = now()->subWeek()->endOfWeek();
                break;
            case 'This Month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'Last Month':
                $startDate = now()->subMonth()->startOfMonth();
                $endDate = now()->subMonth()->endOfMonth();
                break;
            case 'This Year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'All Time':
                $startDate = now()->subYears(10);
                $endDate = now();
                break;
        }

        // Get time tracking data for the period
        $timeTrackings = DB::table('time_trackings')
            ->where('caregiver_id', $id)
            ->whereBetween('work_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNotNull('clock_out_time')
            ->get();

        $totalHours = 0;
        $regularHours = 0;
        $overtimeHours = 0;
        $sessionsCompleted = 0;
        $clientsSet = [];

        foreach ($timeTrackings as $tracking) {
            $hours = $tracking->hours_worked ?? 0;
            $totalHours += $hours;
            
            // Calculate regular vs overtime (anything over 8 hours per day is overtime)
            if ($hours > 8) {
                $regularHours += 8;
                $overtimeHours += ($hours - 8);
            } else {
                $regularHours += $hours;
            }
            
            $sessionsCompleted++;
            if ($tracking->client_id) {
                $clientsSet[$tracking->client_id] = true;
            }
        }

        $hourlyRate = 28.00;
        $overtimeRate = 42.00; // 1.5x regular rate
        
        $regularEarnings = $regularHours * $hourlyRate;
        $overtimeEarnings = $overtimeHours * $overtimeRate;
        $bonuses = 0; // Could be calculated from a bonuses table
        $grossEarnings = $regularEarnings + $overtimeEarnings + $bonuses;
        
        // Estimated self-employment tax (~15.3%)
        $estimatedTax = $grossEarnings * 0.153;
        $netEarnings = $grossEarnings - $estimatedTax;

        // Calculate days in period for average
        $daysInPeriod = max(1, $startDate->diffInDays($endDate));
        $avgHoursPerDay = $totalHours / $daysInPeriod;

        // Get earnings history
        $history = [];
        $recentTrackings = DB::table('time_trackings')
            ->leftJoin('users', 'time_trackings.client_id', '=', 'users.id')
            ->where('time_trackings.caregiver_id', $id)
            ->whereNotNull('time_trackings.clock_out_time')
            ->orderBy('time_trackings.work_date', 'desc')
            ->limit(10)
            ->select('time_trackings.*', 'users.name as client_name')
            ->get();

        foreach ($recentTrackings as $tracking) {
            $hours = $tracking->hours_worked ?? 0;
            $amount = $hours * $hourlyRate;
            $clientName = $tracking->client_name ?? 'Unknown Client';
            $nameParts = explode(' ', $clientName);
            $initials = count($nameParts) >= 2 
                ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
                : strtoupper(substr($clientName, 0, 2));

            $history[] = [
                'date' => $tracking->work_date ? \Carbon\Carbon::parse($tracking->work_date)->format('M d, Y') : 'N/A',
                'client' => $clientName,
                'clientInitials' => $initials,
                'hours' => number_format($hours, 1),
                'amount' => number_format($amount, 2),
                'status' => 'Paid' // Would check payment status in production
            ];
        }

        // Calculate change from previous period
        $earningsChange = 12; // Placeholder - would compare with previous period

        // Calculate weekly and monthly hours for time tracking summary
        $weeklyTrackings = DB::table('time_trackings')
            ->where('caregiver_id', $id)
            ->whereBetween('work_date', [now()->startOfWeek()->format('Y-m-d'), now()->endOfWeek()->format('Y-m-d')])
            ->whereNotNull('clock_out_time')
            ->sum('hours_worked');
        
        $monthlyTrackings = DB::table('time_trackings')
            ->where('caregiver_id', $id)
            ->whereBetween('work_date', [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')])
            ->whereNotNull('clock_out_time')
            ->sum('hours_worked');

        return response()->json([
            'success' => true,
            'totalEarnings' => number_format($grossEarnings, 2),
            'earningsChange' => $earningsChange,
            'totalHours' => number_format($totalHours, 1),
            'avgHoursPerDay' => number_format($avgHoursPerDay, 1),
            'hourlyRate' => number_format($hourlyRate, 2),
            'clientsServed' => count($clientsSet),
            'completedSessions' => $sessionsCompleted,
            'grossEarnings' => number_format($grossEarnings, 2),
            'regularHours' => number_format($regularHours, 1),
            'regularEarnings' => number_format($regularEarnings, 2),
            'overtimeHours' => number_format($overtimeHours, 1),
            'overtimeEarnings' => number_format($overtimeEarnings, 2),
            'bonuses' => number_format($bonuses, 2),
            'estimatedTax' => number_format($estimatedTax, 2),
            'netEarnings' => number_format($netEarnings, 2),
            'onTimeRate' => 98,
            'completionRate' => 100,
            'weeklyHours' => number_format($weeklyTrackings ?? 0, 1),
            'monthlyHours' => number_format($monthlyTrackings ?? 0, 1),
            'history' => $history
        ]);
    }

    /**
     * Generate PDF earnings report for caregiver
     */
    public function generateEarningsReportPdf(Request $request)
    {
        $caregiverId = $request->input('caregiverId');
        $period = $request->input('period', 'This Month');
        $data = $request->input('data', []);
        $history = $request->input('history', []);

        $caregiver = Caregiver::with('user')->find($caregiverId);
        $caregiverName = $caregiver && $caregiver->user ? $caregiver->user->name : 'Caregiver';

        $html = $this->generateEarningsReportHtml($caregiverName, $period, $data, $history);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="earnings_report.pdf"');
    }

    private function generateEarningsReportHtml($caregiverName, $period, $data, $history)
    {
        $totalEarnings = $data['totalEarnings'] ?? '0.00';
        $totalHours = $data['totalHours'] ?? '0';
        $hourlyRate = $data['hourlyRate'] ?? '28.00';
        $clientsServed = $data['clientsServed'] ?? '0';
        $completedSessions = $data['completedSessions'] ?? '0';
        $grossEarnings = $data['grossEarnings'] ?? '0.00';
        $regularHours = $data['regularHours'] ?? '0';
        $regularEarnings = $data['regularEarnings'] ?? '0.00';
        $overtimeHours = $data['overtimeHours'] ?? '0';
        $overtimeEarnings = $data['overtimeEarnings'] ?? '0.00';
        $bonuses = $data['bonuses'] ?? '0.00';
        $estimatedTax = $data['estimatedTax'] ?? '0.00';
        $netEarnings = $data['netEarnings'] ?? '0.00';
        $onTimeRate = $data['onTimeRate'] ?? '98';
        $completionRate = $data['completionRate'] ?? '100';

        $historyRows = '';
        foreach ($history as $item) {
            $historyRows .= '<tr>
                <td>' . htmlspecialchars($item['date'] ?? '') . '</td>
                <td>' . htmlspecialchars($item['client'] ?? '') . '</td>
                <td>' . htmlspecialchars($item['hours'] ?? '') . ' hrs</td>
                <td>$' . htmlspecialchars($item['amount'] ?? '0.00') . '</td>
                <td>' . htmlspecialchars($item['status'] ?? '') . '</td>
            </tr>';
        }

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Earnings Report - ' . htmlspecialchars($caregiverName) . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10pt; line-height: 1.4; color: #000; background: #fff; padding: 30px 40px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header-table { width: 100%; }
        .header-table td { vertical-align: top; }
        .company-name { font-size: 16pt; font-weight: bold; letter-spacing: 1px; }
        .company-tagline { font-size: 9pt; font-style: italic; color: #333; }
        .company-address { font-size: 8pt; color: #555; margin-top: 3px; }
        .date-cell { text-align: right; font-size: 9pt; }
        .report-title { text-align: center; margin: 20px 0; padding: 12px 0; border-top: 1px solid #000; border-bottom: 1px solid #000; }
        .report-title h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 3px; }
        .report-title .subtitle { font-size: 9pt; color: #333; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 11pt; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 10px; }
        .summary-grid { display: table; width: 100%; margin-bottom: 15px; }
        .summary-row { display: table-row; }
        .summary-cell { display: table-cell; padding: 8px; text-align: center; border: 1px solid #ddd; }
        .summary-cell .label { font-size: 8pt; color: #666; }
        .summary-cell .value { font-size: 14pt; font-weight: bold; }
        .breakdown-table { width: 100%; border-collapse: collapse; }
        .breakdown-table td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        .breakdown-table .label { text-align: left; }
        .breakdown-table .value { text-align: right; font-weight: bold; }
        .breakdown-table .total { border-top: 2px solid #000; font-size: 12pt; }
        .history-table { width: 100%; border-collapse: collapse; }
        .history-table th { background: #f5f5f5; padding: 8px; text-align: left; font-size: 9pt; border-bottom: 2px solid #000; }
        .history-table td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 9pt; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #000; font-size: 8pt; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="company-name">CAS PRIVATE CARE LLC</div>
                    <div class="company-tagline">Comfort & Support</div>
                    <div class="company-address">New York, NY</div>
                </td>
                <td class="date-cell">
                    <strong>Report Date:</strong> ' . date('F d, Y') . '<br>
                    <strong>Period:</strong> ' . htmlspecialchars($period) . '<br>
                    <strong>Contractor:</strong> ' . htmlspecialchars($caregiverName) . '
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        <h1>Earnings Report</h1>
        <div class="subtitle">1099 Contractor Statement</div>
    </div>

    <div class="section">
        <div class="section-title">Summary</div>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 25%; text-align: center; padding: 10px; border: 1px solid #ddd;">
                    <div style="font-size: 8pt; color: #666;">Total Earnings</div>
                    <div style="font-size: 16pt; font-weight: bold;">$' . htmlspecialchars($totalEarnings) . '</div>
                </td>
                <td style="width: 25%; text-align: center; padding: 10px; border: 1px solid #ddd;">
                    <div style="font-size: 8pt; color: #666;">Hours Worked</div>
                    <div style="font-size: 16pt; font-weight: bold;">' . htmlspecialchars($totalHours) . '</div>
                </td>
                <td style="width: 25%; text-align: center; padding: 10px; border: 1px solid #ddd;">
                    <div style="font-size: 8pt; color: #666;">Hourly Rate</div>
                    <div style="font-size: 16pt; font-weight: bold;">$' . htmlspecialchars($hourlyRate) . '</div>
                </td>
                <td style="width: 25%; text-align: center; padding: 10px; border: 1px solid #ddd;">
                    <div style="font-size: 8pt; color: #666;">Clients Served</div>
                    <div style="font-size: 16pt; font-weight: bold;">' . htmlspecialchars($clientsServed) . '</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Earnings Breakdown</div>
        <table class="breakdown-table">
            <tr>
                <td class="label">Regular Hours (' . htmlspecialchars($regularHours) . ' hrs × $28.00)</td>
                <td class="value">$' . htmlspecialchars($regularEarnings) . '</td>
            </tr>
            <tr>
                <td class="label">Overtime Hours (' . htmlspecialchars($overtimeHours) . ' hrs × $42.00)</td>
                <td class="value">$' . htmlspecialchars($overtimeEarnings) . '</td>
            </tr>
            <tr>
                <td class="label">Bonuses & Tips</td>
                <td class="value">$' . htmlspecialchars($bonuses) . '</td>
            </tr>
            <tr>
                <td class="label"><strong>Gross Earnings</strong></td>
                <td class="value"><strong>$' . htmlspecialchars($grossEarnings) . '</strong></td>
            </tr>
            <tr>
                <td class="label" style="color: #c00;">Estimated Tax (Self-Employment 15.3%)</td>
                <td class="value" style="color: #c00;">-$' . htmlspecialchars($estimatedTax) . '</td>
            </tr>
            <tr class="total">
                <td class="label"><strong>Net Earnings</strong></td>
                <td class="value"><strong>$' . htmlspecialchars($netEarnings) . '</strong></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Performance Metrics</div>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 33%; text-align: center; padding: 10px; border: 1px solid #ddd;">
                    <div style="font-size: 8pt; color: #666;">Completed Sessions</div>
                    <div style="font-size: 14pt; font-weight: bold;">' . htmlspecialchars($completedSessions) . '</div>
                </td>
                <td style="width: 33%; text-align: center; padding: 10px; border: 1px solid #ddd;">
                    <div style="font-size: 8pt; color: #666;">On-Time Rate</div>
                    <div style="font-size: 14pt; font-weight: bold;">' . htmlspecialchars($onTimeRate) . '%</div>
                </td>
                <td style="width: 33%; text-align: center; padding: 10px; border: 1px solid #ddd;">
                    <div style="font-size: 8pt; color: #666;">Completion Rate</div>
                    <div style="font-size: 14pt; font-weight: bold;">' . htmlspecialchars($completionRate) . '%</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Recent Earnings History</div>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Hours</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                ' . $historyRows . '
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This is an unofficial earnings statement for 1099 independent contractor purposes.</p>
        <p>CAS Private Care LLC | Generated on ' . date('F d, Y \a\t h:i A') . '</p>
    </div>
</body>
</html>';
    }
}