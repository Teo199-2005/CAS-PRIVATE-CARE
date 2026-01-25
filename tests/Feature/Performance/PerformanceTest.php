<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Performance Test Suite
 * 
 * Tests for performance optimizations and best practices
 */
class PerformanceTest extends TestCase
{
    /** @test */
    public function login_page_loads_quickly()
    {
        $start = microtime(true);
        $response = $this->get('/login');
        $duration = microtime(true) - $start;
        
        $response->assertStatus(200);
        // Page should load in under 2 seconds
        $this->assertLessThan(2.0, $duration, 'Login page took too long to load');
    }

    /** @test */
    public function landing_page_loads_quickly()
    {
        $start = microtime(true);
        $response = $this->get('/');
        $duration = microtime(true) - $start;
        
        $response->assertStatus(200);
        // Page should load in under 3 seconds (it has more content)
        $this->assertLessThan(3.0, $duration, 'Landing page took too long to load');
    }

    /** @test */
    public function pages_have_preconnect_hints()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('rel="preconnect"', false);
        $response->assertSee('fonts.googleapis.com', false);
    }

    /** @test */
    public function pages_have_dns_prefetch()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('rel="dns-prefetch"', false);
    }

    /** @test */
    public function pages_have_proper_viewport_meta()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('name="viewport"', false);
        $response->assertSee('width=device-width', false);
    }

    /** @test */
    public function landing_page_has_lazy_loading_images()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('loading="lazy"', false);
    }

    /** @test */
    public function pages_have_theme_color_for_mobile()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('name="theme-color"', false);
    }

    /** @test */
    public function no_console_logs_in_production_pages()
    {
        $response = $this->get('/login');
        
        $content = $response->getContent();
        
        // Should not contain console.log (we removed them)
        $this->assertStringNotContainsString('console.log(', $content);
    }

    /** @test */
    public function pages_have_reduced_motion_support()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('prefers-reduced-motion', false);
    }

    /** @test */
    public function api_endpoints_respond_quickly()
    {
        $start = microtime(true);
        $response = $this->get('/api/landing/stats');
        $duration = microtime(true) - $start;
        
        // API should respond in under 500ms
        $this->assertLessThan(0.5, $duration, 'API endpoint too slow');
    }

    /** @test */
    public function health_check_endpoint_responds()
    {
        $response = $this->get('/health');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function pages_have_canonical_urls()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('rel="canonical"', false);
    }
}
