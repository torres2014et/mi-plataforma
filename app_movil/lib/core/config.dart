/// Configuración de la conexión al backend (Fase 2).
///
/// La URL base del backend ya **no** está fija: el usuario puede cambiarla
/// desde la pantalla de login (engranaje "Servidor"). Esto permite repartir el
/// APK **una sola vez** y luego apuntarlo a la URL pública del túnel
/// (ngrok / Cloudflare Tunnel) sin recompilar — solo se pega la nueva URL.
///
/// El valor que se escribe se guarda en el teléfono ([ApiClient.cambiarUrl]) y
/// se restaura al abrir la app ([ApiClient.cargarConfig]). [baseUrl] de abajo es
/// solo el **valor por defecto** cuando todavía no se ha configurado ninguno.
///
/// Pistas según DÓNDE corre la app:
/// - **Teléfono físico** en la misma Wi-Fi que el PC: la IP LAN del PC, p. ej.
///   `http://192.168.101.12:8000` (arrancar el backend con
///   `php artisan serve --host=0.0.0.0 --port=8000`).
/// - **Emulador de Android**: `http://10.0.2.2:8000` (el "localhost" del PC).
/// - **Prueba real entre casas**: la URL https del túnel, p. ej.
///   `https://algo.ngrok-free.app`.
class ApiConfig {
  /// URL base de la API **por defecto** (se usa solo si no hay una guardada).
  static const String baseUrl = 'https://retype-coil-charity.ngrok-free.dev/api';

  /// Tiempo máximo de espera de cada petición.
  static const Duration timeout = Duration(seconds: 20);

  // ── Fase 3: tiempo real (Reverb / protocolo Pusher) ──────────────────────
  /// Clave **pública** de la app Reverb (es de cliente, va en el APK; el secreto
  /// se queda en el backend). Debe coincidir con `REVERB_APP_KEY` del `.env`.
  static const String reverbAppKey = '2pqepgpfuvt7rfffgsnm';

  /// Puerto local de Reverb (cuando se prueba en LAN, no por túnel).
  static const int reverbPort = 8080;

  /// Deriva la URL del WebSocket de Reverb a partir de la URL de la API.
  /// - `http`  → `ws`  + puerto local de Reverb (caso LAN / emulador).
  /// - `https` → `wss` + mismo host sin puerto (caso túnel: requiere que Reverb
  ///   esté tunelizado en ese host; ver notas de despliegue de Fase 3).
  static String reverbWsUrl(String apiBaseUrl) {
    final uri = Uri.parse(apiBaseUrl);
    if (uri.scheme == 'https') {
      return 'wss://${uri.host}/app/$reverbAppKey';
    }
    return 'ws://${uri.host}:$reverbPort/app/$reverbAppKey';
  }

  /// Normaliza lo que escribe el usuario a una URL base de API válida:
  /// - recorta espacios y barras finales;
  /// - si no trae esquema, le antepone `http://`;
  /// - si no termina en `/api`, se lo agrega.
  ///
  /// Así el usuario puede pegar `algo.ngrok-free.app` o
  /// `https://algo.ngrok-free.app/` y queda `https://algo.ngrok-free.app/api`.
  /// Si la entrada queda vacía, devuelve [baseUrl].
  static String normalizarUrl(String entrada) {
    var url = entrada.trim();
    if (url.isEmpty) return baseUrl;

    if (!url.startsWith('http://') && !url.startsWith('https://')) {
      url = 'http://$url';
    }
    // Quitar barras finales.
    while (url.endsWith('/')) {
      url = url.substring(0, url.length - 1);
    }
    if (!url.toLowerCase().endsWith('/api')) {
      url = '$url/api';
    }
    return url;
  }
}
