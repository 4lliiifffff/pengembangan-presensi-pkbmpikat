const CACHE_NAME = 'pikat-presensi-v2';
const ASSETS_TO_CACHE = [
  '/',
  '/manifest.json',
  '/assets/img/Logo.jpeg',
  '/build/manifest.json'
];

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE).catch(() => {
        // Silently continue if some optional asset is missing
      });
    })
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // Hanya handle GET request
  if (event.request.method !== 'GET') {
    return;
  }

  // Network First, fallback to Cache Strategy
  event.respondWith(
    fetch(event.request)
      .then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200) {
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone);
          });
        }
        return networkResponse;
      })
      .catch(() => {
        return caches.match(event.request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }
          if (event.request.headers.get('accept')?.includes('text/html')) {
            return caches.match('/');
          }
        });
      })
  );
});

/* ==========================================
   PUSH NOTIFICATION HANDLERS
   ========================================== */
self.addEventListener('push', (event) => {
  let data = {
    title: 'Presensi PKBM Pikat',
    body: 'Anda memiliki notifikasi baru.',
    icon: '/assets/img/Logo.jpeg',
    badge: '/assets/img/Logo.jpeg',
    url: '/'
  };

  if (event.data) {
    try {
      const json = event.data.json();
      data = Object.assign(data, json);
    } catch (e) {
      data.body = event.data.text();
    }
  }

  let iconUrl = data.icon || '/assets/img/Logo.jpeg';
  if (iconUrl.startsWith('http://') && self.location.protocol === 'https:') {
    iconUrl = iconUrl.replace(/^http:\/\//i, 'https://');
  }

  let badgeUrl = data.badge || '/assets/img/Logo.jpeg';
  if (badgeUrl.startsWith('http://') && self.location.protocol === 'https:') {
    badgeUrl = badgeUrl.replace(/^http:\/\//i, 'https://');
  }

  const options = {
    body: data.body,
    icon: iconUrl,
    badge: badgeUrl,
    vibrate: [200, 100, 200],
    tag: 'pikat-presensi-' + Date.now(),
    renotify: true,
    requireInteraction: true,
    data: {
      url: data.url || '/'
    }
  };

  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  let targetUrl = (event.notification.data && event.notification.data.url) 
    ? event.notification.data.url 
    : '/';

  // Pastikan target URL selalu menggunakan protokol yang sama dengan origin halaman (HTTPS)
  if (targetUrl.startsWith('http://') && self.location.protocol === 'https:') {
    targetUrl = targetUrl.replace(/^http:\/\//i, 'https://');
  }

  // Jika URL berupa full domain yang sama dengan origin, atau path relatif
  try {
    const parsedUrl = new URL(targetUrl, self.location.origin);
    targetUrl = parsedUrl.href;
  } catch (e) {
    targetUrl = self.location.origin + '/';
  }

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      // Jika ada window/tab yang sudah terbuka, fokuskan dan arahkan
      for (const client of clientList) {
        if (client.url === targetUrl && 'focus' in client) {
          return client.focus();
        }
      }
      // Jika belum ada window terbuka, buka url baru
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});

