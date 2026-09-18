import 'dart:async';
import 'dart:math' as math;

import 'package:latlong2/latlong.dart';

import '../../core/utils/osrm.dart';
import '../../models/calificacion.dart';
import '../../models/estado_pedido.dart';
import '../../models/medio_transporte.dart';
import '../../models/pedido.dart';
import '../../models/pedido_item.dart';
import '../../models/recorrido.dart';
import '../../models/restaurante.dart';
import '../pedido_service.dart';
import 'fake_data.dart';

/// Versión fake de pedidos. Trabaja sobre una copia mutable en memoria, así
/// crear/calificar/avanzar estado se ven reflejados durante la sesión.
///
/// IDs de la demo: cliente = 1, domiciliario = 2 (ver [FakeAuthService]).
class FakePedidoService implements PedidoService {
  // Copia mutable para no alterar los datos originales de FakeData.
  final List<Pedido> _pedidos = List<Pedido>.from(FakeData.pedidos);

  int _siguienteId = 9100;

  // Recorrido OSRM cacheado por pedido (se pide una sola vez y se reutiliza
  // para dibujar la línea, mostrar distancia/tiempo y mover al domiciliario).
  final Map<int, Recorrido> _rutas = {};

  // ---- Simulación del ciclo de vida en tiempo real ----
  // Emite el pedido cada vez que cambia de estado, para que las pantallas que
  // lo observan (`seguirPedido`) se actualicen solas.
  final StreamController<Pedido> _cambios = StreamController<Pedido>.broadcast();

  // Pedidos cuya cocina ya terminó (listos para salir en cuanto haya domi).
  final Set<int> _listos = {};

  // Pedidos con un ciclo de cocina ya programado (para no duplicarlo).
  final Set<int> _enCiclo = {};

  // Hora a la que cada pedido salió "en camino", para mover al domiciliario por
  // tiempo transcurrido (así, mirar el mapa tarde lo muestra ya avanzado).
  final Map<int, DateTime> _salida = {};

  // Duración (comprimida para la demo) del viaje de cada pedido, calculada una
  // sola vez a partir de su ruta y su medio de transporte.
  final Map<int, Duration> _duracionViajePed = {};

  // Tiempos de la simulación, acelerados para la demo.
  static const _retardoConfirmar = Duration(seconds: 2);
  static const _retardoPreparar = Duration(seconds: 3);

  // El viaje ya NO es fijo: su duración sale de la distancia real de la ruta y
  // la velocidad del medio de transporte (ver [MedioTransporte]). Para no
  // esperar los minutos reales en la demo, esa duración se comprime con
  // [_factorDemo] (≈3x más rápido que la vida real) y se acota entre
  // [_minViaje] y [_maxViaje]. El estimado que ve el usuario, en cambio, sí es
  // el tiempo real. Pon _factorDemo = 1.0 para correr a tiempo real.
  static const double _factorDemo = 0.35;
  static const _minViaje = Duration(seconds: 18);
  static const _maxViaje = Duration(seconds: 150);

  /// Cuánto "cocina" el restaurante en la demo (derivado de su tiempo real de
  /// preparación, acotado para no aburrir ni pasar de largo).
  Duration _duracionCocina(int prepMin) =>
      Duration(seconds: prepMin.clamp(10, 16));

  // ---- Cliente ----
  @override
  Future<List<Pedido>> obtenerMisPedidos() async {
    await Future.delayed(const Duration(milliseconds: 1000));
    final mios = _pedidos.where((p) => p.clienteId == 1).toList()
      ..sort((a, b) =>
          (b.createdAt ?? DateTime(0)).compareTo(a.createdAt ?? DateTime(0)));
    return mios;
  }

