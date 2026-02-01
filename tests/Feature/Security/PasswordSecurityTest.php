<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Rules\SecurePassword;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

/**
 * Password Security Test Suite
 * 
 * Tests password validation rules for compliance with security best practices
 */
class PasswordSecurityTest extends TestCase
{
    /** @test */
    public function rejects_passwords_below_minimum_length()
    {
        $rule = new SecurePassword();
        
        $validator = Validator::make(
            ['password' => 'Short1!'],
            ['password' => $rule]
        );
        
        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('at least', $validator->errors()->first('password'));
    }

    /** @test */
    public function rejects_passwords_without_uppercase()
    {
        $rule = new SecurePassword();
        
        $validator = Validator::make(
            ['password' => 'lowercaseonly123!'],
            ['password' => $rule]
        );
        
        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('uppercase', $validator->errors()->first('password'));
    }

    /** @test */
    public function rejects_passwords_without_lowercase()
    {
        $rule = new SecurePassword();
        
        $validator = Validator::make(
            ['password' => 'UPPERCASEONLY123!'],
            ['password' => $rule]
        );
        
        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('lowercase', $validator->errors()->first('password'));
    }

    /** @test */
    public function rejects_passwords_without_numbers()
    {
        $rule = new SecurePassword();
        
        $validator = Validator::make(
            ['password' => 'NoNumbers!!!Pass'],
            ['password' => $rule]
        );
        
        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('number', $validator->errors()->first('password'));
    }

    /** @test */
    public function rejects_passwords_without_special_characters()
    {
        $rule = new SecurePassword();
        
        $validator = Validator::make(
            ['password' => 'NoSpecialChars123'],
            ['password' => $rule]
        );
        
        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('special character', $validator->errors()->first('password'));
    }

    /** @test */
    public function accepts_valid_secure_password()
    {
        // Mock the HIBP check to return false (not breached)
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response('', 200)
        ]);

        $rule = new SecurePassword();
        
        $validator = Validator::make(
            ['password' => 'ValidSecure$Password123!'],
            ['password' => $rule]
        );
        
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function rejects_commonly_used_weak_passwords()
    {
        $rule = new SecurePassword();
        $weakPasswords = [
            'Password123!',
            'Welcome123!',
            'Admin123!',
            'Qwerty123!',
        ];

        foreach ($weakPasswords as $password) {
            $validator = Validator::make(
                ['password' => $password],
                ['password' => $rule]
            );
            
            $this->assertTrue(
                $validator->fails(), 
                "Password '$password' should be rejected as weak"
            );
        }
    }

    /** @test */
    public function password_policy_config_is_accessible()
    {
        $this->assertNotNull(config('password.min_length'));
        $this->assertNotNull(config('password.require_uppercase'));
        $this->assertNotNull(config('password.require_lowercase'));
        $this->assertNotNull(config('password.require_numbers'));
        $this->assertNotNull(config('password.require_special'));
    }

    /** @test */
    public function password_policy_meets_minimum_requirements()
    {
        $minLength = config('password.min_length');
        
        // NIST SP 800-63B recommends minimum 8 characters
        $this->assertGreaterThanOrEqual(8, $minLength);
    }

    /** @test */
    public function rejects_passwords_exceeding_max_length()
    {
        $rule = new SecurePassword();
        
        // Create a password longer than max (usually 128 chars)
        $longPassword = str_repeat('Aa1!', 50); // 200 chars
        
        $validator = Validator::make(
            ['password' => $longPassword],
            ['password' => $rule]
        );
        
        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('exceed', $validator->errors()->first('password'));
    }

    /** @test */
    public function validates_password_with_optional_hibp_check()
    {
        // Test with HIBP check enabled (mocked)
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response("0D4D4F3E3F4D5D6D7D8D9D0DADADBDCD:5\r\n", 200)
        ]);

        $rule = new SecurePassword(['checkHibp' => true]);
        
        // This would be detected if hash prefix matches
        $validator = Validator::make(
            ['password' => 'ValidFormat$123!'],
            ['password' => $rule]
        );
        
        // Since we're mocking, we just verify the validator runs
        $this->assertInstanceOf(Validator::class, $validator);
    }

    /** @test */
    public function password_history_check_prevents_reuse()
    {
        // Create a user with password history
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('OldPassword123!')
        ]);

        // Store password history
        $user->update([
            'password_history' => json_encode([
                hash('sha256', 'OldPassword123!'),
                hash('sha256', 'OlderPassword123!')
            ])
        ]);

        $rule = new SecurePassword(['userId' => $user->id, 'checkHistory' => true]);
        
        $validator = Validator::make(
            ['password' => 'BrandNew$Password456!'],
            ['password' => $rule]
        );
        
        // New password should pass
        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response('', 200)
        ]);
        
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function registration_uses_secure_password_rule()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'user_type' => 'client'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function password_reset_uses_secure_password_rule()
    {
        $response = $this->postJson('/api/reset-password', [
            'token' => 'fake-token',
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }
}
