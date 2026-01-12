<?php

namespace Tests\Unit\Validation;

use Tests\TestCase;
use App\Rules\ValidSSN;
use Illuminate\Support\Facades\Validator;

class ValidationRulesTest extends TestCase
{
    /** @test */
    public function valid_ssn_passes_validation()
    {
        $rule = new ValidSSN();
        
        $validator = Validator::make(
            ['ssn' => '123-45-6789'],
            ['ssn' => ['required', $rule]]
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function invalid_ssn_fails_validation()
    {
        $rule = new ValidSSN();
        
        $validator = Validator::make(
            ['ssn' => '000-00-0000'],
            ['ssn' => ['required', $rule]]
        );

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function ssn_with_wrong_format_fails()
    {
        $rule = new ValidSSN();
        
        $validator = Validator::make(
            ['ssn' => '12345'], // Too short
            ['ssn' => ['required', $rule]]
        );

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function empty_ssn_passes_when_nullable()
    {
        $rule = new ValidSSN();
        
        $validator = Validator::make(
            ['ssn' => ''],
            ['ssn' => ['nullable', $rule]]
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function zip_code_validates_five_digits()
    {
        $validator = Validator::make(
            ['zip' => '10001'],
            ['zip' => ['required', 'regex:/^\d{5}$/']]
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function zip_code_rejects_invalid_format()
    {
        $validator = Validator::make(
            ['zip' => '123'],
            ['zip' => ['required', 'regex:/^\d{5}$/']]
        );

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function email_validates_proper_format()
    {
        $validator = Validator::make(
            ['email' => 'test@example.com'],
            ['email' => ['required', 'email']]
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function email_rejects_invalid_format()
    {
        $validator = Validator::make(
            ['email' => 'invalid-email'],
            ['email' => ['required', 'email']]
        );

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function password_validates_minimum_length()
    {
        $validator = Validator::make(
            ['password' => 'Pass1234'],
            ['password' => ['required', 'min:8']]
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function password_rejects_short_passwords()
    {
        $validator = Validator::make(
            ['password' => 'Pass1'],
            ['password' => ['required', 'min:8']]
        );

        $this->assertTrue($validator->fails());
    }
}
