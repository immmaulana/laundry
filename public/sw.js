/* CashierLaundry PWA service worker */
const CACHE = 'cashierlaundry-v1';
const PRECACHE = [
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png',
    '/brand/cashierlaundry-logo.png',
    '/manifest.webmanifest',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))),
        ).then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Navigasi / Inertia: network-first
    if (request.mode === 'navigate' || request.headers.get('Accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((response) => response)
                .catch(async () => {
                    const cached = await caches.match(request);
                    return cached || caches.match('/offline.html');
                }),
        );
        return;
    }

    // Asset statis: cache-first
    if (
        url.pathname.startsWith('/icons/')
        || url.pathname.startsWith('/brand/')
        || url.pathname.startsWith('/build/')
        || url.pathname.endsWith('.css')
        || url.pathname.endsWith('.js')
        || url.pathname.endsWith('.woff2')
    ) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const fetched = fetch(request)
                    .then((response) => {
                        if (response && response.ok) {
                            const clone = response.clone();
                            caches.open(CACHE).then((cache) => cache.put(request, clone));
                        }
                        return response;
                    })
                    .catch(() => cached);
                return cached || fetched;
            }),
        );
    }
});
