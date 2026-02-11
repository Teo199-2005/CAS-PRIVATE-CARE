<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marketing partner tier levels based on active (paid) client count.
 *
 * Silver Partner (Tier 1): 1-5 active clients  → $1.00/hr
 * Gold Partner (Tier 2):   6-10 active clients → $1.25/hr
 * Platinum Partner (Tier 3): 11+ active clients → $1.50/hr
 *
 * "Active clients" = unique clients who have actually paid (proof of contract):
 * - Either at least one time_tracking linked to a booking with this referral code
 *   has stripe_charge_id set (client was charged for that session), OR
 * - At least one booking with this referral code has payment_status = 'paid'.
 * This prevents fake clients from inflating tier (only paid/charged clients count).
 */
class MarketingTierService
{
    public const TIER_SILVER = 'Silver';
    public const TIER_GOLD = 'Gold';
    public const TIER_PLATINUM = 'Platinum';

    public const RATE_SILVER = 1.00;
    public const RATE_GOLD = 1.25;
    public const RATE_PLATINUM = 1.50;

    /** @var array<int, array{tier: string, label: string, rate: float}> */
    protected static array $tierByCount = [
        0  => ['tier' => self::TIER_SILVER, 'label' => 'Silver Partner', 'rate' => self::RATE_SILVER],  // 0 = no commission until 1+ client
        1  => ['tier' => self::TIER_SILVER, 'label' => 'Silver Partner', 'rate' => self::RATE_SILVER],
        2  => ['tier' => self::TIER_SILVER, 'label' => 'Silver Partner', 'rate' => self::RATE_SILVER],
        3  => ['tier' => self::TIER_SILVER, 'label' => 'Silver Partner', 'rate' => self::RATE_SILVER],
        4  => ['tier' => self::TIER_SILVER, 'label' => 'Silver Partner', 'rate' => self::RATE_SILVER],
        5  => ['tier' => self::TIER_SILVER, 'label' => 'Silver Partner', 'rate' => self::RATE_SILVER],
        6  => ['tier' => self::TIER_GOLD, 'label' => 'Gold Partner', 'rate' => self::RATE_GOLD],
        7  => ['tier' => self::TIER_GOLD, 'label' => 'Gold Partner', 'rate' => self::RATE_GOLD],
        8  => ['tier' => self::TIER_GOLD, 'label' => 'Gold Partner', 'rate' => self::RATE_GOLD],
        9  => ['tier' => self::TIER_GOLD, 'label' => 'Gold Partner', 'rate' => self::RATE_GOLD],
        10 => ['tier' => self::TIER_GOLD, 'label' => 'Gold Partner', 'rate' => self::RATE_GOLD],
    ];

    /**
     * Get tier and rate from active client count.
     * 0 clients = Silver tier but rate can be treated as 0 for commission (no clients yet).
     *
     * @return array{tier: string, label: string, rate: float}
     */
    public static function getTierFromActiveClientCount(int $activeClientCount): array
    {
        $activeClientCount = max(0, (int) $activeClientCount);
        if ($activeClientCount >= 11) {
            return [
                'tier' => self::TIER_PLATINUM,
                'label' => 'Platinum Partner',
                'rate' => self::RATE_PLATINUM,
            ];
        }
        return self::$tierByCount[$activeClientCount] ?? [
            'tier' => self::TIER_SILVER,
            'label' => 'Silver Partner',
            'rate' => self::RATE_SILVER,
        ];
    }

