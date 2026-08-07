const CACHE = 'sullam-static-v210';
const STATIC_ASSETS = new Set([
  '/offline.html',
  '/css/app.css',
  '/css/app-v203.css',
  '/css/app-v204.css',
  '/css/app-v210.css',
  '/css/public.css',
  '/js/app.js',
  '/js/academy-player.js',
  '/js/public.js',
  '/icon.svg',
  '/brand/logo-mark.svg',
  '/brand/logo-horizontal.svg',
  '/brand/logo-horizontal-light.svg'
]);

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(caches.open(CACHE).then(cache => cache.addAll([...STATIC_ASSETS])));
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

  // Authenticated pages and private files must never enter Cache Storage.
  if (url.pathname.startsWith('/media/') || request.headers.has('authorization')) return;

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
