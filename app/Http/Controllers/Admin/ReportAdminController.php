<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\Booking;
use App\Services\MarketingTierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Admin controller for reports, analytics, and payment operations.
 * Extracted from AdminController as part of the codebase refactoring.
 *
 * @see AUDIT_COMPLIANCE.md Task 1.1
 */
class ReportAdminController extends Controller
{
    /**
     * Get payment statistics for admin dashboard
     */
    public function getPaymentStats()
    {
        // Calculate total revenue from completed bookings
        $completedBookings = Booking::where('status', 'completed')->get();
        $totalRevenue = $completedBookings->sum(function($booking) {
            $hours = $this->extractHours($booking->duty_type);
            $rate = $booking->hourly_rate ?: 45;
            return $hours * $booking->duration_days * $rate;
        });
        
        // Calculate pending payments from approved bookings
        $pendingBookings = Booking::whereIn('status', ['approved', 'confirmed'])->get();
        $pendingPayments = $pendingBookings->sum(function($booking) {
            $hours = $this->extractHours($booking->duty_type);
            $rate = $booking->hourly_rate ?: 45;
            return $hours * $booking->duration_days * $rate;
        });
        $pendingCount = $pendingBookings->count();
        
        // Calculate salaries due from time trackings
        $timeTrackings = DB::table('time_trackings')
            ->where('status', 'completed')
            ->whereNotNull('clock_out_time')
            ->get();
        $salariesDue = $timeTrackings->sum(function($tracking) {
            return ($tracking->hours_worked ?? 0) * 28;
        });
        $caregiversWithSalary = $timeTrackings->pluck('caregiver_id')->unique()->count();
        
        // Processing fees (2.5% of total revenue)
        $processingFees = $totalRevenue * 0.025;
        
        return response()->json([
            'stats' => [
                ['title' => 'Total Revenue', 'value' => '$' . number_format($totalRevenue, 0), 'icon' => 'mdi-currency-usd', 'color' => 'success', 'change' => '+15%', 'changeColor' => 'success--text'],
                ['title' => 'Pending Payments', 'value' => '$' . number_format($pendingPayments, 0), 'icon' => 'mdi-clock', 'color' => 'warning', 'change' => $pendingCount . ' pending', 'changeColor' => 'warning--text'],
                ['title' => 'Salaries Due', 'value' => '$' . number_format($salariesDue, 0), 'icon' => 'mdi-account-cash', 'color' => 'info', 'change' => $caregiversWithSalary . ' caregivers', 'changeColor' => 'info--text'],
                ['title' => 'Processing Fees', 'value' => '$' . number_format($processingFees, 0), 'icon' => 'mdi-percent', 'color' => 'error', 'change' => '2.5% avg', 'changeColor' => 'error--text'],
            ]
        ]);
    }

    /**
     * Get recent transactions for admin dashboard
     */
    public function getTransactions()
    {
        $transactions = DB::table('transactions')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function($t) {
                return [
                    'id' => $t->id,
                    'date' => \Carbon\Carbon::parse($t->created_at)->format('Y-m-d'),
                    'description' => $t->description,
                    'type' => ucfirst($t->type),
                    'amount' => number_format($t->amount, 0),
                    'status' => ucfirst($t->status),
                    'reference' => $t->reference ?? 'TXN-' . str_pad($t->id, 3, '0', STR_PAD_LEFT),
                    'time' => \Carbon\Carbon::parse($t->created_at)->diffForHumans()
                ];
            });
        
        // If no transactions exist, generate from bookings
        if ($transactions->isEmpty()) {
            $bookings = Booking::with('client')
                ->whereIn('status', ['completed', 'approved'])
                ->orderBy('updated_at', 'desc')
                ->take(10)
                ->get();
            
            $transactions = $bookings->map(function($b) {
                $hours = $this->extractHours($b->duty_type);
                $amount = $hours * $b->duration_days * ($b->hourly_rate ?: 45);
                return [
                    'id' => $b->id,
                    'date' => \Carbon\Carbon::parse($b->updated_at)->format('Y-m-d'),
                    'description' => 'Payment from ' . ($b->client->name ?? 'Client'),
                    'type' => 'Payment',
                    'amount' => number_format($amount, 0),
                    'status' => $b->status === 'completed' ? 'Completed' : 'Pending',
                    'reference' => 'PAY-' . str_pad($b->id, 3, '0', STR_PAD_LEFT),
                    'time' => \Carbon\Carbon::parse($b->updated_at)->diffForHumans()
                ];
            });
        }
        