  @override
  Future<Pedido> obtenerPedido(int id) async {
    await Future.delayed(const Duration(milliseconds: 500));
    return _pedidos.firstWhere(
      (p) => p.id == id,
      orElse: () => throw Exception('Pedido $id no encontrado'),
    );
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
    await Future.delayed(const Duration(milliseconds: 1100));

    final subtotal = items.fold<double>(0, (acc, i) => acc + i.subtotal);
    final restaurante = FakeData.restaurantes.firstWhere(
      (r) => r.id == restauranteId,
      orElse: () => throw Exception('Restaurante $restauranteId no encontrado'),
    );

    final pedido = Pedido(
      id: _siguienteId++,
      restauranteId: restauranteId,
      restauranteNombre: restaurante.nombre,
      restauranteImagenUrl: restaurante.imagenUrl,
      clienteId: 1,
      // Sin domiciliario: queda "disponible" para que un repartidor lo acepte.
      estado: EstadoPedido.pendiente,
      items: items,
      subtotal: subtotal,
      costoDomicilio: costoDomicilio,
      total: subtotal + costoDomicilio,
      direccionEntrega: direccionEntrega,
      // Coordenadas reales si llegan (GPS); si no, un punto por defecto cerca
      // de Ubaté para que el mapa siga funcionando.
      latEntrega: latEntrega ?? 5.3168,
      lngEntrega: lngEntrega ?? -73.8121,
      // Medio del domiciliario según qué tan lejos queda la entrega.
      medioTransporte: _medioSegunDistancia(
          restaurante, latEntrega ?? 5.3168, lngEntrega ?? -73.8121),
      createdAt: DateTime.now(),
      codigoConfirmacion: _generarCodigoConfirmacion(),
    );

    _pedidos.add(pedido);
    // El restaurante "confirma" y entra a cocina solo: arranca la simulación.
    _simularCiclo(pedido.id, restaurante.tiempoPreparacionMin);
    return pedido;
  }

  @override
  Future<Calificacion> calificarPedido({
    required int pedidoId,
    required int estrellas,
    String comentario = '',
  }) async {
    await Future.delayed(const Duration(milliseconds: 800));

    final index = _pedidos.indexWhere((p) => p.id == pedidoId);
    if (index == -1) throw Exception('Pedido $pedidoId no encontrado');

    final pedido = _pedidos[index];
    _pedidos[index] = pedido.copyWith(calificado: true);
    if (!_cambios.isClosed) _cambios.add(_pedidos[index]);

    return Calificacion(
      id: DateTime.now().millisecondsSinceEpoch,
      pedidoId: pedidoId,
      restauranteId: pedido.restauranteId,
      estrellas: estrellas,
      comentario: comentario,
      createdAt: DateTime.now(),
    );
  }

  // ---- Domiciliario ----
  @override
  Future<List<Pedido>> obtenerPedidosActivos() async {
    await Future.delayed(const Duration(milliseconds: 900));
    return _pedidos
        .where((p) => p.domiciliarioId == 2 && p.estado.estaActivo)
        .toList()
      ..sort((a, b) =>
          (b.createdAt ?? DateTime(0)).compareTo(a.createdAt ?? DateTime(0)));
  }

  @override
  Future<List<Pedido>> obtenerPedidosDisponibles() async {
    await Future.delayed(const Duration(milliseconds: 900));
    return _pedidos
        .where((p) => p.domiciliarioId == null && p.estado.estaActivo)
        .toList()
      ..sort((a, b) =>
          (b.createdAt ?? DateTime(0)).compareTo(a.createdAt ?? DateTime(0)));
  }

  @override
  Future<Pedido> aceptarPedido(int pedidoId,
      {MedioTransporte medio = MedioTransporte.moto}) async {
    await Future.delayed(const Duration(milliseconds: 700));

    final index = _pedidos.indexWhere((p) => p.id == pedidoId);
    if (index == -1) throw Exception('Pedido $pedidoId no encontrado');
    if (_pedidos[index].domiciliarioId != null) {
      throw Exception('Ese pedido ya fue tomado por otro domiciliario');
    }

    // Se lo asigna a este domiciliario con su medio de transporte, sin tocar el
    // estado: si la comida aún se cocina, espera en preparación; sale "en
    // camino" recién esté lista.
    final actualizado =
        _pedidos[index].copyWith(domiciliarioId: 2, medioTransporte: medio);
    _pedidos[index] = actualizado;
    if (!_cambios.isClosed) _cambios.add(actualizado);

    // Pedidos semilla (o cualquiera sin ciclo en marcha) no tienen temporizador
    // de cocina: al aceptarlos, se les programa una cocción corta para que
    // también salgan "en camino" solos.
    if (!_enCiclo.contains(pedidoId) && !_listos.contains(pedidoId)) {
      _enCiclo.add(pedidoId);
      Timer(const Duration(seconds: 5), () {
        _listos.add(pedidoId);
        _intentarSalir(pedidoId);
      });
    }
    // Si la cocina ya había terminado, sale de inmediato.
    _intentarSalir(pedidoId);
    return _pedidos[_pedidos.indexWhere((p) => p.id == pedidoId)];
  }

  @override
  Future<Pedido> marcarVoyARecoger(int pedidoId) async {
    await Future.delayed(const Duration(milliseconds: 400));
    final index = _pedidos.indexWhere((p) => p.id == pedidoId);
    if (index == -1) throw Exception('Pedido $pedidoId no encontrado');
    final actualizado = _pedidos[index].copyWith(recogiendo: true);
    _pedidos[index] = actualizado;
    if (!_cambios.isClosed) _cambios.add(actualizado);
    return actualizado;
  }

