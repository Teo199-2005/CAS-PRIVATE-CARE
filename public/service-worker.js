/**
 * CAS Private Care - Service Worker
 * 
 * Provides offline functionality, caching, and performance improvements.
 * 
 * @version 2.1.1
 * @since 2026-01-24
 */

const CACHE_NAME = 'cas-private-care-v2.1.1';
const STATIC_CACHE = 'cas-static-v2.1.1';
const DYNAMIC_CACHE = 'cas-dynamic-v2.1.1';
const API_CACHE = 'cas-api-v2.1.1';

// Files to cache immediately on install
const PRECACHE_ASSETS = [
    '/',
    '/offline',
    '/css/app.css',
    '/js/app.js',
    '/images/logo.png',
    '/images/CAS logo.png',
    '/manifest.json',
    // Fonts
    '/fonts/nunito-v25-latin-regular.woff2',
    '/fonts/nunito-v25-latin-700.woff2',
];

// API endpoints to cache with network-first strategy
const API_CACHE_PATTERNS = [
    /\/api\/zipcode-lookup\//,
    /\/api\/v1\/health/,
];

// Never cache these patterns
const NO_CACHE_PATTERNS = [
    /\/api\/.*payment/i,
    /\/api\/.*stripe/i,
    /\/api\/.*webhook/i,
    /\/api\/admin\//,
    /sanctum\/csrf-cookie/,
    /login/,
    /logout/,
    /register/,
];

/**
 * Install event - precache static assets
 */
self.addEventListener('install', (event) => {
    console.log('[ServiceWorker] Installing...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('[ServiceWorker] Precaching static assets');
                return cache.addAll(PRECACHE_ASSETS.filter(url => {
                    // Only cache URLs that exist
                    return true;
                })).catch(err => {
                    console.warn('[ServiceWorker] Some precache assets failed:', err);
                });
            })
            .then(() => {
                console.log('[ServiceWorker] Installation complete');
                return self.skipWaiting();
            })
    );
});

/**
 * Activate event - clean up old caches
 */
self.addEventListener('activate', (event) => {
    console.log('[ServiceWorker] Activating...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => {
                            return name.startsWith('cas-') && 
                                   name !== STATIC_CACHE && 
                                   name !== DYNAMIC_CACHE && 
                                   name !== API_CACHE;
                        })
                        .map((name) => {
                            console.log('[ServiceWorker] Deleting old cache:', name);
                            return caches.delete(name);
                        })
                );
            })
            .then(() => {
                console.log('[ServiceWorker] Activation complete');
                return self.clients.claim();
            })
    );
});

/**
 * Fetch event - handle requests with appropriate caching strategy
 */
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip requests to other origins
    if (url.origin !== location.origin) {
        return;
    }
    
    // Skip requests that should never be cached
    if (shouldNotCache(url.pathname)) {
        return;
    }
    
    // API requests - network first, cache fallback
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirstWithCache(request, API_CACHE));
        return;
    }
    
    // Static assets - cache first, network fallback
    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirstWithNetwork(request, STATIC_CACHE));
        return;
    }
    
    // HTML pages - network first, cache fallback, then offline page
    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(networkFirstWithOffline(request));
        return;
    }
    
    // Everything else - stale while revalidate
    event.respondWith(staleWhileRevalidate(request, DYNAMIC_CACHE));
});

/**
 * Check if URL should never be cached
 */
function shouldNotCache(pathname) {
    return NO_CACHE_PATTERNS.some(pattern => pattern.test(pathname));
}

/**
 * Check if URL is a static asset
 */
function isStaticAsset(pathname) {
    return /\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i.test(pathname);
}

/**
 * Cache first, network fallback strategy
 */
async function cacheFirstWithNetwork(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
        // Return cached response immediately
        // Also fetch fresh version in background
        fetchAndCache(request, cacheName);
        return cachedResponse;
    }
    
    return fetchAndCache(request, cacheName);
}

/**
 * Network first, cache fallback strategy
 */
async function networkFirstWithCache(request, cacheName) {
    const cache = await caches.open(cacheName);
    
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        const cachedResponse = await cache.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }
        
        throw error;
    }
}

/**
 * Network first with offline page fallback
 */
async function networkFirstWithOffline(request) {
    const cache = await caches.open(DYNAMIC_CACHE);
    
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        const cachedResponse = await cache.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline page
        const offlineResponse = await caches.match('/offline');
        if (offlineResponse) {
            return offlineResponse;
        }
        
        return new Response('Offline', {
            status: 503,
            statusText: 'Service Unavailable',
            headers: { 'Content-Type': 'text/plain' }
        });
    }
}

/**
 * Stale while revalidate strategy
 */
async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);
    
    const fetchPromise = fetch(request)
        .then((networkResponse) => {
            if (networkResponse.ok) {
                cache.put(request, networkResponse.clone());
            }
            return networkResponse;
        })
        .catch(() => null);
    
    return cachedResponse || fetchPromise;
}

/**
 * Fetch and cache a request
 */
async function fetchAndCache(request, cacheName) {
    try {
        const cache = await caches.open(cacheName);
        const networkResponse = await fetch(request);
        
        if (networkResponse && networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        // Silently handle network failures - return cached response if available
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        // If no cache, return a basic offline response for navigation requests
        if (request.mode === 'navigate') {
            return caches.match('/offline');
        }
        // For other requests, just fail silently
        console.warn('Service Worker: Failed to fetch', request.url);
        return new Response('Network unavailable', { status: 503, statusText: 'Service Unavailable' });
    }
}

/**
 * Handle push notifications
 */
self.addEventListener('push', (event) => {
    const options = {
        body: event.data?.text() || 'New notification from CAS Private Care',
        icon: '/images/logo.png',
        badge: '/images/badge.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            { action: 'view', title: 'View' },
            { action: 'dismiss', title: 'Dismiss' }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('CAS Private Care', options)
    );
});

/**
 * Handle notification clicks
 */
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

/**
 * Handle background sync
 */
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-bookings') {
        event.waitUntil(syncBookings());
    }
});

/**
 * Sync bookings when online
 */
async function syncBookings() {
    // Implementation for syncing offline bookings
    console.log('[ServiceWorker] Syncing bookings...');
}

console.log('[ServiceWorker] Service worker loaded');
