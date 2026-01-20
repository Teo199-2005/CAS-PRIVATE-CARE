{{-- CAS Private Care - SEO & Structured Data Component --}}
{{-- Usage: @include('components.seo-structured-data', ['type' => 'organization']) --}}

@php
$baseUrl = config('app.url', 'https://casprivatecare.com');
$logo = asset('logo.png');
$phone = '+1-646-282-8282';
@endphp

{{-- Organization Schema --}}
@if(!isset($type) || $type === 'organization')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "HomeHealthCareService",
    "@id": "{{ $baseUrl }}/#organization",
    "name": "CAS Private Care LLC",
    "alternateName": "CAS Private Care",
    "url": "{{ $baseUrl }}",
    "logo": {
        "@type": "ImageObject",
        "url": "{{ $logo }}",
        "width": 200,
        "height": 200
    },
    "image": "{{ $logo }}",
    "description": "Professional home care services including verified caregivers, housekeeping, and personal assistants in New York.",
    "telephone": "{{ $phone }}",
    "email": "contact@casprivatecare.com",
    "foundingDate": "2020",
    "priceRange": "$$",
    "currenciesAccepted": "USD",
    "paymentAccepted": "Credit Card, Debit Card, Bank Transfer",
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "New York",
        "addressLocality": "New York",
        "addressRegion": "NY",
        "postalCode": "10001",
        "addressCountry": "US"
    },
    "geo": {
        "@type": "GeoCoordinates",
        "latitude": 40.7128,
        "longitude": -74.0060
    },
    "areaServed": [
        {
            "@type": "City",
            "name": "New York"
        },
        {
            "@type": "State",
            "name": "New York"
        }
    ],
    "hasOfferCatalog": {
        "@type": "OfferCatalog",
        "name": "Home Care Services",
        "itemListElement": [
            {
                "@type": "Offer",
                "itemOffered": {
                    "@type": "Service",
                    "name": "Caregiver Services",
                    "description": "Professional, verified caregivers for elderly care, companion care, and personal assistance."
                }
            },
            {
                "@type": "Offer",
                "itemOffered": {
                    "@type": "Service",
                    "name": "Housekeeping Services",
                    "description": "Professional housekeeping and home maintenance services."
                }
            },
            {
                "@type": "Offer",
                "itemOffered": {
                    "@type": "Service",
                    "name": "Personal Assistant Services",
                    "description": "Professional personal assistants for daily tasks and errands."
                }
            }
        ]
    },
    "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
        "opens": "00:00",
        "closes": "23:59"
    },
    "sameAs": [
        "https://www.facebook.com/casprivatecare",
        "https://www.instagram.com/casprivatecare",
        "https://www.linkedin.com/company/casprivatecare",
        "https://twitter.com/casprivatecare"
    ],
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.9",
        "reviewCount": "500",
        "bestRating": "5",
        "worstRating": "1"
    }
}
</script>
@endif

{{-- Website Schema --}}
@if(!isset($type) || $type === 'website')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "@id": "{{ $baseUrl }}/#website",
    "name": "CAS Private Care",
    "url": "{{ $baseUrl }}",
    "description": "Connect with verified caregivers and home helpers in New York.",
    "publisher": {
        "@id": "{{ $baseUrl }}/#organization"
    },
    "potentialAction": {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": "{{ $baseUrl }}/search?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
    }
}
</script>
@endif

{{-- Breadcrumb Schema --}}
@if(isset($breadcrumbs) && count($breadcrumbs) > 0)
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        @foreach($breadcrumbs as $index => $crumb)
        {
            "@type": "ListItem",
            "position": {{ $index + 1 }},
            "name": "{{ $crumb['name'] }}",
            "item": "{{ $crumb['url'] }}"
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endif

{{-- Service Schema --}}
@if(isset($type) && $type === 'service' && isset($service))
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "{{ $service['name'] ?? 'Caregiver Service' }}",
    "description": "{{ $service['description'] ?? 'Professional home care services' }}",
    "provider": {
        "@id": "{{ $baseUrl }}/#organization"
    },
    "serviceType": "{{ $service['type'] ?? 'Home Care' }}",
    "areaServed": {
        "@type": "City",
        "name": "New York"
    },
    "offers": {
        "@type": "Offer",
        "priceCurrency": "USD",
        "price": "{{ $service['price'] ?? '25' }}",
        "priceSpecification": {
            "@type": "UnitPriceSpecification",
            "price": "{{ $service['price'] ?? '25' }}",
            "priceCurrency": "USD",
            "unitCode": "HUR"
        }
    }
}
</script>
@endif

{{-- FAQ Schema --}}
@if(isset($type) && $type === 'faq' && isset($faqs))
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        @foreach($faqs as $index => $faq)
        {
            "@type": "Question",
            "name": "{{ $faq['question'] }}",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "{{ $faq['answer'] }}"
            }
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endif

{{-- Blog/Article Schema --}}
@if(isset($type) && $type === 'article' && isset($article))
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $article['title'] ?? 'Blog Post' }}",
    "description": "{{ $article['excerpt'] ?? '' }}",
    "image": "{{ $article['image'] ?? $logo }}",
    "datePublished": "{{ $article['published_at'] ?? now()->toIso8601String() }}",
    "dateModified": "{{ $article['updated_at'] ?? now()->toIso8601String() }}",
    "author": {
        "@type": "Organization",
        "@id": "{{ $baseUrl }}/#organization"
    },
    "publisher": {
        "@type": "Organization",
        "@id": "{{ $baseUrl }}/#organization"
    },
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ url()->current() }}"
    }
}
</script>
@endif

{{-- Review Schema --}}
@if(isset($type) && $type === 'review' && isset($reviews))
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "@id": "{{ $baseUrl }}/#organization",
    "name": "CAS Private Care LLC",
    "review": [
        @foreach($reviews as $review)
        {
            "@type": "Review",
            "author": {
                "@type": "Person",
                "name": "{{ $review['author'] ?? 'Client' }}"
            },
            "reviewRating": {
                "@type": "Rating",
                "ratingValue": "{{ $review['rating'] ?? 5 }}",
                "bestRating": "5"
            },
            "reviewBody": "{{ $review['body'] ?? '' }}"
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endif

{{-- Contact Page Schema --}}
@if(isset($type) && $type === 'contact')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ContactPage",
    "name": "Contact CAS Private Care",
    "description": "Get in touch with CAS Private Care for home care services in New York.",
    "url": "{{ $baseUrl }}/contact",
    "mainEntity": {
        "@type": "Organization",
        "@id": "{{ $baseUrl }}/#organization"
    }
}
</script>
@endif

{{-- How-To Schema for Booking --}}
@if(isset($type) && $type === 'howto')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "HowTo",
    "name": "How to Book a Caregiver with CAS Private Care",
    "description": "Step-by-step guide to booking professional home care services.",
    "step": [
        {
            "@type": "HowToStep",
            "position": 1,
            "name": "Create an Account",
            "text": "Register for a free account on CAS Private Care."
        },
        {
            "@type": "HowToStep",
            "position": 2,
            "name": "Browse Caregivers",
            "text": "Browse our verified caregivers and their profiles."
        },
        {
            "@type": "HowToStep",
            "position": 3,
            "name": "Book Your Service",
            "text": "Select your caregiver and book your service dates and times."
        },
        {
            "@type": "HowToStep",
            "position": 4,
            "name": "Confirm Payment",
            "text": "Complete your booking with secure payment."
        }
    ],
    "totalTime": "PT10M"
}
</script>
@endif
