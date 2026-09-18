/// Contrato para mostrarle notificaciones del sistema al cliente sobre su
/// pedido. Implementado por `LocalNotificacionService` (notificaciones locales).
///
/// En Fase 2, el "salió"/"por llegar" llegarán como push desde el backend
/// (firebase_messaging) cuando el domiciliario actualice el pedido en su
/// propio teléfono; aquí se disparan localmente.
abstract interface class NotificacionService {
  /// Inicializa el plugin y pide permiso de notificaciones (idempotente).
  Future<void> init();

  /// "Tu pedido salió": el domiciliario lo recogió y va en camino.
  Future<void> pedidoSalio({required int pedidoId, required String restaurante});

  /// "Tu pedido está por llegar": el domiciliario está cerca de la entrega.
  Future<void> pedidoPorLlegar(
      {required int pedidoId, required String restaurante});

  /// Muestra un aviso con título y cuerpo arbitrarios. Lo usa `FcmService` para
  /// reflejar en la bandeja un push de FCM recibido con la app **en primer
  /// plano** (en ese caso Android no lo muestra solo).
  Future<void> mostrar({required String titulo, required String cuerpo});
}
