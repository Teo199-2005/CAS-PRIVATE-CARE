<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">

    <!-- Primary Meta Tags -->
    <title>CAS Private Care LLC - Verified Caregivers & Home Care Services New York</title>
    <meta name="title" content="CAS Private Care LLC - Verified Caregivers & Home Care Services New York">
    <meta name="description" content="Connect with verified caregivers, nannies, and home helpers in the New York. Professional elderly care, housekeeping, and personal care services. 24/7 support. Book trusted care professionals today.">
    <meta name="keywords" content="caregivers New York, home care services, elderly care, nanny services, housekeeping, personal care, verified caregivers, Manila caregivers, professional care services">
    <meta name="author" content="CAS Private Care LLC">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="CAS Private Care LLC - Verified Caregivers & Home Care Services New York">
    <meta property="og:description" content="Connect with verified caregivers, nannies, and home helpers in the New York. Professional elderly care, housekeeping, and personal care services.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <meta property="og:site_name" content="CAS Private Care LLC">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="CAS Private Care LLC - Verified Caregivers & Home Care Services New York">
    <meta property="twitter:description" content="Connect with verified caregivers, nannies, and home helpers in the New York. Professional elderly care, housekeeping, and personal care services.">
    <meta property="twitter:image" content="{{ asset('logo.png') }}">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    @php
    echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => 'CAS Private Care LLC',
        'image' => asset('logo.png'),
        '@id' => url('/'),
        'url' => url('/'),
        'telephone' => '+1-646-282-8282',
        'priceRange' => '$$',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'New York',
            'addressRegion' => 'NY',
            'addressCountry' => 'US'
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => 40.7128,
            'longitude' => -74.0060
        ],
        'areaServed' => [
            '@type' => 'City',
            'name' => 'New York'
        ],
        'openingHoursSpecification' => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'opens' => '00:00',
            'closes' => '23:59'
        ],
        'sameAs' => [
            'https://www.facebook.com/casprivatecare',
            'https://www.instagram.com/casprivatecare',
            'https://www.linkedin.com/company/casprivatecare'
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    @endphp
    </script>

    <!-- Google Fonts for the landing page -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body style="margin:0">
    <div id="landing-app">
        <landing-page></landing-page>
    </div>
</body>
</html>
