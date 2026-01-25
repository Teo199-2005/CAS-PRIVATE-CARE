<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * BookingFlowBrowserTest
 * 
 * End-to-end browser tests for the booking creation flow.
 * 
 * Why it matters:
 * - Tests real user interactions with JavaScript-dependent UI
 * - Catches issues that PHPUnit tests miss (Vue reactivity, modal interactions)
 * - Validates accessibility (keyboard navigation, focus management)
 * 
 * Prerequisites:
 * - Install Dusk: composer require laravel/dusk --dev
 * - Install driver: php artisan dusk:install
 * - Run: php artisan dusk
 */
class BookingFlowBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected User $clientUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client user for testing
        $this->clientUser = User::factory()->create([
            'email' => 'browser-test-client@example.com',
            'password' => bcrypt('password123'),
            'user_type' => 'client',
            'status' => 'Active',
            'email_verified_at' => now(),
        ]);

        Client::factory()->create([
            'user_id' => $this->clientUser->id,
        ]);
    }

    /**
     * Test client can login and see dashboard
     */
    public function test_client_can_login_and_view_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->waitFor('#email') // Wait for form to load
                ->type('#email', $this->clientUser->email)
                ->type('#password', 'password123')
                ->press('Sign In')
                ->waitForLocation('/client-dashboard')
                ->assertSee('Dashboard')
                ->assertSee($this->clientUser->name);
        });
    }

    /**
     * Test dashboard loads all sections
     */
    public function test_dashboard_sections_load_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->clientUser)
                ->visit('/client-dashboard')
                ->waitFor('.v-app') // Wait for Vue to mount
                ->waitForText('Quick Actions', 10)
                ->assertSee('My Bookings')
                ->assertSee('Payment History');
        });
    }

    /**
     * Test booking modal can be opened
     */
    public function test_can_open_new_booking_modal(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->clientUser)
                ->visit('/client-dashboard')
                ->waitFor('.v-app')
                ->click('@new-booking-button') // Using Dusk selector
                ->waitFor('.v-dialog--active', 5)
                ->assertSee('Create New Booking');
        });
    }

    /**
     * Test booking form validation
     */
    public function test_booking_form_shows_validation_errors(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->clientUser)
                ->visit('/client-dashboard')
                ->waitFor('.v-app')
                ->click('@new-booking-button')
                ->waitFor('.v-dialog--active')
                // Try to submit empty form
                ->click('@submit-booking-button')
                // Should show validation errors
                ->waitForText('required', 5)
                ->assertPresent('.v-messages__message');
        });
    }

    /**
     * Test keyboard navigation for accessibility
     */
    public function test_keyboard_navigation_works(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->clientUser)
                ->visit('/client-dashboard')
                ->waitFor('.v-app')
                // Press Tab to navigate
                ->keys('body', '{tab}')
                // Skip link should be visible on focus
                ->assertVisible('.skip-link:focus')
                // Continue tabbing through navigation
                ->keys('body', '{tab}')
                ->keys('body', '{tab}')
                // Should reach interactive elements
                ->assertNotNull($browser->driver->switchTo()->activeElement());
        });
    }

    /**
     * Test mobile responsive layout
     */
    public function test_mobile_layout_displays_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 812) // iPhone X dimensions
                ->loginAs($this->clientUser)
                ->visit('/client-dashboard')
                ->waitFor('.v-app')
                // Mobile navigation should be visible
                ->assertPresent('.mobile-app-bar')
                // Bottom navigation should be visible
                ->assertPresent('.v-bottom-navigation')
                // Drawer should be hidden by default
                ->assertMissing('.v-navigation-drawer--active');
        });
    }

    /**
     * Test mobile menu toggle
     */
    public function test_mobile_menu_toggles(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 812)
                ->loginAs($this->clientUser)
                ->visit('/client-dashboard')
                ->waitFor('.v-app')
                // Click menu button
                ->click('.mobile-menu-btn, .v-app-bar__nav-icon')
                ->waitFor('.v-navigation-drawer--active', 5)
                ->assertVisible('.v-navigation-drawer');
        });
    }

    /**
     * Test loading state displays correctly
     */
    public function test_loading_overlay_displays(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->clientUser)
                ->visit('/client-dashboard')
                // Loading overlay should appear initially
                ->assertPresent('.loading-overlay, #loading-overlay')
                // Wait for it to disappear
                ->waitUntilMissing('.loading-overlay', 10);
        });
    }

    /**
     * Test logout functionality
     */
    public function test_user_can_logout(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->clientUser)
                ->visit('/client-dashboard')
                ->waitFor('.v-app')
                ->click('@logout-button')
                ->waitForLocation('/login')
                ->assertPathIs('/login');
        });
    }

    /**
     * Test session timeout redirect
     */
    public function test_expired_session_redirects_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            // Visit dashboard without auth
            $browser->visit('/client-dashboard')
                ->waitForLocation('/login', 5)
                ->assertPathIs('/login');
        });
    }
}
