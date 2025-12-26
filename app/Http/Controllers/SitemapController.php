<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>' . url('/') . '</loc>
        <lastmod>' . date('Y-m-d') . '</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>' . url('/register') . '</loc>
        <lastmod>' . date('Y-m-d') . '</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'text/xml');
    }
}

