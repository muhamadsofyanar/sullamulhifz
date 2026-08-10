/* @phase 5.3 Mobile, Offline & Global — offline-safe static shell only */
const CACHE = 'sullam-static-v530';
const STATIC_ASSETS = new Set([
  '/offline.html',
  '/manifest.webmanifest',
  '/academy-manifest.webmanifest',
  '/css/app.css',
  '/css/app-v203.css',
  '/css/app-v204.css',
  '/css/app-v210.css',
  '/css/app-v220.css',
  '/css/app-v230.css',
  '/css/app-v240.css',
  '/css/app-v250.css',
  '/css/app-v300.css',
  '/css/app-v310.css',
  '/css/app-v330.css',
  '/css/app-v340.css',
  '/css/app-v400.css',
  '/css/app-v410.css',
  '/css/app-v440.css',
  '/css/app-v450.css',
  '/css/app-v480.css',
  '/css/app-v490.css',
  '/css/app-v530.css',
  '/css/public.css',
  '/css/public-v450.css',
  '/js/app.js',
  '/js/academy-player.js',
  '/js/academy-quran.js',
  '/js/public.js',
  '/icon.svg',
  '/brand/logo-mark.svg',
  '/brand/logo-horizontal.svg',
  '/brand/logo-horizontal-light.svg'
]);

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE).then(async cache => {
      for (const asset of STATIC_ASSETS) {
        try {
          await cache.add(asset);
        } catch (error) {
          // Satu asset opsional tidak boleh menggagalkan instal PWA seluruhnya.
          console.warn('Sullam PWA asset skipped:', asset);
        }
      }
    })
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(Promise.all([
    self.clients.claim(),
    caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE).map(key => caches.delete(key))))
  ]));
});

self.addEventListener('fetch', event => {
  const request = event.request;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;

  // Guardrail privasi: halaman terautentikasi, media privat, API, dan request
  // dengan Authorization tidak pernah disimpan sebagai data offline.
  if (
    url.pathname.startsWith('/media/') ||
    url.pathname.startsWith('/api/') ||
    request.headers.has('authorization')
  ) return;

  if (request.mode === 'navigate') {
    event.respondWith(fetch(request, { cache: 'no-store' }).catch(() => caches.match('/offline.html')));
    return;
  }

  if (!STATIC_ASSETS.has(url.pathname)) return;

  event.respondWith(
    caches.match(url.pathname).then(cached => cached || fetch(request).then(response => {
      if (response.ok && response.type === 'basic') {
        const copy = response.clone();
        caches.open(CACHE).then(cache => cache.put(url.pathname, copy));
      }
      return response;
    }))
  );
});
