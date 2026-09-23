import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../services/api/api_auth_service.dart';
import '../services/api/api_chatbot_service.dart';
import '../services/api/api_client.dart';
import '../services/api/api_pedido_service.dart';
import '../services/api/api_restaurante_service.dart';
import '../services/auth_service.dart';
import '../services/chatbot_service.dart';
import '../services/fcm_service.dart';
import '../services/geolocator_ubicacion_service.dart';
import '../services/local_notificacion_service.dart';
import '../services/notificacion_service.dart';
import '../services/pedido_service.dart';
import '../services/restaurante_service.dart';
import '../services/ubicacion_service.dart';

/// ┌─────────────────────────────────────────────────────────────────────┐
/// │  PUNTO ÚNICO DE CAMBIO ENTRE "DATOS FALSOS" Y "BACKEND REAL".          │
/// │                                                                       │
/// │  FASE 2 (actual): cada service apunta a su implementación `Api...`     │
/// │  que llama al backend Laravel (ver `core/config.dart` para la URL).    │
/// │  Para volver a los datos de prueba, basta cambiar estos `return` por   │
/// │  las versiones `Fake...` de `lib/services/fake/`. Las pantallas no     │
/// │  cambian: dependen de la interfaz, no de la implementación.            │
/// └─────────────────────────────────────────────────────────────────────┘

/// Cliente HTTP compartido (dio + token Sanctum). Única instancia en la app.
final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient.crear();
});

final authServiceProvider = Provider<AuthService>((ref) {
  return ApiAuthService(ref.read(apiClientProvider));
});

final restauranteServiceProvider = Provider<RestauranteService>((ref) {
  return ApiRestauranteService(ref.read(apiClientProvider));
});

final pedidoServiceProvider = Provider<PedidoService>((ref) {
  return ApiPedidoService(ref.read(apiClientProvider));
});

final chatbotServiceProvider = Provider<ChatbotService>((ref) {
  return ApiChatbotService(ref.read(apiClientProvider));
});

// Ubicación: implementación real con GPS (no tiene versión Fake). No depende
// del backend; se mantiene igual.
final ubicacionServiceProvider = Provider<UbicacionService>((ref) {
  return GeolocatorUbicacionService();
});

// Notificaciones locales del sistema. Se sobrescribe en main() con una
// instancia ya inicializada (ver `main.dart`).
final notificacionServiceProvider = Provider<NotificacionService>((ref) {
  return LocalNotificacionService();
});

// Push reales (FCM) — Fase 3, Paso 3. Servicio de dispositivo; se sobrescribe
// en main() con una instancia ya inicializada. Inactivo (falla suave) hasta
// agregar google-services.json y activar el plugin de gradle (ver FASE3.md §4).
final fcmServiceProvider = Provider<FcmService>((ref) {
  return FcmService(
    ref.read(apiClientProvider),
    ref.read(notificacionServiceProvider),
  );
});