  @override
  Future<List<Pedido>> obtenerHistorialDomiciliario() async {
    await Future.delayed(const Duration(milliseconds: 1000));
    return _pedidos
        .where((p) => p.domiciliarioId == 2 && p.estado.esFinal)
        .toList();
  }

  @override
  Future<Pedido> actualizarEstado(int pedidoId, String nuevoEstadoApi) async {
    await Future.delayed(const Duration(milliseconds: 600));

    final actualizado =
        _setEstado(pedidoId, EstadoPedido.fromApi(nuevoEstadoApi));
    if (actualizado == null) throw Exception('Pedido $pedidoId no encontrado');
    return actualizado;
  }

  @override
  Future<Pedido> confirmarEntrega(int pedidoId) async {
    await Future.delayed(const Duration(milliseconds: 600));
    final actualizado = _setEstado(pedidoId, EstadoPedido.entregado);
    if (actualizado == null) throw Exception('Pedido $pedidoId no encontrado');
    return actualizado;
  }

  // ---- Máquina de estados de la simulación ----

  /// Cambia el estado de un pedido (y opcionalmente le asigna domiciliario),
  /// avisa a quien lo observe y, si entra a "en camino", arranca su viaje.
  Pedido? _setEstado(int id, EstadoPedido estado, {int? domiciliarioId}) {
    final i = _pedidos.indexWhere((p) => p.id == id);
    if (i == -1) return null;
    final actualizado =
        _pedidos[i].copyWith(estado: estado, domiciliarioId: domiciliarioId);
    _pedidos[i] = actualizado;
    if (!_cambios.isClosed) _cambios.add(actualizado);
    if (estado == EstadoPedido.enCamino) _iniciarViaje(id);
    return actualizado;
  }

  /// Programa el ciclo automático de un pedido recién creado:
  /// pendiente → confirmado → en preparación → (cuando esté listo) en camino.
  void _simularCiclo(int id, int prepMin) {
    _enCiclo.add(id);
    Timer(_retardoConfirmar, () => _setEstado(id, EstadoPedido.confirmado));
    final tPreparar = _retardoConfirmar + _retardoPreparar;
    Timer(tPreparar, () => _setEstado(id, EstadoPedido.enPreparacion));
    Timer(tPreparar + _duracionCocina(prepMin), () {
      _listos.add(id);
      _intentarSalir(id);
    });
  }

  /// Lleva el pedido a "en camino" si ya está cocinado y tiene domiciliario.
  /// Si falta cualquiera de las dos, no hace nada y se reintenta luego (cuando
  /// termina la cocina o cuando un domiciliario lo acepta).
  void _intentarSalir(int id) {
    if (!_listos.contains(id)) return;
    final i = _pedidos.indexWhere((p) => p.id == id);
    if (i == -1) return;
    final p = _pedidos[i];
    if (p.domiciliarioId == null) return;
    if (p.estado.esFinal || p.estado == EstadoPedido.enCamino) return;
    _setEstado(id, EstadoPedido.enCamino);
  }

  /// Marca la hora de salida del domiciliario (para mover su posición por el
  /// tiempo transcurrido). Idempotente. Ya **no** entrega solo: el domiciliario
  /// llega al destino y espera a que el **cliente confirme** la recepción (ver
  /// [confirmarEntrega]); así el restaurante también se entera de la entrega.
  void _iniciarViaje(int id) {
    _salida.putIfAbsent(id, () => DateTime.now());
  }

  /// Elige el medio del domiciliario según la distancia en línea recta del
  /// restaurante a la entrega: cerca → bici, medio → moto, lejos → auto.
  MedioTransporte _medioSegunDistancia(
      Restaurante restaurante, double lat, double lng) {
    final metros = const Distance()(
        LatLng(restaurante.lat, restaurante.lng), LatLng(lat, lng));
    if (metros < 1000) return MedioTransporte.bici;
    if (metros < 3000) return MedioTransporte.moto;
    return MedioTransporte.auto;
  }

  /// Duración del viaje (comprimida para la demo) de un pedido, a partir de la
  /// distancia real de [recorrido] y la velocidad de su medio de transporte.
  Duration _duracionViajeDe(int id, Recorrido recorrido) {
    final i = _pedidos.indexWhere((p) => p.id == id);
    final medio =
        i == -1 ? MedioTransporte.moto : _pedidos[i].medioTransporte;
    final realSeg = medio.tiempoSegundos(recorrido.distanciaMetros);
    final demo = Duration(milliseconds: (realSeg * 1000 * _factorDemo).round());
    if (demo < _minViaje) return _minViaje;
    if (demo > _maxViaje) return _maxViaje;
    return demo;
  }

