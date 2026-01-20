<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    private $client;
    private $booking;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('stripe.secret', 'sk_test_dummy');
        Config::set('stripe.key', 'pk_test_dummy');
        $this->client = User::factory()->create(['user_type' => 'client', 'stripe_customer_id' => 'cus_test123']);
        Client::factory()->create(['user_id' => $this->client->id]);
        $this->booking = Booking::factory()->create(['client_id' => $this->client->id, 'status' => 'approved', 'total_budget' => 500.00]);
    }

    #[Test]
    public function stripe_routes_exist(): void
    {
        $this->actingAs($this->client);
        $response = $this->getJson('/api/stripe/payment-methods');
        $this->assertNotEquals(404, $response->status());
    }

    #[Test]
    public function unauthenticated_cannot_access_payment_routes(): void
    {
        $response = $this->getJson('/api/stripe/payment-methods');
        $response->assertStatus(401);
    }

    #[Test]
    public function booking_total_budget_is_numeric(): void
    {
        $this->assertIsNumeric($this->booking->total_budget);
        $this->assertEquals(500.00, (float)$this->booking->total_budget);
    }
}
