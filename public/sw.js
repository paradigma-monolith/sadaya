const CACHE_NAME = 'sadaya-static-v1';
const PRECACHE_URLS = [
  '/manifest.webmanifest',
  '/Logo%20BKK%20Bandung.png',
  '/favicon.ico',
];

const OFFLINE_HTML = `<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Offline — SADAYA BKK Bandung</title>
  <style>
    body{font-family:system-ui,-apple-system,sans-serif;background:#f4f7f9;color:#0f172a;margin:0;min-height:100vh;display:grid;place-items:center;padding:24px}
    .card{max-width:420px;background:#fff;border:1px solid #cbd5e1;border-radius:16px;padding:28px;box-shadow:0 12px 30px rgba(15,23,42,.08);text-align:center}
    h1{font-size:18px;margin:0 0 8px;color:#0f172a}
    p{font-size:14px;line-height:1.5;color:#475569;margin:0 0 20px}
    button{background:#00A99D;color:#fff;border:0;border-radius:10px;padding:10px 18px;font-weight:600;cursor:pointer}
  </style>
</head>
<body>
  <div class="card">
    <h1>Koneksi Jaringan Terputus</h1>
    <p>Perangkat sedang berada di luar jangkauan internet. Draf formulir lokal tetap tersimpan di peramban hingga koneksi pulih.</p>
    <button type="button" onclick="window.location.reload()">Coba Muat Ulang</button>
  </div>
</body>
</html>`;

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS)).catch(() => {})
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;

  // Never cache dynamic API, auth, or Inertia XHR requests; let navigations use network first.
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(
        () =>
          new Response(OFFLINE_HTML, {
            status: 503,
            headers: { 'Content-Type': 'text/html; charset=utf-8' },
          })
      )
    );
    return;
  }

  const isImmutableBuildAsset = url.pathname.startsWith('/build/assets/') || PRECACHE_URLS.includes(url.pathname);
  if (!isImmutableBuildAsset) return;

  event.respondWith(
    caches.match(request).then((cached) => {
      if (cached) return cached;
      return fetch(request).then((response) => {
        if (response.ok) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, clone)).catch(() => {});
        }
        return response;
      });
    })
  );
});
