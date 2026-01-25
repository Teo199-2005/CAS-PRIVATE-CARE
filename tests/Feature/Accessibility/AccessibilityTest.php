<?php

namespace Tests\Feature\Accessibility;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Accessibility Test Suite
 * 
 * Tests WCAG 2.1 AA compliance indicators for key pages
 */
class AccessibilityTest extends TestCase
{
    /** @test */
    public function login_page_has_skip_link()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('Skip to main content', false);
        $response->assertSee('class="skip-link"', false);
    }

    /** @test */
    public function login_page_has_proper_aria_labels()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('aria-label="Login form"', false);
        $response->assertSee('aria-required="true"', false);
        $response->assertSee('role="main"', false);
    }

    /** @test */
    public function login_page_has_form_labels_associated_with_inputs()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('for="email"', false);
        $response->assertSee('id="email"', false);
        $response->assertSee('for="password"', false);
        $response->assertSee('id="password"', false);
    }

    /** @test */
    public function password_toggle_has_accessibility_attributes()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('aria-label="Show password"', false);
        $response->assertSee('aria-pressed="false"', false);
        $response->assertSee('aria-controls="password"', false);
    }

    /** @test */
    public function modal_has_proper_dialog_attributes()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-modal="true"', false);
        $response->assertSee('aria-labelledby="modal-title"', false);
    }

    /** @test */
    public function alerts_have_proper_role_attributes()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('role="alert"', false);
        $response->assertSee('aria-live="assertive"', false);
    }

    /** @test */
    public function images_have_alt_text()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('alt="CAS Private Care LLC', false);
    }

    /** @test */
    public function page_has_proper_document_structure()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('lang="en"', false);
        $response->assertSee('<main', false);
        $response->assertSee('<header', false);
        $response->assertSee('<footer', false);
    }

    /** @test */
    public function social_login_buttons_have_aria_labels()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        // These may or may not be present depending on OAuth config
        // But if present, they should have aria-labels
        $content = $response->getContent();
        
        if (str_contains($content, '/auth/google')) {
            $this->assertStringContainsString('aria-label="Continue with Google account"', $content);
        }
        
        if (str_contains($content, '/auth/facebook')) {
            $this->assertStringContainsString('aria-label="Continue with Facebook account"', $content);
        }
    }

    /** @test */
    public function register_page_is_accessible()
    {
        $response = $this->get('/register');
        
        $response->assertStatus(200);
        $response->assertSee('lang="en"', false);
    }

    /** @test */
    public function landing_page_has_proper_heading_hierarchy()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('<h1', false);
    }

    /** @test */
    public function contact_page_is_accessible()
    {
        $response = $this->get('/contact');
        
        $response->assertStatus(200);
        $response->assertSee('lang="en"', false);
    }
}
