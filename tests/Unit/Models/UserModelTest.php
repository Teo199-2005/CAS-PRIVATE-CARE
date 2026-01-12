<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Caregiver;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_has_client_relationship()
    {
        $user = User::factory()->create(['user_type' => 'client']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Client::class, $user->client);
        $this->assertEquals($client->id, $user->client->id);
    }

    /** @test */
    public function user_has_caregiver_relationship()
    {
        $user = User::factory()->create(['user_type' => 'caregiver']);
        $caregiver = Caregiver::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Caregiver::class, $user->caregiver);
        $this->assertEquals($caregiver->id, $user->caregiver->id);
    }

    /** @test */
    public function user_has_bookings_relationship()
    {
        $user = User::factory()->create(['user_type' => 'client']);
        
        Booking::factory()->count(3)->create([
            'client_id' => $user->id
        ]);

        $this->assertCount(3, $user->bookings);
    }

    /** @test */
    public function user_type_can_be_client()
    {
        $user = User::factory()->create(['user_type' => 'client']);
        
        $this->assertEquals('client', $user->user_type);
    }

    /** @test */
    public function user_type_can_be_caregiver()
    {
        $user = User::factory()->create(['user_type' => 'caregiver']);
        
        $this->assertEquals('caregiver', $user->user_type);
    }

    /** @test */
    public function user_type_can_be_admin()
    {
        $user = User::factory()->create(['user_type' => 'admin']);
        
        $this->assertEquals('admin', $user->user_type);
    }

    /** @test */
    public function user_email_is_unique()
    {
        User::factory()->create(['email' => 'unique@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['email' => 'unique@example.com']);
    }

    /** @test */
    public function user_password_is_hidden_in_json()
    {
        $user = User::factory()->create();
        
        $json = $user->toArray();
        
        $this->assertArrayNotHasKey('password', $json);
    }
}
