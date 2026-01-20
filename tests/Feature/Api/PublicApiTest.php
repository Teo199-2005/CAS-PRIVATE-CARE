<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function health_endpoint_returns_healthy()
    {
        $response = $this->get('/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'healthy'
            ]);
    }

    /** @test */
    public function health_detailed_returns_component_status()
    {
        $response = $this->get('/health/detailed');

        // Health check should return 200 (healthy) or 503 (degraded) - both are valid
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 503]),
            'Expected status 200 or 503, got ' . $response->getStatusCode()
        );
        
        $response->assertJsonStructure([
            'status',
            'checks'
        ]);
    }

    /** @test */
    public function health_ready_endpoint_works()
    {
        $response = $this->get('/health/ready');

        $response->assertStatus(200);
    }

    /** @test */
    public function health_live_endpoint_works()
    {
        $response = $this->get('/health/live');

        $response->assertStatus(200);
    }

    /** @test */
    public function sitemap_returns_xml()
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        // Accept both text/xml and application/xml
        $contentType = $response->headers->get('Content-Type');
        $this->assertTrue(str_contains($contentType, 'xml'), 'Content-Type should contain xml');
    }

    /** @test */
    public function landing_page_loads()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /** @test */
    public function services_page_loads()
    {
        $response = $this->get('/services');

        $response->assertStatus(200);
    }

    /** @test */
    public function contact_page_loads()
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
    }

    /** @test */
    public function about_page_loads()
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
    }

    /** @test */
    public function faq_page_loads()
    {
        $response = $this->get('/faq');

        $response->assertStatus(200);
    }

    /** @test */
    public function blog_page_loads()
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);
    }

    /** @test */
    public function login_page_loads()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /** @test */
    public function register_page_loads()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /** @test */
    public function zipcode_lookup_works()
    {
        $response = $this->getJson('/api/zipcode-lookup/10001');

        $response->assertStatus(200);
    }

    /** @test */
    public function location_data_endpoint_works()
    {
        $response = $this->getJson('/api/location-data');

        $response->assertStatus(200);
    }

    /** @test */
    public function check_email_exists_works()
    {
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $response = $this->getJson('/api/check-email-exists/existing@example.com');

        $response->assertStatus(200)
            ->assertJson(['exists' => true]);

        $response2 = $this->getJson('/api/check-email-exists/nonexistent@example.com');

        $response2->assertStatus(200)
            ->assertJson(['exists' => false]);
    }

    /** @test */
    public function landing_stats_returns_data()
    {
        $response = $this->getJson('/api/landing/stats');

        $response->assertStatus(200);
    }

    /** @test */
    public function seo_pages_have_meta_tags()
    {
        $pages = ['/', '/services', '/about', '/contact'];

        foreach ($pages as $page) {
            $response = $this->get($page);
            
            if ($response->status() !== 200) {
                continue;
            }
            
            $content = $response->getContent();

            // Check for title tag (required for SEO)
            $this->assertStringContainsString('<title>', $content, "Page {$page} should have a title tag");
        }
    }

    /** @test */
    public function caregiver_new_york_page_loads()
    {
        $response = $this->get('/caregiver-new-york');

        $response->assertStatus(200);
    }

    /** @test */
    public function housekeeper_new_york_page_loads()
    {
        $response = $this->get('/housekeeper-new-york');

        $response->assertStatus(200);
    }

    /** @test */
    public function privacy_page_loads()
    {
        $response = $this->get('/privacy');

        $response->assertStatus(200);
    }

    /** @test */
    public function terms_page_loads()
    {
        $response = $this->get('/terms');

        $response->assertStatus(200);
    }

    /** @test */
    public function contractors_page_loads()
    {
        $response = $this->get('/contractors');

        $response->assertStatus(200);
    }

    /** @test */
    public function contact_form_submission_works()
    {
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '6462828282',
            'message' => 'This is a test message from the contact form.'
        ]);

        // Should redirect or return success
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    /** @test */
    public function contact_form_validates_email()
    {
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'message' => 'Test message'
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function robots_txt_accessible()
    {
        // robots.txt is served by web server directly from public/, not Laravel
        // Verify the file exists instead of testing HTTP access
        $this->assertFileExists(public_path('robots.txt'));
    }
}
