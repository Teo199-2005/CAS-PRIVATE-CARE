<?php

namespace Tests\Feature\PWA;

use Tests\TestCase;

/**
 * PWA (Progressive Web App) Test Suite
 * 
 * Tests for PWA features including manifest, service worker, and offline support
 */
class PWATest extends TestCase
{
    /** @test */
    public function manifest_json_is_accessible()
    {
        $response = $this->get('/manifest.json');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    public function manifest_has_required_properties()
    {
        $response = $this->get('/manifest.json');
        
        $response->assertStatus(200);
        
        $manifest = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('name', $manifest);
        $this->assertArrayHasKey('short_name', $manifest);
        $this->assertArrayHasKey('start_url', $manifest);
        $this->assertArrayHasKey('display', $manifest);
        $this->assertArrayHasKey('theme_color', $manifest);
        $this->assertArrayHasKey('background_color', $manifest);
        $this->assertArrayHasKey('icons', $manifest);
    }

    /** @test */
    public function manifest_has_valid_icons()
    {
        $response = $this->get('/manifest.json');
        
        $manifest = json_decode($response->getContent(), true);
        
        $this->assertNotEmpty($manifest['icons']);
        
        foreach ($manifest['icons'] as $icon) {
            $this->assertArrayHasKey('src', $icon);
            $this->assertArrayHasKey('sizes', $icon);
            $this->assertArrayHasKey('type', $icon);
        }
    }

    /** @test */
    public function service_worker_is_accessible()
    {
        $response = $this->get('/sw.js');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function offline_page_is_accessible()
    {
        $response = $this->get('/offline.html');
        
        $response->assertStatus(200);
        $response->assertSee("You're Offline", false);
    }

    /** @test */
    public function landing_page_has_manifest_link()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('rel="manifest"', false);
    }

    /** @test */
    public function landing_page_has_theme_color()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('name="theme-color"', false);
    }

    /** @test */
    public function landing_page_has_apple_touch_icon()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('rel="apple-touch-icon"', false);
    }

    /** @test */
    public function landing_page_registers_service_worker()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee("serviceWorker.register('/sw.js')", false);
    }
}
