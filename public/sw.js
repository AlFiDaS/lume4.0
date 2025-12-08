// 🚀 SERVICE WORKER OPTIMIZADO - Lume 2.0
const CACHE_NAME = 'lume-2.1.0-2025-09-03T22-17-43';
const STATIC_CACHE = 'lume-static-2.1.0-2025-09-03T22-17-43';
const DYNAMIC_CACHE = 'lume-dynamic-2.1.0-2025-09-03T22-17-43';

// 📱 ESTRATEGIAS DE CACHE
const STATIC_ASSETS = [
  '/',
  '/global.css',
  '/js/cart.js',
  '/js/carrito.js',
  '/js/slider.js',
  '/js/search.js',
  '/js/souvenir-search.js',
  '/favicon.svg',
  '/images/lume-logo.png',
  '/images/lume-logo-blanco.png',
  '/images/hero.webp',
  '/images/hero2.webp',
  '/images/hero3.webp'
];

// 🎯 INSTALACIÓN DEL SW
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => {
        console.log('🔄 Cacheando assets estáticos...');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => {
        console.log('✅ Service Worker instalado correctamente');
        return self.skipWaiting();
      })
  );
});

// 🔄 ACTIVACIÓN DEL SW
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
            console.log('🗑️ Eliminando cache obsoleto:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      console.log('🚀 Service Worker activado');
      return self.clients.claim();
    })
  );
});

// 🌐 ESTRATEGIA DE CACHE: Cache First para estáticos, Network First para dinámicos
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // 🖼️ IMÁGENES: Cache First con fallback a red
  if (request.destination === 'image') {
    event.respondWith(
      caches.match(request)
        .then(response => {
          if (response) {
            return response;
          }
          return fetch(request).then(response => {
            if (response.status === 200) {
              const responseClone = response.clone();
              caches.open(DYNAMIC_CACHE).then(cache => {
                cache.put(request, responseClone);
              });
            }
            return response;
          });
        })
    );
    return;
  }

  // 📄 PÁGINAS: Network First con fallback a cache
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then(response => {
          if (response.status === 200) {
            const responseClone = response.clone();
            caches.open(DYNAMIC_CACHE).then(cache => {
              cache.put(request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          return caches.match(request);
        })
    );
    return;
  }

  // 🎨 CSS/JS: Cache First
  if (request.destination === 'style' || request.destination === 'script') {
    event.respondWith(
      caches.match(request)
        .then(response => {
          if (response) {
            return response;
          }
          return fetch(request);
        })
    );
    return;
  }

  // 🌐 API: Network First
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(
      fetch(request)
        .then(response => {
          if (response.status === 200) {
            const responseClone = response.clone();
            caches.open(DYNAMIC_CACHE).then(cache => {
              cache.put(request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          return caches.match(request);
        })
    );
    return;
  }

  // 🔄 FALLBACK: Cache First para todo lo demás
  event.respondWith(
    caches.match(request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(request);
      })
  );
});

// 📊 MÉTRICAS DE PERFORMANCE
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

// 🚀 OFFLINE SUPPORT
self.addEventListener('fetch', (event) => {
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => {
        return caches.match('/offline.html');
      })
    );
  }
});