    /**
     * Count unique clients who have actually paid (proof of contract).
     * A client counts only if:
     * (1) They have at least one time_tracking (session) linked to a booking with this
     *     referral code where stripe_charge_id is set (client was charged), OR
     * (2) They have at least one booking with this referral code and payment_status = 'paid'.
     * This prevents fake clients from ranking up tiers.
     */
    public static function getActiveClientCountForReferralCode(int $referralCodeId): int
    {
        $referralCodeId = (int) $referralCodeId;
        if ($referralCodeId <= 0) {
            return 0;
        }
        $clientIdsFromChargedSessions = collect();
        // bookings.client_id = users.id; time_trackings.client_id = clients.id — use bookings.client_id for consistent "client user" count
        if (Schema::hasTable('time_trackings') && Schema::hasColumn('time_trackings', 'stripe_charge_id')) {
            $clientIdsFromChargedSessions = DB::table('time_trackings')
                ->join('bookings', 'time_trackings.booking_id', '=', 'bookings.id')
                ->where('bookings.referral_code_id', $referralCodeId)
                ->whereIn('bookings.status', ['approved', 'confirmed', 'completed'])
                ->whereNotNull('time_trackings.stripe_charge_id')
                ->whereNotNull('time_trackings.booking_id')
                ->select('bookings.client_id')
                ->distinct()
                ->pluck('client_id');
        }

        // Only 'paid' — exclude refunded, partially_refunded, failed, etc.
        $clientIdsFromPaidBookings = Booking::where('referral_code_id', $referralCodeId)
            ->whereIn('status', ['approved', 'confirmed', 'completed'])
            ->where('payment_status', 'paid')
            ->distinct()
            ->pluck('client_id');

        // Both sources use bookings.client_id (users.id); merge and remove nulls
        $allPaidClientIds = $clientIdsFromChargedSessions
            ->merge($clientIdsFromPaidBookings)
            ->unique()
            ->filter(fn ($id) => $id !== null && $id !== '');

        return max(0, $allPaidClientIds->count());
    }

    /**
     * Get active client count for a marketing user (via their referral code).
     * Returns 0 if user has no referral code.
     */
    public static function getActiveClientCountForUser(int $userId): int
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return 0;
        }
        $referralCode = ReferralCode::where('user_id', $userId)->first();
        if (!$referralCode) {
            return 0;
        }
        return self::getActiveClientCountForReferralCode($referralCode->id);
    }

    /**
     * Get tier and rate for a referral code (based on its active client count).
     * Use this when calculating commission at clock-out or recalc.
     *
     * @return array{tier: string, label: string, rate: float, active_client_count: int}
     */
    public static function getTierAndRateForReferralCode(int $referralCodeId): array
    {
        $referralCodeId = (int) $referralCodeId;
        if ($referralCodeId <= 0) {
            return [
                'tier' => self::TIER_SILVER,
                'label' => 'Silver Partner',
                'rate' => 0.0,
                'active_client_count' => 0,
            ];
        }
        $count = self::getActiveClientCountForReferralCode($referralCodeId);
        $tierData = self::getTierFromActiveClientCount($count);
        $tierData['active_client_count'] = $count;
        // If 0 active clients, commission rate is 0 (no commission until they have at least 1 client)
        if ($count === 0) {
            $tierData['rate'] = 0.0;
        }
        return $tierData;
    }

    /**
     * Get tier and rate for a marketing user (via their referral code).
     *
     * @return array{tier: string, label: string, rate: float, active_client_count: int}
     */
    public static function getTierAndRateForUser(int $userId): array
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return [
                'tier' => self::TIER_SILVER,
                'label' => 'Silver Partner',
                'rate' => 0.0,
                'active_client_count' => 0,
            ];
        }
        $referralCode = ReferralCode::where('user_id', $userId)->first();
        if (!$referralCode) {
            return [
                'tier' => self::TIER_SILVER,
                'label' => 'Silver Partner',
                'rate' => 0.0,
                'active_client_count' => 0,
            ];
        }
        return self::getTierAndRateForReferralCode($referralCode->id);
    }

    /**
     * Commission rate to use for a given referral code when recording time.
     * Returns 0 if referral code has 0 active clients.
     */
    public static function getCommissionRateForReferralCode(int $referralCodeId): float
    {
        $data = self::getTierAndRateForReferralCode($referralCodeId);
        return (float) $data['rate'];
    }

    /**
     * Commission rate to use for a given marketing user when recording time.
     */
    public static function getCommissionRateForUser(int $userId): float
    {
        $data = self::getTierAndRateForUser($userId);
        return (float) $data['rate'];
    }
}
