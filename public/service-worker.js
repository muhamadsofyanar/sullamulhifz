const CACHE='sullam-static-v130';
const ASSETS=['/css/app.css','/css/public.css','/js/app.js','/js/public.js','/icon.svg','/brand/logo-mark.svg','/brand/logo-horizontal.svg','/brand/logo-horizontal-light.svg'];
self.addEventListener('install',event=>{self.skipWaiting();event.waitUntil(caches.open(CACHE).then(cache=>cache.addAll(ASSETS)))});
self.addEventListener('activate',event=>event.waitUntil(Promise.all([self.clients.claim(),caches.keys().then(keys=>Promise.all(keys.filter(key=>key!==CACHE).map(key=>caches.delete(key))))])));
self.addEventListener('fetch',event=>{if(event.request.method!=='GET')return;const url=new URL(event.request.url);if(url.origin!==location.origin)return;if(event.request.mode==='navigate'){event.respondWith(fetch(event.request));return;}event.respondWith(fetch(event.request).catch(()=>caches.match(event.request)));});
