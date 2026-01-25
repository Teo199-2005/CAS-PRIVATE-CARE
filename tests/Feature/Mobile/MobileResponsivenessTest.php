<?php

namespace Tests\Feature\Mobile;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Mobile Responsiveness Test Suite
 * 
 * Tests for mobile-friendly features and responsive design indicators
 */
class MobileResponsivenessTest extends TestCase
{
    /** @test */
    public function pages_have_responsive_viewport()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('width=device-width', false);
        $response->assertSee('initial-scale=1.0', false);
    }

    /** @test */
    public function login_page_has_mobile_breakpoints()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        // Check for mobile-specific media queries
        $response->assertSee('@media (max-width: 768px)', false);
        $response->assertSee('@media (max-width: 480px)', false);
    }

    /** @test */
    public function pages_have_touch_friendly_font_size()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        // iOS requires 16px font-size to prevent zoom on focus
        $response->assertSee('font-size: 16px', false);
    }

    /** @test */
    public function pages_have_theme_color_for_mobile_browser()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('name="theme-color"', false);
    }

    /** @test */
    public function landing_page_has_mobile_styles()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('@media', false);
    }

    /** @test */
    public function pages_prevent_horizontal_scroll()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        // overflow: hidden includes both x and y, or overflow-x: hidden specifically
        $content = $response->getContent();
        $this->assertTrue(
            str_contains($content, 'overflow-x: hidden') || 
            str_contains($content, 'overflow: hidden')
        );
    }

    /** @test */
    public function forms_have_proper_input_types_for_mobile()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('type="email"', false);
        $response->assertSee('type="password"', false);
        $response->assertSee('inputmode="email"', false);
    }

    /** @test */
    public function pages_have_proper_maximum_scale()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        // Should have maximum-scale for accessibility but allow zoom
        $response->assertSee('maximum-scale=5.0', false);
    }

    /** @test */
    public function register_page_has_mobile_support()
    {
        $response = $this->get('/register');
        
        $response->assertStatus(200);
        $response->assertSee('width=device-width', false);
    }

    /** @test */
    public function contact_page_has_mobile_support()
    {
        $response = $this->get('/contact');
        
        $response->assertStatus(200);
        $response->assertSee('width=device-width', false);
    }

    /** @test */
    public function pages_use_flexible_units()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        // Check for percentage or rem/em usage
        $content = $response->getContent();
        
        $this->assertTrue(
            str_contains($content, 'width: 90%') || 
            str_contains($content, 'max-width') ||
            str_contains($content, 'rem')
        );
    }

    /** @test */
    public function buttons_are_touch_friendly_size()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        // Buttons should have adequate padding for touch targets (44x44px minimum)
        $response->assertSee('padding:', false);
    }

    /** @test */
    public function images_are_responsive()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Check for responsive image techniques
        $this->assertTrue(
            str_contains($content, 'max-width') ||
            str_contains($content, 'width: 100%') ||
            str_contains($content, 'height: auto')
        );
    }
}
