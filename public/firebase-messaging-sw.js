/* Service Worker de Firebase Cloud Messaging (web) — Fase 3, Paso 3.
 *
 * Recibe los push aunque la pestaña/navegador esté CERRADO y los muestra como
 * notificación del sistema. La config (valores públicos del SDK web) llega por
 * query params al registrar el SW, así no hay que hornearla en el archivo. */
importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js');

const p = new URL(location).searchParams;
firebase.initializeApp({
  apiKey: p.get('apiKey'),
  authDomain: p.get('authDomain'),
  projectId: p.get('projectId'),
  storageBucket: p.get('storageBucket'),
  messagingSenderId: p.get('messagingSenderId'),
  appId: p.get('appId'),
});

const messaging = firebase.messaging();

// Push recibido con la web en segundo plano / cerrada.
messaging.onBackgroundMessage((payload) => {
  const n = payload.notification || {};
  self.registration.showNotification(n.title || 'Tu pedido', {
    body: n.body || '',
    icon: '/favicon.ico',
    tag: 'pedido-' + ((payload.data && payload.data.pedido_id) || ''),
    renotify: true,
  });
});

// Al tocar la notificación, enfocar/abrir la web.
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  event.waitUntil(clients.matchAll({ type: 'window' }).then((list) => {
    for (const c of list) { if ('focus' in c) return c.focus(); }
    if (clients.openWindow) return clients.openWindow('/');
  }));
});
