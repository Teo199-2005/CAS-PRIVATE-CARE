<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_as_client()
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '(212) 555-0123',
            'zip_code' => '10001',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'client',
            'terms' => true
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'user_type' => 'client'
        ]);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function registration_validates_required_fields()
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'email',
            'phone',
            'zip_code',
            'password',
            'user_type',
            'terms'
        ]);
    }

    /** @test */
    public function registration_validates_email_format()
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'phone' => '(212) 555-0123',
            'zip_code' => '10001',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'client',
            'terms' => true
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function registration_validates_unique_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'phone' => '(212) 555-0123',
            'zip_code' => '10001',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'client',
            'terms' => true
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function registration_validates_password_confirmation()
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '(212) 555-0123',
            'zip_code' => '10001',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
            'user_type' => 'client',
            'terms' => true
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function registration_validates_zip_code_format()
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '(212) 555-0123',
            'zip_code' => '123', // Invalid - must be 5 digits
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'client',
            'terms' => true
        ]);

        $response->assertSessionHasErrors('zip_code');
    }

    /** @test */
    public function registration_requires_terms_acceptance()
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '(212) 555-0123',
            'zip_code' => '10001',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'client',
            'terms' => false
        ]);

        $response->assertSessionHasErrors('terms');
    }

    /** @test */
    public function password_is_hashed_when_stored()
    {
        $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '(212) 555-0123',
            'zip_code' => '10001',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'client',
            'terms' => true
        ]);

        $user = User::where('email', 'john@example.com')->first();
        
        $this->assertNotEquals('Password123!', $user->password);
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    /** @test */
    public function caregiver_registration_creates_caregiver_record()
    {
        $this->post('/register', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '(212) 555-0124',
            'zip_code' => '10002',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'caregiver',
            'terms' => true
        ]);

        $user = User::where('email', 'jane@example.com')->first();
        
        $this->assertDatabaseHas('caregivers', [
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function client_registration_creates_client_record()
    {
        $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '(212) 555-0123',
            'zip_code' => '10001',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'client',
            'terms' => true
        ]);

        $user = User::where('email', 'john@example.com')->first();
        
        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
    }
}
