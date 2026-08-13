const CACHE_NAME = 'slid-etc-pwa-v1';
const CORE_ASSETS = [
  '/offline.html',
  '/images/slid-logo.png',
  '/images/coat-of-arms.png'
];

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function (cache) {
        return cache.addAll(CORE_ASSETS);
      })
      .then(function () {
        return self.skipWaiting();
      })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys()
      .then(function (cacheNames) {
        return Promise.all(
          cacheNames
            .filter(function (cacheName) {
              return cacheName !== CACHE_NAME;
            })
            .map(function (cacheName) {
              return caches.delete(cacheName);
            })
        );
      })
      .then(function () {
        return self.clients.claim();
      })
  );
});

self.addEventListener('fetch', function (event) {
  const request = event.request;

  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(function () {
        return caches.match('/offline.html');
      })
    );

    return;
  }

  if (url.origin !== self.location.origin) {
    return;
  }

  if (! ['style', 'script', 'image', 'font'].includes(request.destination)) {
    return;
  }

  event.respondWith(
    caches.match(request).then(function (cachedResponse) {
      if (cachedResponse) {
        return cachedResponse;
      }

      return fetch(request).then(function (networkResponse) {
        if (networkResponse && networkResponse.ok) {
          const responseClone = networkResponse.clone();

          caches.open(CACHE_NAME).then(function (cache) {
            cache.put(request, responseClone);
          });
        }

        return networkResponse;
      });
    })
  );
});