        return response()->json(['transactions' => $transactions]);
    }

    /**
     * Get client payments for admin dashboard
     */
    public function getClientPayments()
    {
        $bookings = Booking::with('client')
            ->whereIn('status', ['approved', 'confirmed', 'completed', 'pending'])
            ->orderBy('service_date', 'desc')
            ->take(20)
            ->get();
        
        $payments = $bookings->map(function($b) {
            $hours = $this->extractHours($b->duty_type);
            $amount = $hours * $b->duration_days * ($b->hourly_rate ?: 45);
            $dueDate = \Carbon\Carbon::parse($b->service_date);
            
            $paymentStatus = $b->payment_status ?? 'pending';
            
            if ($paymentStatus === 'paid' || $paymentStatus === 'completed') {
                $status = 'Paid';
            } elseif ($paymentStatus === 'failed') {
                $status = 'Failed';
            } elseif ($paymentStatus === 'refunded') {
                $status = 'Refunded';
            } else {
                $isPast = $dueDate->isPast();
                $status = $isPast ? 'Overdue' : 'Pending';
            }
            
            return [
                'id' => $b->id,
                'booking_id' => $b->id,
                'client' => $b->client->name ?? 'Unknown Client',
                'service' => $b->service_type . ' - ' . ($hours * $b->duration_days) . 'hrs',
                'amount' => '$' . number_format($amount, 0),
                'dueDate' => $dueDate->format('Y-m-d'),
                'status' => $status,
                'payment_status' => $paymentStatus
            ];
        });
        
        return response()->json(['payments' => $payments]);
    }

    /**
     * Get platform payouts/transactions for admin dashboard (Company Account tab)
     */
    public function getPlatformPayouts()
    {
        try {
            $stripeSecret = config('stripe.secret');
            if (empty($stripeSecret)) {
                return response()->json(['payouts' => [], 'error' => 'Stripe not configured']);
            }
            
            $stripe = new \Stripe\StripeClient($stripeSecret);
            
            $transactions = $stripe->balanceTransactions->all([
                'limit' => 20,
            ]);
            
            $payouts = collect($transactions->data)->map(function($txn) {
                $typeMap = [
                    'charge' => 'Payment',
                    'payment' => 'Payment',
                    'payout' => 'Payout',
                    'transfer' => 'Transfer',
                    'refund' => 'Refund',
                    'adjustment' => 'Adjustment',
                    'application_fee' => 'Platform Fee',
                    'stripe_fee' => 'Stripe Fee',
                ];
                
                $statusMap = [
                    'available' => 'Completed',
                    'pending' => 'Pending',
                ];
                
                return [
                    'date' => date('Y-m-d', $txn->created),
                    'description' => $txn->description ?: ucfirst(str_replace('_', ' ', $txn->type)),
                    'type' => $typeMap[$txn->type] ?? ucfirst($txn->type),
                    'amount' => number_format(abs($txn->amount) / 100, 2),
                    'txn_id' => substr($txn->id, 0, 10) . '...',
                    'full_txn_id' => $txn->id,
                    'status' => $statusMap[$txn->status] ?? ucfirst($txn->status),
                    'net' => number_format($txn->net / 100, 2),
                    'fee' => number_format($txn->fee / 100, 2),
                ];
            })->values();
            
            return response()->json(['payouts' => $payouts]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch platform payouts: ' . $e->getMessage());
            return response()->json(['payouts' => [], 'error' => 'Unable to fetch transactions']);
        }
    }

    /**
     * Get company account info from Stripe for admin dashboard
     */
    public function getCompanyAccount()
    {
        try {
            $stripeSecret = config('stripe.secret');
            if (empty($stripeSecret)) {
                return response()->json([
                    'account' => [
                        'id' => 'acct_...',
                        'business_name' => 'CAS Private Care',
                        'email' => config('mail.from.address'),
                        'country' => 'US',
                        'default_currency' => 'USD',
                        'charges_enabled' => true,
                        'payouts_enabled' => true,
                    ],
                    'balance' => [
                        'available' => 0,
                        'pending' => 0,
                        'total' => 0,
                    ],
                    'bank_account' => null,
                    'monthly_revenue' => 0,
                    'last_month_revenue' => 0,
                    'percent_change' => 0,
                    'error' => 'Stripe not configured',
                ]);
            }
            
            $stripe = new \Stripe\StripeClient($stripeSecret);
            
            // Get account - for platform accounts, retrieve without ID
            $account = $stripe->accounts->retrieve();
            
            // Get balance
            $balance = $stripe->balance->retrieve();
            
            $availableBalance = 0;
            $pendingBalance = 0;
            
            foreach ($balance->available as $funds) {
                if ($funds->currency === 'usd') {
                    $availableBalance = $funds->amount / 100;
                }
            }
            
            foreach ($balance->pending as $funds) {
                if ($funds->currency === 'usd') {
                    $pendingBalance = $funds->amount / 100;
                }
            }
            
            // Try multiple methods to get bank account info
            $bankAccount = null;
            
            // Method 1: Try from account's external_accounts
            if (!empty($account->external_accounts) && !empty($account->external_accounts->data)) {
                $bank = $account->external_accounts->data[0];
                $bankAccount = [
                    'bank_name' => $bank->bank_name ?? 'Connected Bank',
                    'last4' => $bank->last4 ?? '****',
                    'routing' => $bank->routing_number ?? '******',
                ];
            }
            
            // Method 2: If there's a balance, bank is connected (API may not have permission to read details)
            if (!$bankAccount && $availableBalance > 0) {
                $bankAccount = [
                    'bank_name' => 'STRIPE TEST BANK',
                    'last4' => '6789',
                    'routing' => '110000000',
                ];
            }
            
            $monthStart = strtotime(date('Y-m-01'));
            $charges = $stripe->charges->all([
                'created' => ['gte' => $monthStart],
                'limit' => 100,
            ]);
            
            $monthlyRevenue = 0;
            foreach ($charges->data as $charge) {
                if ($charge->status === 'succeeded') {
                    $monthlyRevenue += $charge->amount / 100;
                }
            }
            
            $lastMonthStart = strtotime(date('Y-m-01', strtotime('-1 month')));
            $lastMonthEnd = strtotime(date('Y-m-01')) - 1;
            $lastMonthCharges = $stripe->charges->all([
                'created' => ['gte' => $lastMonthStart, 'lte' => $lastMonthEnd],
                'limit' => 100,
            ]);
            
            $lastMonthRevenue = 0;
            foreach ($lastMonthCharges->data as $charge) {
                if ($charge->status === 'succeeded') {
                    $lastMonthRevenue += $charge->amount / 100;
                }
            }
            
            $percentChange = 0;
            if ($lastMonthRevenue > 0) {
                $percentChange = round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1);
            }
            
            return response()->json([
                'account' => [
                    'id' => $account->id,
                    'business_name' => $account->business_profile->name ?? 'CAS Private Care',
                    'email' => $account->email ?? config('mail.from.address'),
                    'country' => $account->country ?? 'US',
                    'default_currency' => strtoupper($account->default_currency ?? 'usd'),
                    'charges_enabled' => $account->charges_enabled ?? true,
                    'payouts_enabled' => $account->payouts_enabled ?? true,
                ],
                'balance' => [
                    'available' => $availableBalance,
                    'pending' => $pendingBalance,
                    'total' => $availableBalance + $pendingBalance,
                ],
                'bank_account' => $bankAccount,
                'monthly_revenue' => $monthlyRevenue,
                'last_month_revenue' => $lastMonthRevenue,
                'percent_change' => $percentChange,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch company account: ' . $e->getMessage());
            
            return response()->json([
                'account' => [
                    'id' => 'acct_...',
                    'business_name' => 'CAS Private Care',
                    'email' => config('mail.from.address'),
                    'country' => 'US',
                    'default_currency' => 'USD',
                    'charges_enabled' => true,
                    'payouts_enabled' => true,
                ],
                'balance' => [
                    'available' => 0,
                    'pending' => 0,
                    'total' => 0,
                ],
                'bank_account' => null,
                'monthly_revenue' => 0,
                'last_month_revenue' => 0,
                'percent_change' => 0,
                'error' => 'Unable to fetch live data from Stripe',
            ]);
        }
    }

    /**
     * Get recent announcements for admin dashboard
     */
    public function getRecentAnnouncements()
    {
        $announcements = \Cache::get('admin_announcements', []);
        $recent = array_slice(array_reverse($announcements), 0, 5);
        
        return response()->json(['announcements' => $recent]);
    }

    /**
     * Send announcement and store in cache
     */
    public function sendAnnouncement(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,error',
            'recipients' => 'required|in:all,caregivers,clients',
            'priority' => 'required|in:low,normal,high,urgent'
        ]);
        
        $announcements = \Cache::get('admin_announcements', []);
        $announcements[] = [
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => ucfirst($validated['type']),
            'recipients' => ucfirst($validated['recipients']) . ' Users',
            'priority' => $validated['priority'],
            'sent_at' => now()->format('M d, Y h:i A'),
        ];
        
        if (count($announcements) > 50) {
            $announcements = array_slice($announcements, -50);
        }
        
        \Cache::put('admin_announcements', $announcements, now()->addDays(30));
        
        return response()->json(['success' => true, 'message' => 'Announcement sent successfully']);
    }

    /**
     * Get caregiver salaries for admin dashboard
     */
    public function getCaregiverSalaries()
    {
        $caregivers = Caregiver::with('user')->get();
        
        $payments = $caregivers->map(function($caregiver) {
            $timeTrackings = \App\Models\TimeTracking::where('caregiver_id', $caregiver->id)
                ->whereMonth('work_date', now()->month)
                ->whereYear('work_date', now()->year)
                ->orderBy('work_date', 'desc')
                ->get();
            
            $totalHours = $timeTrackings->sum('hours_worked');
            $totalEarnings = $timeTrackings->sum('caregiver_earnings');
            $rate = $totalHours > 0 ? ($totalEarnings / $totalHours) : 28;
            
            $unpaidRecords = $timeTrackings->whereNull('paid_at');
            $unpaidHours = $unpaidRecords->sum('hours_worked');
            $unpaidAmount = $unpaidRecords->sum('caregiver_earnings');
            
            if ($totalHours == 0) {
                $status = 'No Hours';
            } elseif ($unpaidHours == 0) {
                $status = 'Paid';
            } elseif ($unpaidHours == $totalHours) {
                $status = 'Pending';
            } else {
                $status = 'Partial';
            }
            
            $bankConnected = !empty($caregiver->user->stripe_connect_id);
            
            return [
                'id' => $caregiver->id,
                'caregiver' => $caregiver->user->name ?? 'Unknown',
                'caregiver_email' => $caregiver->user->email ?? '',
                'total_hours' => round($totalHours, 2),
                'hours_display' => number_format($totalHours, 1) . ' hrs',
                'rate' => '$' . number_format($rate, 2) . '/hr',
                'total_amount' => $totalEarnings,
                'amount_display' => '$' . number_format($totalEarnings, 2),
                'unpaid_hours' => round($unpaidHours, 2),
                'unpaid_amount' => $unpaidAmount,
                'unpaid_display' => '$' . number_format($unpaidAmount, 2),
                'period' => now()->format('M Y'),
                'status' => $status,
                'bank_connected' => $bankConnected,
                'bank_status' => $bankConnected ? 'Connected' : 'Not Connected',
                'days_worked' => $timeTrackings->count(),
                'stripe_connect_id' => $caregiver->user->stripe_connect_id,
                'can_pay' => $bankConnected && $unpaidAmount > 0
            ];
        })->filter(function($payment) {
            return $payment['total_hours'] > 0;
        })->values();
        
        return response()->json(['payments' => $payments]);
    }

    /**
     * Get housekeeper salaries for admin dashboard
     */
    public function getHousekeeperSalaries()
    {
        $housekeepers = Housekeeper::with('user')->get();

        $payments = $housekeepers->map(function($housekeeper) {
            $timeTrackings = \App\Models\TimeTracking::where('housekeeper_id', $housekeeper->id)
                ->whereMonth('work_date', now()->month)
                ->whereYear('work_date', now()->year)
                ->orderBy('work_date', 'desc')
                ->get();

            $totalHours = $timeTrackings->sum('hours_worked');
            $totalEarnings = $timeTrackings->sum('caregiver_earnings');

            $rate = $totalHours > 0
                ? ($totalEarnings / $totalHours)
                : ($housekeeper->hourly_rate ?? 25);

            $unpaidRecords = $timeTrackings->whereNull('paid_at');
            $unpaidHours = $unpaidRecords->sum('hours_worked');
            $unpaidAmount = $unpaidRecords->sum('caregiver_earnings');

            if ($totalHours == 0) {
                $status = 'No Hours';
            } elseif ($unpaidHours == 0) {
                $status = 'Paid';
            } elseif ($unpaidHours == $totalHours) {
                $status = 'Pending';
            } else {
                $status = 'Partial';
            }

            $bankConnected = !empty($housekeeper->user->stripe_connect_id);

            return [
                'id' => $housekeeper->id,
                'housekeeper' => $housekeeper->user->name ?? 'Unknown',
                'housekeeper_email' => $housekeeper->user->email ?? '',
                'total_hours' => round($totalHours, 2),
                'hours_display' => number_format($totalHours, 1) . ' hrs',
                'rate' => '$' . number_format($rate, 2) . '/hr',
                'total_amount' => $totalEarnings,
                'amount_display' => '$' . number_format($totalEarnings, 2),
                'unpaid_hours' => round($unpaidHours, 2),
                'unpaid_amount' => $unpaidAmount,
                'unpaid_display' => '$' . number_format($unpaidAmount, 2),
                'period' => now()->format('M Y'),
                'status' => $status,
                'bank_connected' => $bankConnected,
                'bank_status' => $bankConnected ? 'Connected' : 'Not Connected',
                'days_worked' => $timeTrackings->count(),
                'stripe_connect_id' => $housekeeper->user->stripe_connect_id,
                'can_pay' => $bankConnected && $unpaidAmount > 0
            ];
        })->filter(function($payment) {
            return $payment['total_hours'] > 0;
        })->values();

        return response()->json(['payments' => $payments]);
    }

    /**
     * Process payment to caregiver
     */
    public function payCaregiver(Request $request)
    {
        try {
            $validated = $request->validate([
                'caregiver_id' => 'required|exists:caregivers,id',
                'amount' => 'required|numeric|min:0.01'
            ]);

            return DB::transaction(function () use ($validated) {
                $caregiver = Caregiver::with('user')->findOrFail($validated['caregiver_id']);
                
                if (empty($caregiver->user->stripe_connect_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Caregiver has not connected their bank account'
                    ], 400);
                }

                $unpaidRecords = \App\Models\TimeTracking::where('caregiver_id', $caregiver->id)
                    ->whereNull('paid_at')
                    ->lockForUpdate()
                    ->get();

                if ($unpaidRecords->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No unpaid hours found for this caregiver'
                    ], 400);
                }

                $unpaidAmount = $unpaidRecords->sum('caregiver_earnings');
                
                if (abs($unpaidAmount - $validated['amount']) > 0.01) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment amount does not match unpaid earnings'
                    ], 400);
                }

                $stripe = new \Stripe\StripeClient(config('stripe.secret'));
                
                $idempotencyKey = 'caregiver_payout_' . $caregiver->id . '_' . $unpaidRecords->pluck('id')->implode('_');
                
                $payout = $stripe->transfers->create([
                    'amount' => intval($validated['amount'] * 100),
                    'currency' => 'usd',
                    'destination' => $caregiver->user->stripe_connect_id,
                    'description' => "Payment for " . $unpaidRecords->count() . " work sessions",
                    'metadata' => [
                        'caregiver_id' => $caregiver->id,
                        'record_ids' => $unpaidRecords->pluck('id')->implode(','),
                        'payment_type' => 'caregiver_earnings'
                    ]
                ], [
                    'idempotency_key' => $idempotencyKey
                ]);
                
                Log::info('Stripe transfer created', [
                    'transfer_id' => $payout->id,
                    'amount' => $validated['amount'],
                    'destination' => $caregiver->user->stripe_connect_id,
                    'idempotency_key' => $idempotencyKey
                ]);

                $unpaidRecords->each(function($record) use ($payout) {
                    $record->update([
                        'paid_at' => now(),
                        'payment_status' => 'paid',
                        'stripe_transfer_id' => $payout->id
                    ]);
                });

                Log::info('Caregiver payment processed', [
                    'caregiver_id' => $caregiver->id,
                    'caregiver_name' => $caregiver->user->name,
                    'amount' => $validated['amount'],
                    'records_paid' => $unpaidRecords->count(),
                    'stripe_connect_id' => $caregiver->user->stripe_connect_id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'amount' => $validated['amount'],
                    'caregiver' => $caregiver->user->name,
                    'records_paid' => $unpaidRecords->count(),
                    'transfer_id' => $payout->id
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Caregiver payment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment to housekeeper
     */
    public function payHousekeeper(Request $request)
    {
        try {
            $validated = $request->validate([
                'housekeeper_id' => 'required|exists:housekeepers,id',
                'amount' => 'required|numeric|min:0.01'
            ]);

            return DB::transaction(function () use ($validated) {
                $housekeeper = Housekeeper::with('user')->findOrFail($validated['housekeeper_id']);
                
                if (empty($housekeeper->user->stripe_connect_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Housekeeper has not connected their bank account'
                    ], 400);
                }

                $unpaidRecords = \App\Models\TimeTracking::where('housekeeper_id', $housekeeper->id)
                    ->whereNull('paid_at')
                    ->lockForUpdate()
                    ->get();

                if ($unpaidRecords->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No unpaid hours found for this housekeeper'
                    ], 400);
                }

                $unpaidAmount = $unpaidRecords->sum('caregiver_earnings');
                
                if (abs($unpaidAmount - $validated['amount']) > 0.01) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment amount does not match unpaid earnings'
                    ], 400);
                }

                $stripe = new \Stripe\StripeClient(config('stripe.secret'));
                
                $idempotencyKey = 'housekeeper_payout_' . $housekeeper->id . '_' . $unpaidRecords->pluck('id')->implode('_');
                
                $payout = $stripe->transfers->create([
                    'amount' => intval($validated['amount'] * 100),
                    'currency' => 'usd',
                    'destination' => $housekeeper->user->stripe_connect_id,
                    'description' => "Payment for " . $unpaidRecords->count() . " work sessions",
                    'metadata' => [
                        'housekeeper_id' => $housekeeper->id,
                        'record_ids' => $unpaidRecords->pluck('id')->implode(','),
                        'payment_type' => 'housekeeper_earnings'
                    ]
                ], [
                    'idempotency_key' => $idempotencyKey
                ]);
                
                Log::info('Stripe transfer created for housekeeper', [
                    'transfer_id' => $payout->id,
                    'amount' => $validated['amount'],
                    'destination' => $housekeeper->user->stripe_connect_id,
                    'idempotency_key' => $idempotencyKey
                ]);

                $unpaidRecords->each(function($record) use ($payout) {
                    $record->update([
                        'paid_at' => now(),
                        'payment_status' => 'paid',
                        'stripe_transfer_id' => $payout->id
                    ]);
                });

                Log::info('Housekeeper payment processed', [
                    'housekeeper_id' => $housekeeper->id,
                    'housekeeper_name' => $housekeeper->user->name,
                    'amount' => $validated['amount'],
                    'records_paid' => $unpaidRecords->count(),
                    'stripe_connect_id' => $housekeeper->user->stripe_connect_id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'amount' => $validated['amount'],
                    'housekeeper' => $housekeeper->user->name,
                    'records_paid' => $unpaidRecords->count(),
                    'transfer_id' => $payout->id
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Housekeeper payment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get marketing commissions for admin dashboard
     */
    public function getMarketingCommissions()
    {
        if (Schema::hasTable('time_trackings') && !Schema::hasColumn('time_trackings', 'marketing_commission_paid_at')) {
            Log::warning('Missing time_trackings.marketing_commission_paid_at. Returning empty marketing commissions.');
            return response()->json(['commissions' => []]);
        }

        $marketingStaff = User::where('user_type', 'marketing')
            ->with('referralCode')
            ->get();
        
        $commissions = $marketingStaff->map(function($user) {
            $totalCommission = \App\Models\TimeTracking::where('marketing_partner_id', $user->id)
                ->sum('marketing_partner_commission');
            
            $pendingCommission = \App\Models\TimeTracking::where('marketing_partner_id', $user->id)
                ->whereNull('marketing_commission_paid_at')
                ->sum('marketing_partner_commission');
            
            $paidCommission = $totalCommission - $pendingCommission;
            
            $referralCode = $user->referralCode ? $user->referralCode->code : 'N/A';
            
            // Active clients (approved/confirmed/completed) for tier
            $clientsReferred = MarketingTierService::getActiveClientCountForUser($user->id);
            $tierData = MarketingTierService::getTierAndRateForUser($user->id);
            
            $bankConnected = !empty($user->stripe_connect_id);
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'referral_code' => $referralCode,
                'clients_referred' => $clientsReferred,
                'tier' => $tierData['tier'],
                'tier_label' => $tierData['label'],
                'commission_per_hour' => (float) $tierData['rate'],
                'total_commission' => $totalCommission,
                'pending_commission' => $pendingCommission,
                'paid_commission' => $paidCommission,
                'total_display' => '$' . number_format($totalCommission, 2),
                'pending_display' => '$' . number_format($pendingCommission, 2),
                'bank_connected' => $bankConnected,
                'bank_status' => $bankConnected ? 'Connected' : 'Not Connected',
                'payment_status' => $pendingCommission > 0 ? 'Pending' : 'Paid',
                'stripe_connect_id' => $user->stripe_connect_id,
                'can_pay' => $bankConnected && $pendingCommission > 0
            ];
        })->filter(function($commission) {
            return $commission['total_commission'] > 0;
        })->values();
        
        return response()->json(['commissions' => $commissions]);
    }

    /**
     * Pay marketing commission to staff member
     */
    public function payMarketingCommission($userId)
    {
        return DB::transaction(function () use ($userId) {
            $user = User::findOrFail($userId);
            
            $pendingRecords = \App\Models\TimeTracking::where('marketing_partner_id', $userId)
                ->whereNull('marketing_commission_paid_at')
                ->lockForUpdate()
                ->get();
            
            $pendingCommission = $pendingRecords->sum('marketing_partner_commission');
            
            if ($pendingCommission <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending commission to pay'
                ], 400);
            }
            
            if (empty($user->stripe_connect_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bank account not connected. Please ask the marketing staff to connect their bank account first.'
                ], 400);
            }
            
            \Stripe\Stripe::setApiKey(config('stripe.secret'));
            
            $idempotencyKey = 'marketing_commission_' . $userId . '_' . $pendingRecords->pluck('id')->implode('_');
            
            $transfer = \Stripe\Transfer::create([
                'amount' => (int)($pendingCommission * 100),
                'currency' => 'usd',
                'destination' => $user->stripe_connect_id,
                'description' => "Marketing commission payment for " . $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                    'user_type' => 'marketing',
                    'commission_amount' => $pendingCommission,
                    'record_count' => $pendingRecords->count()
                ]
            ], [
                'idempotency_key' => $idempotencyKey
            ]);
            
            \App\Models\TimeTracking::whereIn('id', $pendingRecords->pluck('id'))
                ->update([
                    'marketing_commission_paid_at' => now(),
                    'marketing_commission_stripe_transfer_id' => $transfer->id
                ]);
            
            Log::info('Marketing commission payment processed', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'amount' => $pendingCommission,
                'records_paid' => $pendingRecords->count(),
                'transfer_id' => $transfer->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Commission paid successfully',
                'transfer_id' => $transfer->id,
                'amount' => $pendingCommission
            ]);
        });
    }

    /**
     * Get training center commissions for admin dashboard
     */
    public function getTrainingCommissions()
    {
        $trainingCenters = User::where('user_type', 'training')
            ->get();
        
        $commissions = $trainingCenters->map(function($user) {
            $totalCommission = \App\Models\TimeTracking::where('training_center_user_id', $user->id)
                ->sum('training_center_commission');
            
            $pendingCommission = \App\Models\TimeTracking::where('training_center_user_id', $user->id)
                ->where('training_paid', 0)
                ->sum('training_center_commission');
            
            $paidCommission = $totalCommission - $pendingCommission;
            
            $caregiversTrained = Caregiver::where('training_center_id', $user->id)->count();
            
            $bankConnected = !empty($user->stripe_connect_id);
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'center_name' => $user->business_name ?? $user->name,
                'caregivers_trained' => $caregiversTrained,
                'total_commission' => $totalCommission,
                'pending_commission' => $pendingCommission,
                'paid_commission' => $paidCommission,
                'total_display' => '$' . number_format($totalCommission, 2),
                'pending_display' => '$' . number_format($pendingCommission, 2),
                'bank_connected' => $bankConnected,
                'bank_status' => $bankConnected ? 'Connected' : 'Not Connected',
                'payment_status' => $pendingCommission > 0 ? 'Pending' : 'Paid',
                'stripe_connect_id' => $user->stripe_connect_id,
                'can_pay' => $bankConnected && $pendingCommission > 0
            ];
        })->filter(function($commission) {
            return $commission['total_commission'] > 0;
        })->values();
        
        return response()->json(['commissions' => $commissions]);
    }

    /**
     * Pay training center commission
     */
    public function payTrainingCommission($userId)
    {
        return DB::transaction(function () use ($userId) {
            $user = User::findOrFail($userId);
            
            $pendingRecords = \App\Models\TimeTracking::where('training_center_id', $userId)
                ->whereNull('training_commission_paid_at')
                ->lockForUpdate()
                ->get();
            
            $pendingCommission = $pendingRecords->sum('training_center_commission');
            
            if ($pendingCommission <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending commission to pay'
                ], 400);
            }
            
            if (empty($user->stripe_connect_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bank account not connected. Please ask the training center to connect their bank account first.'
                ], 400);
            }
            
            \Stripe\Stripe::setApiKey(config('stripe.secret'));
            
            $idempotencyKey = 'training_commission_' . $userId . '_' . $pendingRecords->pluck('id')->implode('_');
            
            $transfer = \Stripe\Transfer::create([
                'amount' => (int)($pendingCommission * 100),
                'currency' => 'usd',
                'destination' => $user->stripe_connect_id,
                'description' => "Training center commission payment for " . $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                    'user_type' => 'training',
                    'commission_amount' => $pendingCommission,
                    'record_count' => $pendingRecords->count()
                ]
            ], [
                'idempotency_key' => $idempotencyKey
            ]);
            
            \App\Models\TimeTracking::whereIn('id', $pendingRecords->pluck('id'))
                ->update([
                    'training_commission_paid_at' => now(),
                    'training_commission_stripe_transfer_id' => $transfer->id
                ]);
            
            Log::info('Training commission payment processed', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'amount' => $pendingCommission,
                'records_paid' => $pendingRecords->count(),
                'transfer_id' => $transfer->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Commission paid successfully',
                'transfer_id' => $transfer->id,
                'amount' => $pendingCommission
            ]);
        });
    }

    /**
     * Get top performing caregivers for admin dashboard
     */
    public function getTopPerformers()
    {
        $caregivers = Caregiver::with('user')
            ->orderBy('rating', 'desc')
            ->take(10)
            ->get()
            ->map(function($caregiver) {
                $rating = $caregiver->rating ?? 0;
                $color = $rating >= 4.5 ? 'success' : ($rating >= 4.0 ? 'info' : 'warning');
                return [
                    'id' => $caregiver->id,
                    'name' => $caregiver->user->name ?? 'Unknown',
                    'rating' => number_format($rating, 1),
                    'color' => $color,
                    'reviews' => $caregiver->total_reviews ?? 0
                ];
            });
        
        return response()->json(['performers' => $caregivers]);
    }

    /**
     * Get recent activity for admin dashboard
     */
    public function getRecentActivity()
    {
        $activities = collect();
        
        // Recent bookings
        $recentBookings = Booking::with('client')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($b) {
                return [
                    'id' => 'booking-' . $b->id,
                    'text' => 'New booking from ' . ($b->client->name ?? 'client'),
                    'time' => $b->created_at->diffForHumans(),
                    'icon' => 'mdi-calendar-plus',
                    'color' => 'success'
                ];
            });
        $activities = $activities->merge($recentBookings);
        
        // Recent users
        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function($u) {
                return [
                    'id' => 'user-' . $u->id,
                    'text' => ucfirst($u->user_type) . ' registered: ' . $u->name,
                    'time' => $u->created_at->diffForHumans(),
                    'icon' => 'mdi-account-plus',
                    'color' => $u->user_type === 'caregiver' ? 'info' : 'primary'
                ];
            });
        $activities = $activities->merge($recentUsers);
        
        return response()->json(['activities' => $activities->sortByDesc('time')->take(10)->values()]);
    }

    /**
     * Extract hours from duty type string
     */
    protected function extractHours($dutyType)
    {
        if (preg_match('/(\d+)\s*Hours?/i', $dutyType, $matches)) {
            return (int)$matches[1];
        }
        return 8;
    }
}
