import 'dart:async';
import 'dart:convert';

import 'package:latlong2/latlong.dart';

import '../../core/realtime/reverb_client.dart';
import '../../core/utils/osrm.dart';
import '../../models/calificacion.dart';
import '../../models/estado_pedido.dart';
import '../../models/medio_transporte.dart';
import '../../models/pedido.dart';
import '../../models/pedido_item.dart';
import '../../models/recorrido.dart';
import '../pedido_service.dart';
import 'api_client.dart';

/// Implementación real de [PedidoService] contra el backend Laravel.
///
/// El **CRUD** (crear, listar, aceptar, avanzar estado, confirmar) va por la
/// API. El **seguimiento en el mapa** (ruta por calles y movimiento del
/// domiciliario) se sigue calculando en el dispositivo con OSRM, igual que en
/// Fase 1: hasta que el backend emita la posición real del repartidor por
/// websocket, se simula por tiempo sobre la ruta. `seguirPedido` (estado en
/// vivo) se resuelve por **polling** del detalle.
class ApiPedidoService implements PedidoService {
  final ApiClient _api;

  ApiPedidoService(this._api);

  // Ruta OSRM cacheada por pedido (origen restaurante → destino entrega).
  final Map<int, Recorrido> _rutas = {};
  // Coordenadas de cada restaurante, cacheadas para no re-pedirlas.
  final Map<int, LatLng> _origenes = {};
  // Hora a la que cada pedido se observó por primera vez "en camino".
  final Map<int, DateTime> _salida = {};
  // Duración (comprimida para la demo) del viaje de cada pedido.
  final Map<int, Duration> _duracionViajePed = {};

  // Parámetros de la simulación del viaje (idénticos a Fase 1).
  static const double _factorDemo = 0.35;
  static const _minViaje = Duration(seconds: 18);
  static const _maxViaje = Duration(seconds: 150);

  // ---- Helpers de mapeo ----
  Pedido _pedidoDe(Map<String, dynamic> data) =>
      Pedido.fromJson(data['data'] as Map<String, dynamic>);

  List<Pedido> _listaDe(dynamic data) => (data['data'] as List)
      .map((e) => Pedido.fromJson(e as Map<String, dynamic>))
      .toList();

