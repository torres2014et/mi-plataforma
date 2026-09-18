{{-- Push WEB real (FCM) — Fase 3, Paso 3.
     Solo se activa si hay config web de Firebase Y el usuario está autenticado.
     Sin config (api_key vacío) no renderiza nada → web sigue igual (inerte). --}}
@auth
@if(config('services.fcm.web.api_key'))
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js"></script>
<script>
(async function () {
  if (!('serviceWorker' in navigator) || typeof firebase === 'undefined') return;

  const cfg = {
    apiKey:            @json(config('services.fcm.web.api_key')),
    authDomain:        @json(config('services.fcm.web.auth_domain')),
    projectId:         @json(config('services.fcm.web.project_id')),
    storageBucket:     @json(config('services.fcm.web.storage_bucket')),
    messagingSenderId: @json(config('services.fcm.web.messaging_sender_id')),
    appId:             @json(config('services.fcm.web.app_id')),
  };
  const vapidKey = @json(config('services.fcm.web.vapid_key'));

  try {
    firebase.initializeApp(cfg);
    const messaging = firebase.messaging();

    if (Notification.permission === 'default') await Notification.requestPermission();
    if (Notification.permission !== 'granted') return;

    // Registra el SW pasándole la config por query params.
    const qs  = new URLSearchParams(cfg).toString();
    const reg = await navigator.serviceWorker.register('/firebase-messaging-sw.js?' + qs);

    const token = await messaging.getToken({ vapidKey: vapidKey, serviceWorkerRegistration: reg });
    if (token) {
      await fetch(@json(route('fcm-token.store')), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        },
        body: JSON.stringify({ token }),
      });
    }

    // Push con la pestaña en primer plano: lo mostramos a mano.
    messaging.onMessage((payload) => {
      const n = payload.notification || {};
      if (Notification.permission === 'granted') {
        new Notification(n.title || 'Tu pedido', { body: n.body || '', icon: '/favicon.ico' });
      }
    });
  } catch (e) {
    console.warn('FCM web no disponible:', e);
  }
})();
</script>
@endif
@endauth
