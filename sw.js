/* Bijouterie Manager PWA — static shell only, private data is network-only. */
'use strict';
const CACHE_NAME = 'bijouterie-manager-pwa-v1-9';
const STATIC_URLS = [
  './offline.html',
  './manifest.json',
  './assets/style.css',
  './assets/app.js',
  './assets/icon.svg',
  './assets/icon-192.png',
  './assets/icon-512.png',
  './assets/icon-maskable-512.png'
];
const STATIC_PATHS = new Set(STATIC_URLS.map(path => new URL(path, self.registration.scope).pathname));

self.addEventListener('install', event => {
  event.waitUntil((async () => {
    const cache = await caches.open(CACHE_NAME);
    await cache.addAll(STATIC_URLS);
    await self.skipWaiting();
  })());
});

self.addEventListener('activate', event => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter(key => key.startsWith('bijouterie-manager-') && key !== CACHE_NAME).map(key => caches.delete(key)));
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', event => {
  const request = event.request;
  if (request.method !== 'GET') return;
  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;

  // PHP routes, login, exports, uploads, photos and all personal data are never cached.
  if (request.mode === 'navigate' || url.pathname.endsWith('.php')) {
    if (request.mode === 'navigate') {
      event.respondWith(fetch(request).catch(async () => {
        const cache = await caches.open(CACHE_NAME);
        return (await cache.match(new URL('./offline.html', self.registration.scope).href)) || Response.error();
      }));
    }
    return;
  }

  // Only a small, explicit list of public static app-shell assets is cacheable.
  if (!STATIC_PATHS.has(url.pathname)) return;
  // Réseau d'abord : les mises à jour de style.css / app.js sont vues immédiatement ; le cache sert hors ligne.
  event.respondWith((async () => {
    const cache = await caches.open(CACHE_NAME);
    try {
      const response = await fetch(request);
      if (response.ok && response.type === 'basic') await cache.put(request, response.clone());
      return response;
    } catch (err) {
      const cached = await cache.match(request, {ignoreSearch: true});
      if (cached) return cached;
      throw err;
    }
  })());
});
