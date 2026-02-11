<?php

namespace Tests\Feature\Auth;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a fake Socialite user for Google.
     */
    protected function fakeGoogleUser(string $email = 'oauth@example.com', string $name = 'OAuth User'): SocialiteUser
    {
        $user = new SocialiteUser;
        $user->map([
            'id' => 'google-'.md5($email),
            'name' => $name,
            'email' => $email,
            'avatar' => null,
        ]);

        return $user;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function google_redirect_returns_error_when_credentials_not_configured()
    {
        config(['services.google.client_id' => null]);
        config(['services.google.client_secret' => null]);

        $response = $this->get('/auth/google');

        $response->assertRedirect('/register');
        $response->assertSessionHas('error');
        $this->assertStringContainsString('not configured', session('error'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function google_redirect_returns_error_when_placeholder_credentials_used()
    {
        config(['services.google.client_id' => 'your_google_client_id']);
        config(['services.google.client_secret' => 'your_google_client_secret']);

        $response = $this->get('/auth/google');

        $response->assertRedirect('/register');
        $response->assertSessionHas('error');
        $this->assertStringContainsString('not configured', session('error'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function google_redirect_succeeds_when_credentials_configured()
    {
        config([
            'services.google.client_id' => 'real-client-id.apps.googleusercontent.com',
            'services.google.client_secret' => 'real-secret',
        ]);
        Socialite::fake('google');

        $response = $this->get('/auth/google');

        $response->assertRedirect();
        $this->assertStringContainsString('google', $response->headers->get('Location'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function google_callback_logs_in_existing_user_and_redirects_to_dashboard()
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'user_type' => 'client',
            'status' => 'Active',
        ]);
        Socialite::fake('google', $this->fakeGoogleUser('existing@example.com', 'Existing User'));

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/client/dashboard-vue');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function google_callback_creates_new_user_and_client_when_coming_from_login()
    {
        Socialite::fake('google', $this->fakeGoogleUser('newuser@example.com', 'New User'));

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticated();
        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('New User', $user->name);
        $this->assertSame('client', $user->user_type);
        $this->assertNotNull($user->email_verified_at);

        $client = Client::where('user_id', $user->id)->first();
        $this->assertNotNull($client);
        $this->assertSame('New', $client->first_name);
        $this->assertSame('User', $client->last_name);

        $response->assertRedirect('/client/dashboard-vue');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function google_callback_redirects_to_register_with_oauth_data_when_coming_from_registration()
    {
        Socialite::fake('google', $this->fakeGoogleUser('newpartner@example.com', 'New Partner'));
        $this->withSession(['oauth_referrer' => 'http://localhost/register']);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/register');
        $response->assertSessionHas('oauth_success');
        $response->assertSessionHas('oauth_user');
        $this->assertSame('newpartner@example.com', session('oauth_user')['email']);
        $this->assertSame('google', session('oauth_user')['provider']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function google_callback_rejects_rejected_caregiver()
    {
        User::factory()->create([
            'email' => 'rejected@example.com',
            'user_type' => 'caregiver',
            'status' => 'rejected',
        ]);
        Socialite::fake('google', $this->fakeGoogleUser('rejected@example.com', 'Rejected Caregiver'));

        $response = $this->get('/auth/google/callback');

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function google_redirect_passes_partner_query_to_session()
    {
        config([
            'services.google.client_id' => 'real-client-id.apps.googleusercontent.com',
            'services.google.client_secret' => 'real-secret',
        ]);
        Socialite::fake('google');

        $response = $this->get('/auth/google?partner=caregiver');

        $response->assertRedirect();
        $this->assertEquals('caregiver', session('oauth_partner_type'));
    }
}