  @override
  Stream<Pedido> seguirPedido(int id) async* {
    final actual = _pedidos.firstWhere(
      (p) => p.id == id,
      orElse: () => throw Exception('Pedido $id no encontrado'),
    );
    yield actual;
    yield* _cambios.stream.where((p) => p.id == id);
  }

  // ---- Seguimiento en el mapa ----
  @override
  Future<Recorrido> obtenerRutaPedido(int pedidoId) async {
    final cacheado = _rutas[pedidoId];
    if (cacheado != null) return cacheado;

    final pedido = _pedidos.firstWhere(
      (p) => p.id == pedidoId,
      orElse: () => throw Exception('Pedido $pedidoId no encontrado'),
    );
    if (pedido.latEntrega == null || pedido.lngEntrega == null) {
      return Recorrido.vacio;
    }

    final restaurante = FakeData.restaurantes.firstWhere(
      (r) => r.id == pedido.restauranteId,
      orElse: () =>
          throw Exception('Restaurante ${pedido.restauranteId} no encontrado'),
    );

    final recorrido = await obtenerRutaOsrm(
      LatLng(restaurante.lat, restaurante.lng),
      LatLng(pedido.latEntrega!, pedido.lngEntrega!),
    );
    _rutas[pedidoId] = recorrido;
    return recorrido;
  }

  @override
  Future<void> enviarUbicacion(int pedidoId, LatLng posicion) async {
    // Respaldo sin red: el movimiento ya se simula en seguirDomiciliario, así
    // que aquí no hay nada que difundir.
  }

  @override
  Stream<LatLng> seguirDomiciliario(int pedidoId) async* {
    final pedido = _pedidos.firstWhere(
      (p) => p.id == pedidoId,
      orElse: () => throw Exception('Pedido $pedidoId no encontrado'),
    );
    final recorrido = await obtenerRutaPedido(pedidoId);
    final ruta = recorrido.puntos;
    if (ruta.length < 2) {
      if (ruta.isNotEmpty) yield ruta.first;
      return;
    }

    // Si todavía no salió, el domiciliario espera en el restaurante; si ya
    // entregó, queda en el destino.
    if (pedido.estado == EstadoPedido.entregado) {
      yield ruta.last;
      return;
    }
    if (pedido.estado != EstadoPedido.enCamino) {
      yield ruta.first;
      return;
    }

    // Distancias acumuladas a lo largo de la ruta.
    const dist = Distance();
    final acumulado = <double>[0];
    for (var i = 1; i < ruta.length; i++) {
      acumulado.add(acumulado[i - 1] + dist(ruta[i - 1], ruta[i]));
    }
    final total = acumulado.last;

    // El avance se calcula por tiempo transcurrido desde la salida (no por
    // número de pasos): así, si el cliente abre el mapa a mitad del viaje, ve
    // al domiciliario ya avanzado en vez de reiniciarse. (Pedidos semilla que
    // nacen "en camino" fijan su salida aquí, la primera vez que se observan.)
    _iniciarViaje(pedidoId);
    final salida = _salida[pedidoId]!;
    final duracionMs =
        (_duracionViajePed[pedidoId] ??= _duracionViajeDe(pedidoId, recorrido))
            .inMilliseconds;
    const tick = Duration(milliseconds: 200);
    while (true) {
      final transcurrido = DateTime.now().difference(salida).inMilliseconds;
      final f = (transcurrido / duracionMs).clamp(0.0, 1.0);
      yield _puntoEnRuta(ruta, acumulado, total * f);
      if (f >= 1.0) break;
      // Si el pedido dejó de ir en camino (p. ej. lo entregó), cortar.
      final i = _pedidos.indexWhere((p) => p.id == pedidoId);
      if (i != -1 && _pedidos[i].estado != EstadoPedido.enCamino) break;
      await Future.delayed(tick);
    }
  }

  /// Punto sobre la polilínea [ruta] a la distancia [objetivo] (en metros)
  /// desde el inicio, usando las distancias [acumulado] precalculadas.
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

  /// Código de 6 caracteres alfanuméricos para la confirmación de entrega.
  /// Excluye 0/O/1/I/L para que sea legible si toca escribirlo a mano.
  String _generarCodigoConfirmacion() {
    const chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    final rnd = math.Random();
    return List.generate(6, (_) => chars[rnd.nextInt(chars.length)]).join();
  }
}
