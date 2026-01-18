<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $lastmod = date('Y-m-d');
        
        $urls = [
            // Main pages
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => url('/services'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => url('/about'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => url('/contact'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => url('/blog'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => url('/faq'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            
            // Service pages
            ['loc' => url('/hire-caregiver-new-york'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => url('/housekeeping-new-york'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => url('/personal-assistant-new-york'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => url('/housekeeping-personal-assistant'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => url('/book-service'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            
            // Training and contractor pages
            ['loc' => url('/training-center'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => url('/contractor-partner'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            
            // Auth pages (for SEO visibility)
            ['loc' => url('/login'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => url('/register'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            
            // Legal pages
            ['loc' => url('/privacy'), 'priority' => '0.5', 'changefreq' => 'yearly'],
            ['loc' => url('/terms'), 'priority' => '0.5', 'changefreq' => 'yearly'],
        ];
        
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $sitemap .= '    <url>' . "\n";
            $sitemap .= '        <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
            $sitemap .= '        <lastmod>' . $lastmod . '</lastmod>' . "\n";
            $sitemap .= '        <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $sitemap .= '        <priority>' . $url['priority'] . '</priority>' . "\n";
            $sitemap .= '    </url>' . "\n";
        }
        
        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'text/xml');
    }
}

