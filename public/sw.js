/**
 * CAS Private Care - Service Worker
 * 
 * Provides offline support and intelligent caching for improved performance.
 * 
 * Caching Strategies:
 * - Static assets: Cache-first (CSS, JS, images)
 * - API requests: Network-first with fallback
 * - Pages: Network-first with offline fallback
 */

const CACHE_NAME = 'cas-private-care-v1';
const OFFLINE_URL = '/offline.html';

// Assets to cache immediately on install
const PRECACHE_ASSETS = [
    '/',
    '/offline.html',
    '/logo flower.png',
    '/logo.png',
    '/manifest.json',
];

// Assets patterns to cache dynamically
const CACHEABLE_PATTERNS = [
    /\.css$/,
    /\.js$/,
    /\.woff2?$/,
    /\.png$/,
    /\.jpg$/,
    /\.jpeg$/,
    /\.svg$/,
    /\.webp$/,
    /\.ico$/,
];

// URLs that should never be cached
const NEVER_CACHE = [
    '/api/',
    '/login',
    '/logout',
    '/register',
    '/admin/',
    '/client/',
    '/caregiver/',
    '/payment',
    '/stripe',
];

/**
 * Install Event
 * Pre-cache essential assets
 */
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Pre-caching essential assets');
                return cache.addAll(PRECACHE_ASSETS.filter(url => {
                    // Only cache assets that exist
                    return true;
                })).catch(err => {
                    console.log('[SW] Pre-cache failed for some assets:', err);
                });
            })
            .then(() => {
                // Take control immediately
                return self.skipWaiting();
            })
    );
});

/**
 * Activate Event
 * Clean up old caches
 */
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => name !== CACHE_NAME)
                        .map((name) => {
                            console.log('[SW] Deleting old cache:', name);
                            return caches.delete(name);
                        })
                );
            })
            .then(() => {
                // Take control of all clients immediately
                return self.clients.claim();
            })
    );
});

/**
 * Fetch Event
 * Implement caching strategies
 */
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip cross-origin requests (except for CDN assets)
    if (url.origin !== location.origin && !isCDNAsset(url)) {
        return;
    }

    // Skip never-cache URLs
    if (shouldNeverCache(url.pathname)) {
        return;
    }

    // Determine caching strategy
    if (isStaticAsset(url.pathname)) {
        // Cache-first for static assets
        event.respondWith(cacheFirst(request));
    } else if (url.pathname.startsWith('/api/')) {
        // Network-first for API
        event.respondWith(networkFirst(request));
    } else {
        // Network-first with offline fallback for pages
        event.respondWith(networkFirstWithOffline(request));
    }
});

/**
 * Cache-First Strategy
 * Good for static assets that rarely change
 */
async function cacheFirst(request) {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
        // Return cached version, but update cache in background
        updateCache(request);
        return cachedResponse;
    }

    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('[SW] Cache-first fetch failed:', error);
        return new Response('Asset not available offline', { status: 503 });
    }
}

/**
 * Network-First Strategy
 * Good for API requests where fresh data is important
 */
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        return new Response(JSON.stringify({ error: 'Offline' }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

/**
 * Network-First with Offline Fallback
 * Good for HTML pages
 */
async function networkFirstWithOffline(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }

        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            const offlineResponse = await caches.match(OFFLINE_URL);
            if (offlineResponse) {
                return offlineResponse;
            }
        }

        return new Response('You appear to be offline', { status: 503 });
    }
}

/**
 * Update cache in background (stale-while-revalidate)
 */
async function updateCache(request) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse);
        }
    } catch (error) {
        // Silently fail - we already have cached version
    }
}

/**
 * Check if URL is a static asset
 */
function isStaticAsset(pathname) {
    return CACHEABLE_PATTERNS.some(pattern => pattern.test(pathname));
}

/**
 * Check if URL should never be cached
 */
function shouldNeverCache(pathname) {
    return NEVER_CACHE.some(path => pathname.startsWith(path));
}

/**
 * Check if URL is a CDN asset
 */
function isCDNAsset(url) {
    const cdnHosts = [
        'fonts.googleapis.com',
        'fonts.gstatic.com',
        'cdn.jsdelivr.net',
    ];
    return cdnHosts.includes(url.host);
}

/**
 * Message Event
 * Handle messages from the main thread
 */
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        caches.delete(CACHE_NAME).then(() => {
            console.log('[SW] Cache cleared');
        });
    }
});
