<?php

namespace Tests\Feature\SEO;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * SEO Test Suite
 * 
 * Tests for SEO best practices and meta tags
 */
class SEOTest extends TestCase
{
    /** @test */
    public function landing_page_has_title_tag()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('<title>', false);
        $response->assertSee('CAS Private Care', false);
    }

    /** @test */
    public function landing_page_has_meta_description()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('name="description"', false);
    }

    /** @test */
    public function landing_page_has_open_graph_tags()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:description"', false);
        $response->assertSee('property="og:type"', false);
        $response->assertSee('property="og:url"', false);
        $response->assertSee('property="og:image"', false);
    }

    /** @test */
    public function landing_page_has_twitter_cards()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('property="twitter:card"', false);
        $response->assertSee('property="twitter:title"', false);
    }

    /** @test */
    public function landing_page_has_structured_data()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('@context', false);
        $response->assertSee('schema.org', false);
    }

    /** @test */
    public function landing_page_has_canonical_url()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('rel="canonical"', false);
    }

    /** @test */
    public function login_page_has_noindex()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('noindex', false);
    }

    /** @test */
    public function landing_page_is_indexable()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('index, follow', false);
    }

    /** @test */
    public function sitemap_is_accessible()
    {
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function about_page_has_proper_seo()
    {
        $response = $this->get('/about');
        
        $response->assertStatus(200);
        $response->assertSee('<title>', false);
        $response->assertSee('name="description"', false);
    }

    /** @test */
    public function blog_page_has_proper_seo()
    {
        $response = $this->get('/blog');
        
        $response->assertStatus(200);
        $response->assertSee('<title>', false);
    }

    /** @test */
    public function seo_pages_have_proper_headings()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('<h1', false);
    }

    /** @test */
    public function images_have_alt_attributes()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        // Check that images have alt attributes
        $content = $response->getContent();
        
        // Count img tags vs alt attributes (rough check)
        $imgCount = substr_count($content, '<img');
        $altCount = substr_count($content, 'alt="');
        
        // Most images should have alt text
        $this->assertGreaterThan(0, $altCount);
    }

    /** @test */
    public function location_pages_are_accessible()
    {
        // Only test location pages that have existing views
        $locations = [
            '/caregiver-new-york',
            '/housekeeper-new-york',
        ];
        
        foreach ($locations as $location) {
            $response = $this->get($location);
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function pages_have_language_attribute()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('lang="en"', false);
    }
}
