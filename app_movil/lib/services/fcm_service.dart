import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';

import 'api/api_client.dart';
import 'notificacion_service.dart';

/// Handler de mensajes FCM recibidos con la app **en segundo plano o cerrada**.
///
/// Debe ser una función de nivel superior y marcada como entry-point para que
/// el motor de Flutter pueda invocarla en un isolate aparte. Android ya pinta
/// la `notification` del payload en la bandeja del sistema automáticamente
/// cuando la app no está en primer plano, así que aquí no hace falta nada; se
/// deja registrado porque FCM lo exige para entregar en segundo plano.
@pragma('vm:entry-point')
Future<void> fcmBackgroundHandler(RemoteMessage message) async {}

/// Servicio de **push reales** (Firebase Cloud Messaging) — Fase 3, Paso 3.
///
/// Es un servicio de **dispositivo** (como las notificaciones locales): no
/// cambia ninguna pantalla. Se inicializa en `main()` y registra el token FCM
/// del teléfono en el backend tras iniciar sesión, para que el servidor pueda
/// enviar "tu pedido salió" / "está por llegar" aunque la app esté cerrada.
///
/// **Falla suave:** mientras no se agregue `google-services.json` (paso manual
/// del usuario — ver FASE3.md §4), `Firebase.initializeApp()` lanza y se
/// captura: el servicio queda **inactivo** y la app sigue funcionando con las
/// notificaciones locales de Fase 2. En cuanto se agregue el archivo y se
/// active el plugin de gradle, los push se encienden solos **sin tocar más
/// código**.
class FcmService {
  FcmService(this._api, this._locales);

  final ApiClient _api;
  final NotificacionService _locales;

  bool _activo = false;
  bool get activo => _activo;

  /// Inicializa Firebase y los listeners. Idempotente y tolerante a fallos.
  /// Se llama una vez en `main()` antes de levantar la app.
  Future<void> init() async {
    if (_activo) return;
    try {
      await Firebase.initializeApp();
    } catch (e) {
      // Sin google-services.json todavía: push deshabilitado, no se rompe nada.
      debugPrint('FCM inactivo (Firebase no inicializado aún): $e');
      return;
    }
    _activo = true;

    FirebaseMessaging.onBackgroundMessage(fcmBackgroundHandler);

    final messaging = FirebaseMessaging.instance;
    await messaging.requestPermission();

    // App en PRIMER PLANO: Android no muestra el push solo, así que lo
    // reflejamos con el plugin de notificaciones locales (mismo canal "pedidos").
    FirebaseMessaging.onMessage.listen((msg) {
      final n = msg.notification;
      if (n != null) {
        _locales.mostrar(
          titulo: n.title ?? 'Tu pedido',
          cuerpo: n.body ?? '',
        );
      }
    });

    // Si el token rota, re-registrarlo en el backend.
    messaging.onTokenRefresh.listen(_enviarToken);
  }

  /// Obtiene el token del dispositivo y lo registra en el backend. Se llama tras
  /// iniciar/restaurar sesión (cuando ya hay token Sanctum para autenticar).
  Future<void> registrar() async {
    if (!_activo) return;
    try {
      final token = await FirebaseMessaging.instance.getToken();
      if (token != null) await _enviarToken(token);
    } catch (e) {
      debugPrint('FCM: no se pudo registrar el token: $e');
    }
  }

  Future<void> _enviarToken(String token) async {
    try {
      await _api.dio.post('/me/fcm-token', data: {'fcm_token': token});
    } catch (e) {
      debugPrint('FCM: fallo al enviar el token al backend: $e');
    }
  }

  /// Borra el token en el backend y en el dispositivo (al cerrar sesión), para
  /// que el servidor no le siga mandando push a un teléfono deslogueado.
  Future<void> eliminar() async {
    if (!_activo) return;
    try {
      await _api.dio.delete('/me/fcm-token');
      await FirebaseMessaging.instance.deleteToken();
    } catch (e) {
      debugPrint('FCM: fallo al eliminar el token: $e');
    }
  }
}
