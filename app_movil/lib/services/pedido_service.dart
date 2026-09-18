import 'package:latlong2/latlong.dart';

import '../models/calificacion.dart';
import '../models/medio_transporte.dart';
import '../models/pedido.dart';
import '../models/pedido_item.dart';
import '../models/recorrido.dart';

/// Contrato de pedidos, compartido por cliente y domiciliario.
///
/// Implementado hoy por `FakePedidoService`.
abstract interface class PedidoService {
  // ---- Cliente ----
  /// Pedidos del cliente actual (historial + activos).
  Future<List<Pedido>> obtenerMisPedidos();

  Future<Pedido> obtenerPedido(int id);

  /// Crea un pedido (checkout). Devuelve el pedido creado. Si se pasan
  /// [latEntrega]/[lngEntrega], esas son las coordenadas reales de entrega;
  /// si no, el service usa un punto por defecto.
  Future<Pedido> crearPedido({
    required int restauranteId,
    required List<PedidoItem> items,
    required double costoDomicilio,
    required String direccionEntrega,
    double? latEntrega,
    double? lngEntrega,
  });

  /// Califica un pedido entregado.
  Future<Calificacion> calificarPedido({
    required int pedidoId,
    required int estrellas,
    String comentario,
  });

  // ---- Domiciliario ----
  /// Pedidos que el domiciliario está atendiendo ahora mismo (asignados a él
  /// y aún activos). Pueden ser varios.
  Future<List<Pedido>> obtenerPedidosActivos();

  /// Pedidos sin domiciliario asignado y aún activos: están "disponibles" para
  /// que un repartidor los acepte. Aquí caen también los que crea el cliente.
  Future<List<Pedido>> obtenerPedidosDisponibles();

  /// El domiciliario acepta un pedido disponible: se lo asigna (con su [medio]
  /// de transporte) y, en cuanto la comida esté lista, sale "en camino".
  /// Devuelve el pedido actualizado.
  Future<Pedido> aceptarPedido(int pedidoId,
      {MedioTransporte medio = MedioTransporte.moto});

  /// El domiciliario sale a recoger el pedido al restaurante (botón "Voy a
  /// recoger"). No cambia el estado; avisa EN VIVO al restaurante que va en
  /// camino a recoger. Devuelve el pedido actualizado (`recogiendo == true`).
  Future<Pedido> marcarVoyARecoger(int pedidoId);

  /// Pedidos ya entregados por el domiciliario.
  Future<List<Pedido>> obtenerHistorialDomiciliario();

  /// Avanza el estado del pedido (lo usa el domiciliario).
  Future<Pedido> actualizarEstado(int pedidoId, String nuevoEstadoApi);

  /// El **cliente** confirma que recibió el pedido: lo marca como entregado.
  /// En Fase 2 esto notifica al restaurante y al domiciliario desde el backend.
  Future<Pedido> confirmarEntrega(int pedidoId);

  /// Estado del pedido en vivo: emite el pedido actual y luego una vez por cada
  /// cambio de estado. Lo usa el cliente para ver el ciclo (cocina → en camino →
  /// entregado) avanzar en tiempo real sin refrescar. En Fase 2 será el canal de
  /// actualizaciones del backend (websocket).
  Stream<Pedido> seguirPedido(int id);

  /// El **domiciliario** emite su posición GPS para un pedido en camino
  /// (Fase 3). El backend la difunde por websocket a quien sigue el pedido. Es
  /// best-effort: una posición perdida no debe romper el reparto.
  Future<void> enviarUbicacion(int pedidoId, LatLng posicion);

  // ---- Seguimiento en el mapa ----
  /// Recorrido restaurante → entrega (por calles) con distancia y tiempo
  /// estimados. `Recorrido.vacio` si el pedido no tiene punto de entrega.
  Future<Recorrido> obtenerRutaPedido(int pedidoId);

  /// Posición del domiciliario en el tiempo, para verlo moverse por el mapa.
  /// En Fase 3 escucha el GPS real del repartidor por websocket; si no hay
  /// conexión a Reverb (o aún no llega ninguna posición real) cae a la
  /// simulación por tiempo sobre la ruta. Emite mientras el pedido va
  /// `en_camino`.
  Stream<LatLng> seguirDomiciliario(int pedidoId);
}
