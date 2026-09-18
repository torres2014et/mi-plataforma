import 'package:flutter_local_notifications/flutter_local_notifications.dart';

import 'notificacion_service.dart';

/// Implementación real de [NotificacionService] con notificaciones locales del
/// sistema (barra de notificaciones de Android).
class LocalNotificacionService implements NotificacionService {
  final FlutterLocalNotificationsPlugin _plugin =
      FlutterLocalNotificationsPlugin();
  bool _listo = false;

  static const String _canalId = 'pedidos';
  static const String _canalNombre = 'Estado del pedido';
  static const String _canalDesc =
      'Avisos de cuándo tu pedido sale y está por llegar';

  @override
  Future<void> init() async {
    if (_listo) return;
    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    await _plugin.initialize(const InitializationSettings(android: android));

    final androidImpl =
        _plugin.resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>();
    await androidImpl?.createNotificationChannel(
      const AndroidNotificationChannel(
        _canalId,
        _canalNombre,
        description: _canalDesc,
        importance: Importance.high,
      ),
    );
    // Permiso de notificaciones (Android 13+).
    await androidImpl?.requestNotificationsPermission();
    _listo = true;
  }

  Future<void> _mostrar(int id, String titulo, String cuerpo) async {
    await init();
    await _plugin.show(
      id,
      titulo,
      cuerpo,
      const NotificationDetails(
        android: AndroidNotificationDetails(
          _canalId,
          _canalNombre,
          channelDescription: _canalDesc,
          importance: Importance.high,
          priority: Priority.high,
          icon: '@mipmap/ic_launcher',
        ),
      ),
    );
  }

  @override
  Future<void> pedidoSalio(
      {required int pedidoId, required String restaurante}) {
    return _mostrar(
      pedidoId,
      'Tu pedido salió 🛵',
      'Tu pedido de $restaurante ya va en camino.',
    );
  }

  @override
  Future<void> pedidoPorLlegar(
      {required int pedidoId, required String restaurante}) {
    // Id distinto para que no reemplace la notificación de "salió".
    return _mostrar(
      pedidoId + 500000,
      'Tu pedido está por llegar 📍',
      'El domiciliario de $restaurante está cerca de tu dirección.',
    );
  }

  @override
  Future<void> mostrar({required String titulo, required String cuerpo}) {
    // Id rotativo para no pisar otros avisos en pantalla.
    final id = DateTime.now().millisecondsSinceEpoch.remainder(1000000);
    return _mostrar(id, titulo, cuerpo);
  }
}
