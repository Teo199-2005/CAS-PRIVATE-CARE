<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;
use App\Helpers\DateHelper;
use Carbon\Carbon;

/**
 * Test cases for DateHelper utility class
 */
class DateHelperTest extends TestCase
{
    /**
     * Test basic date formatting
     */
    public function test_format_creates_readable_date(): void
    {
        $date = '2026-01-27';
        $this->assertEquals('Jan 27, 2026', DateHelper::format($date));
    }

    /**
     * Test custom date format
     */
    public function test_format_with_custom_format(): void
    {
        $date = '2026-01-27';
        $this->assertEquals('01/27/2026', DateHelper::format($date, 'm/d/Y'));
        $this->assertEquals('27-01-2026', DateHelper::format($date, 'd-m-Y'));
    }

    /**
     * Test empty date handling
     */
    public function test_format_handles_empty_date(): void
    {
        $this->assertEquals('', DateHelper::format(''));
        $this->assertEquals('', DateHelper::format(null));
    }

    /**
     * Test datetime formatting
     */
    public function test_format_datetime(): void
    {
        $datetime = '2026-01-27 14:30:00';
        $this->assertEquals('Jan 27, 2026 2:30 PM', DateHelper::formatDateTime($datetime));
    }

    /**
     * Test time formatting
     */
    public function test_format_time(): void
    {
        $time = '14:30:00';
        $this->assertEquals('2:30 PM', DateHelper::formatTime($time));
    }

    /**
     * Test human readable diff
     */
    public function test_diff_for_humans(): void
    {
        $pastDate = Carbon::now()->subHours(2);
        $result = DateHelper::diffForHumans($pastDate);
        $this->assertStringContainsString('ago', $result);
    }

    /**
     * Test duration formatting in minutes
     */
    public function test_format_duration_minutes(): void
    {
        $this->assertEquals('30 min', DateHelper::formatDuration(30));
        $this->assertEquals('45 min', DateHelper::formatDuration(45));
    }

    /**
     * Test duration formatting in hours
     */
    public function test_format_duration_hours(): void
    {
        $this->assertEquals('1 hr', DateHelper::formatDuration(60));
        $this->assertEquals('2 hr', DateHelper::formatDuration(120));
        $this->assertEquals('1 hr 30 min', DateHelper::formatDuration(90));
        $this->assertEquals('2 hr 15 min', DateHelper::formatDuration(135));
    }

    /**
     * Test hours formatting
     */
    public function test_format_hours(): void
    {
        $this->assertEquals('1.5 hours', DateHelper::formatHours(90));
        $this->assertEquals('2.0 hours', DateHelper::formatHours(120));
    }

    /**
     * Test day of week
     */
    public function test_day_of_week(): void
    {
        $monday = '2026-01-26'; // This is a Monday
        $this->assertEquals('Monday', DateHelper::dayOfWeek($monday));
    }

    /**
     * Test isToday
     */
    public function test_is_today(): void
    {
        $this->assertTrue(DateHelper::isToday(Carbon::now()));
        $this->assertFalse(DateHelper::isToday(Carbon::yesterday()));
    }

    /**
     * Test isPast
     */
    public function test_is_past(): void
    {
        $this->assertTrue(DateHelper::isPast(Carbon::yesterday()));
        $this->assertFalse(DateHelper::isPast(Carbon::tomorrow()));
    }

    /**
     * Test isFuture
     */
    public function test_is_future(): void
    {
        $this->assertTrue(DateHelper::isFuture(Carbon::tomorrow()));
        $this->assertFalse(DateHelper::isFuture(Carbon::yesterday()));
    }

    /**
     * Test age calculation
     */
    public function test_age(): void
    {
        $birthdate = Carbon::now()->subYears(30);
        $this->assertEquals(30, DateHelper::age($birthdate));
    }

    /**
     * Test age with null birthdate
     */
    public function test_age_handles_null(): void
    {
        $this->assertNull(DateHelper::age(null));
        $this->assertNull(DateHelper::age(''));
    }

    /**
     * Test date range formatting - same day
     */
    public function test_format_range_same_day(): void
    {
        $start = '2026-01-27';
        $end = '2026-01-27';
        $this->assertEquals('Jan 27, 2026', DateHelper::formatRange($start, $end));
    }

    /**
     * Test date range formatting - same month
     */
    public function test_format_range_same_month(): void
    {
        $start = '2026-01-20';
        $end = '2026-01-27';
        $this->assertEquals('Jan 20 - 27, 2026', DateHelper::formatRange($start, $end));
    }

    /**
     * Test week bounds
     */
    public function test_week_bounds(): void
    {
        $bounds = DateHelper::weekBounds('2026-01-27');
        $this->assertArrayHasKey('start', $bounds);
        $this->assertArrayHasKey('end', $bounds);
        $this->assertInstanceOf(Carbon::class, $bounds['start']);
        $this->assertInstanceOf(Carbon::class, $bounds['end']);
    }

    /**
     * Test month bounds
     */
    public function test_month_bounds(): void
    {
        $bounds = DateHelper::monthBounds('2026-01-15');
        $this->assertEquals(1, $bounds['start']->day);
        $this->assertEquals(31, $bounds['end']->day); // January has 31 days
    }

    /**
     * Test ISO 8601 formatting
     */
    public function test_to_iso8601(): void
    {
        $date = '2026-01-27 14:30:00';
        $result = DateHelper::toIso8601($date);
        $this->assertStringContainsString('2026-01-27', $result);
        $this->assertStringContainsString('T', $result); // ISO format has T separator
    }

    /**
     * Test now with timezone
     */
    public function test_now(): void
    {
        $now = DateHelper::now();
        $this->assertInstanceOf(Carbon::class, $now);
        
        $nowNY = DateHelper::now('America/New_York');
        $this->assertEquals('America/New_York', $nowNY->timezoneName);
    }
}
