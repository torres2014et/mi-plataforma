import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:latlong2/latlong.dart';

import '../models/estado_pedido.dart';
import '../models/medio_transporte.dart';
import '../models/pedido.dart';
import '../models/recorrido.dart';
import 'services_providers.dart';

/// Medio de transporte con el que reparte el domiciliario. Lo elige en su home
/// y se aplica al aceptar un pedido (define velocidad e ícono de la entrega).
class MedioDomiciliarioNotifier extends Notifier<MedioTransporte> {
  @override
  MedioTransporte build() => MedioTransporte.moto;

  void seleccionar(MedioTransporte medio) => state = medio;
}

final medioDomiciliarioProvider =
    NotifierProvider<MedioDomiciliarioNotifier, MedioTransporte>(
        MedioDomiciliarioNotifier.new);

/// Pedidos del cliente actual (historial + activos).
final misPedidosProvider = FutureProvider<List<Pedido>>((ref) async {
  return ref.watch(pedidoServiceProvider).obtenerMisPedidos();
});

/// Pedidos activos del domiciliario (asignados a él y aún en curso).
final pedidosActivosProvider = FutureProvider<List<Pedido>>((ref) async {
  return ref.watch(pedidoServiceProvider).obtenerPedidosActivos();
});

/// IDs de pedidos que el domiciliario rechazó en esta sesión: la lista se
/// guarda en memoria (en Fase 2 el backend persistirá esta decisión). Filtra
/// [pedidosDisponiblesProvider] para que esos pedidos no reaparezcan.
class PedidosRechazadosNotifier extends Notifier<Set<int>> {
  @override
  Set<int> build() => <int>{};

  void rechazar(int id) => state = {...state, id};
  void desRechazar(int id) => state = {...state}..remove(id);
}

final pedidosRechazadosProvider =
    NotifierProvider<PedidosRechazadosNotifier, Set<int>>(
        PedidosRechazadosNotifier.new);

/// Pedidos disponibles (sin asignar) que el domiciliario puede aceptar,
/// excluyendo los que ya rechazó.
final pedidosDisponiblesProvider = FutureProvider<List<Pedido>>((ref) async {
  final rechazados = ref.watch(pedidosRechazadosProvider);
  final lista =
      await ref.watch(pedidoServiceProvider).obtenerPedidosDisponibles();
  return lista.where((p) => !rechazados.contains(p.id)).toList();
});

/// Historial de entregas del domiciliario.
final historialDomiciliarioProvider = FutureProvider<List<Pedido>>((ref) async {
  return ref.watch(pedidoServiceProvider).obtenerHistorialDomiciliario();
});

/// Detalle de un pedido por id.
final pedidoDetalleProvider =
    FutureProvider.family<Pedido, int>((ref, id) async {
  return ref.watch(pedidoServiceProvider).obtenerPedido(id);
});

/// Pedido en vivo: emite cada vez que cambia de estado, para ver el ciclo
/// (cocina → en camino → entregado) avanzar solo, sin refrescar la pantalla.
final pedidoEnVivoProvider =
    StreamProvider.autoDispose.family<Pedido, int>((ref, id) {
  return ref.watch(pedidoServiceProvider).seguirPedido(id);
});

/// Recorrido restaurante → entrega de un pedido (ruta por calles + distancia y
/// tiempo estimados), para dibujarlo y mostrar el estimado.
final rutaPedidoProvider =
    FutureProvider.autoDispose.family<Recorrido, int>((ref, id) async {
  return ref.watch(pedidoServiceProvider).obtenerRutaPedido(id);
});

/// Posición del domiciliario en vivo (se mueve mientras el pedido va en camino).
/// `autoDispose`: al dejar de mirar el mapa, la simulación se detiene.
final seguimientoDomiciliarioProvider =
    StreamProvider.autoDispose.family<LatLng, int>((ref, id) {
  return ref.watch(pedidoServiceProvider).seguirDomiciliario(id);
});

/// Resumen de desempeño del domiciliario, derivado de su historial y sus
/// pedidos activos. El "ingreso" es la suma de las tarifas de domicilio
/// (`costoDomicilio`) de los pedidos entregados.
class EstadisticasDomiciliario {
  final int entregas;
  final int activos;
  final double ingresos;
  final double valorEntregado;

  const EstadisticasDomiciliario({
    this.entregas = 0,
    this.activos = 0,
    this.ingresos = 0,
    this.valorEntregado = 0,
  });

  /// Ingreso promedio por entrega.
  double get promedioPorEntrega => entregas == 0 ? 0 : ingresos / entregas;
}

/// Estadísticas del domiciliario. Se recalcula cuando cambian el historial o
/// los pedidos activos (p. ej. tras entregar uno).
final estadisticasDomiciliarioProvider =
    Provider.autoDispose<EstadisticasDomiciliario>((ref) {
  final historial =
      ref.watch(historialDomiciliarioProvider).asData?.value ?? const [];
  final activos =
      ref.watch(pedidosActivosProvider).asData?.value ?? const [];

  final entregados =
      historial.where((p) => p.estado == EstadoPedido.entregado).toList();

  return EstadisticasDomiciliario(
    entregas: entregados.length,
    activos: activos.length,
    ingresos: entregados.fold(0.0, (a, p) => a + p.costoDomicilio),
    valorEntregado: entregados.fold(0.0, (a, p) => a + p.total),
  );
});
