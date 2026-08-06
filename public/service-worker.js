const CACHE='sullam-static-v120';
const ASSETS=['/css/app.css','/js/app.js','/icon.svg','/brand/logo-mark.svg','/brand/logo-horizontal.svg','/brand/logo-horizontal-light.svg'];
self.addEventListener('install',event=>event.waitUntil(caches.open(CACHE).then(cache=>cache.addAll(ASSETS))));
self.addEventListener('activate',event=>event.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(key=>key!==CACHE).map(key=>caches.delete(key))))));
self.addEventListener('fetch',event=>{if(event.request.method!=='GET')return;if(new URL(event.request.url).origin!==location.origin)return;event.respondWith(fetch(event.request).catch(()=>caches.match(event.request)));});
