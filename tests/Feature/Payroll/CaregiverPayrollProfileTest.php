<?php

declare(strict_types=1);

namespace Tests\Feature\Payroll;

use App\Models\Caregiver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaregiverPayrollProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function caregiver_can_read_and_update_payroll_profile(): void
    {
        $user = User::factory()->create(['user_type' => 'caregiver']);
        Caregiver::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $this->getJson('/api/caregiver/payroll-profile')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['profile']);

        $payload = [
            'legal_first_name' => 'Jane',
            'legal_last_name' => 'Doe',
            'ssn' => '123456789',
            'date_of_birth' => '1990-01-15',
            'address_line1' => '1 Main St',
            'city' => 'New York',
            'region' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
            'bank_routing_number' => '021000021',
            'bank_account_number' => '9876543210',
            'bank_account_type' => 'checking',
            'emergency_contact_name' => 'John Doe',
            'emergency_contact_phone' => '5551234567',
            'emergency_contact_relationship' => 'Spouse',
            'mark_complete' => true,
        ];

        $put = $this->putJson('/api/caregiver/payroll-profile', $payload);
        $put->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('profile.ssn_masked', '***-**-6789');
        $this->assertNotNull($put->json('profile.profile_completed_at'));
    }

    /** @test */
    public function non_caregiver_cannot_access_payroll_profile(): void
    {
        $client = User::factory()->create(['user_type' => 'client']);
        $this->actingAs($client);

        $this->getJson('/api/caregiver/payroll-profile')->assertStatus(403);
    }
}