  // ---- Cliente ----
  @override
  Future<List<Pedido>> obtenerMisPedidos() async {
    try {
      final res = await _api.dio.get('/pedidos');
      return _listaDe(res.data);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<Pedido> obtenerPedido(int id) async {
    try {
      final res = await _api.dio.get('/pedidos/$id');
      return _pedidoDe(res.data);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<Pedido> crearPedido({
    required int restauranteId,
    required List<PedidoItem> items,
    required double costoDomicilio,
    required String direccionEntrega,
    double? latEntrega,
    double? lngEntrega,
  }) async {
    try {
      final res = await _api.dio.post('/pedidos', data: {
        'restaurante_id': restauranteId,
        'items': items.map((i) => i.toJson()).toList(),
        'costo_domicilio': costoDomicilio,
        'direccion_entrega': direccionEntrega,
        'lat_entrega': latEntrega,
        'lng_entrega': lngEntrega,
      });
      return _pedidoDe(res.data);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<Calificacion> calificarPedido({
    required int pedidoId,
    required int estrellas,
    String comentario = '',
  }) async {
    try {
      final res = await _api.dio.post('/pedidos/$pedidoId/calificar', data: {
        'estrellas': estrellas,
        'comentario': comentario.isEmpty ? null : comentario,
      });
      return Calificacion.fromJson(res.data as Map<String, dynamic>);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  // ---- Domiciliario ----
  @override
  Future<List<Pedido>> obtenerPedidosActivos() async {
    try {
      final res = await _api.dio.get('/domiciliario/pedidos/activos');
      return _listaDe(res.data);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<List<Pedido>> obtenerPedidosDisponibles() async {
    try {
      final res = await _api.dio.get('/domiciliario/pedidos/disponibles');
      return _listaDe(res.data);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<Pedido> aceptarPedido(int pedidoId,
      {MedioTransporte medio = MedioTransporte.moto}) async {
    try {
      final res = await _api.dio.post('/pedidos/$pedidoId/aceptar', data: {
        'medio_transporte': medio.apiValue,
      });
      return _pedidoDe(res.data);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<Pedido> marcarVoyARecoger(int pedidoId) async {
    try {
      final res = await _api.dio.post('/pedidos/$pedidoId/recoger');
      return _pedidoDe(res.data);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<List<Pedido>> obtenerHistorialDomiciliario() async {
    try {
      final res = await _api.dio.get('/domiciliario/pedidos/historial');
      return _listaDe(res.data);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<Pedido> actualizarEstado(int pedidoId, String nuevoEstadoApi) async {
    try {
      final res = await _api.dio.patch('/pedidos/$pedidoId/estado', data: {
        'estado': nuevoEstadoApi,
      });
      return _pedidoDe(res.data);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<Pedido> confirmarEntrega(int pedidoId) async {
    try {
      final res = await _api.dio.post('/pedidos/$pedidoId/confirmar-entrega');
      return _pedidoDe(res.data);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  // ---- GPS del domiciliario (emitir) ----
  @override
  Future<void> enviarUbicacion(int pedidoId, LatLng posicion) async {
    try {
      await _api.dio.post('/pedidos/$pedidoId/ubicacion', data: {
        'lat': posicion.latitude,
        'lng': posicion.longitude,
      });
    } catch (_) {
      // Best-effort: una posición perdida no corta el reparto.
    }
  }

  // ---- Estado en vivo (Fase 3: websocket Reverb, con fallback a polling) ----
  @override
  Stream<Pedido> seguirPedido(int id) async* {
    Pedido anterior = await obtenerPedido(id);
    yield anterior;
    if (!anterior.estado.estaActivo) return;

    // Fase 3: intentar push por websocket (Reverb). Si el handshake o la
    // suscripción fallan (Reverb no accesible, etc.), se cae a polling sin
    // romper nada (comportamiento de Fase 2).
    final reverb = await ReverbClient.conectar(_api);
    if (reverb != null) {
      try {
        await for (final msg in reverb.suscribirPrivado('private-pedido.$id')) {
          // El mismo canal lleva el GPS del repartidor (evento 'ubicacion'):
          // eso no es un cambio de estado, así que no re-consultamos el pedido.
          if (msg['event'] == 'ubicacion') continue;
          final actual = await obtenerPedido(id);
          if (actual.estado != anterior.estado ||
              actual.domiciliarioId != anterior.domiciliarioId) {
            yield actual;
          }
          anterior = actual;
          if (!anterior.estado.estaActivo) break;
        }
        return;
      } catch (_) {
        // Falló la suscripción/lectura: seguimos por polling abajo.
      } finally {
        await reverb.cerrar();
      }
    }

    // Fallback: polling cada 3 s (consulta el detalle y emite si algo cambió).
    while (anterior.estado.estaActivo) {
      await Future.delayed(const Duration(seconds: 3));
      try {
        final actual = await obtenerPedido(id);
        if (actual.estado != anterior.estado ||
            actual.domiciliarioId != anterior.domiciliarioId) {
          yield actual;
        }
        anterior = actual;
      } catch (_) {
        // Un fallo puntual de red no corta el seguimiento; reintenta.
      }
    }
  }

  // ---- Seguimiento en el mapa ----
  @override
  Future<Recorrido> obtenerRutaPedido(int pedidoId) async {
    final cacheado = _rutas[pedidoId];
    if (cacheado != null) return cacheado;

    final pedido = await obtenerPedido(pedidoId);
    if (pedido.latEntrega == null || pedido.lngEntrega == null) {
      return Recorrido.vacio;
    }

    final origen = await _origenDe(pedido.restauranteId);
    final recorrido = await obtenerRutaOsrm(
      origen,
      LatLng(pedido.latEntrega!, pedido.lngEntrega!),
    );
    _rutas[pedidoId] = recorrido;
    return recorrido;
  }

  @override
  Stream<LatLng> seguirDomiciliario(int pedidoId) async* {
    final pedido = await obtenerPedido(pedidoId);
    final recorrido = await obtenerRutaPedido(pedidoId);
    final ruta = recorrido.puntos;
    if (ruta.length < 2) {
      if (ruta.isNotEmpty) yield ruta.first;
      return;
    }

    if (pedido.estado == EstadoPedido.entregado) {
      yield ruta.last;
      return;
    }
    if (pedido.estado != EstadoPedido.enCamino) {
      yield ruta.first;
      return;
    }

    // Distancias acumuladas a lo largo de la ruta (para la simulación de respaldo).
    const dist = Distance();
    final acumulado = <double>[0];
    for (var i = 1; i < ruta.length; i++) {
      acumulado.add(acumulado[i - 1] + dist(ruta[i - 1], ruta[i]));
    }
    final total = acumulado.last;

    _salida.putIfAbsent(pedidoId, () => DateTime.now());
    final salida = _salida[pedidoId]!;
    final duracionMs = (_duracionViajePed[pedidoId] ??=
            _duracionViajeDe(pedido.medioTransporte, recorrido))
        .inMilliseconds;

    // Fase 3 — mezcla GPS real + simulación. Arranca moviéndose por la ruta con
    // la simulación por tiempo (igual que Fase 2, así el demo funciona aunque el
    // repartidor no esté emitiendo); en cuanto llega la **primera posición GPS
    // real** por websocket (evento 'ubicacion'), la simulación se apaga y el
    // marcador pasa a seguir la posición real del domiciliario. Si Reverb no es
    // accesible, la simulación cubre todo el viaje sin romperse.
    final out = StreamController<LatLng>();
    var gpsReal = false;
    Timer? sim;
    ReverbClient? reverb;

    void tickSim() {
      if (gpsReal || out.isClosed) return;
      final transcurrido = DateTime.now().difference(salida).inMilliseconds;
      final f = (transcurrido / duracionMs).clamp(0.0, 1.0);
      out.add(_puntoEnRuta(ruta, acumulado, total * f));
      if (f >= 1.0) sim?.cancel();
    }

    tickSim();
    sim = Timer.periodic(const Duration(milliseconds: 200), (_) => tickSim());

    unawaited(() async {
      reverb = await ReverbClient.conectar(_api);
      if (reverb == null) return;
      try {
        await for (final msg
            in reverb!.suscribirPrivado('private-pedido.$pedidoId')) {
          if (out.isClosed || msg['event'] != 'ubicacion') continue;
          final data = jsonDecode(msg['data'] as String) as Map<String, dynamic>;
          gpsReal = true; // a partir de aquí, manda el GPS real.
          sim?.cancel();
          out.add(LatLng(
            (data['lat'] as num).toDouble(),
            (data['lng'] as num).toDouble(),
          ));
        }
      } catch (_) {
        // Falló la suscripción: la simulación (si sigue viva) cubre el viaje.
      }
    }());

    out.onCancel = () async {
      sim?.cancel();
      await reverb?.cerrar();
    };

    yield* out.stream;
  }

  // ---- Internos ----

  /// Coordenadas (origen) del restaurante, cacheadas.
  Future<LatLng> _origenDe(int restauranteId) async {
    final cacheado = _origenes[restauranteId];
    if (cacheado != null) return cacheado;
    final res = await _api.dio.get('/restaurantes/$restauranteId');
    final data = res.data['data'] as Map<String, dynamic>;
    final origen = LatLng(
      (data['lat'] as num).toDouble(),
      (data['lng'] as num).toDouble(),
    );
    _origenes[restauranteId] = origen;
    return origen;
  }

  Duration _duracionViajeDe(MedioTransporte medio, Recorrido recorrido) {
    final realSeg = medio.tiempoSegundos(recorrido.distanciaMetros);
    final demo = Duration(milliseconds: (realSeg * 1000 * _factorDemo).round());
    if (demo < _minViaje) return _minViaje;
    if (demo > _maxViaje) return _maxViaje;
    return demo;
  }

  LatLng _puntoEnRuta(
      List<LatLng> ruta, List<double> acumulado, double objetivo) {
    if (objetivo <= 0) return ruta.first;
    if (objetivo >= acumulado.last) return ruta.last;
    for (var i = 1; i < acumulado.length; i++) {
      if (acumulado[i] >= objetivo) {
        final segmento = acumulado[i] - acumulado[i - 1];
        final f = segmento == 0 ? 0.0 : (objetivo - acumulado[i - 1]) / segmento;
        final a = ruta[i - 1];
        final b = ruta[i];
        return LatLng(
          a.latitude + (b.latitude - a.latitude) * f,
          a.longitude + (b.longitude - a.longitude) * f,
        );
      }
    }
    return ruta.last;
  }
}
