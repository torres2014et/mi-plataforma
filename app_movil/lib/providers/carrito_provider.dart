import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/opcion_producto.dart';
import '../models/producto.dart';
import '../models/restaurante.dart';

/// Una línea del carrito: un producto, su cantidad y las opciones de
/// personalización elegidas. Dos veces el mismo producto pero con opciones
/// distintas son **líneas distintas** (las identifica [lineId]).
class CarritoItem {
  final Producto producto;
  final int cantidad;
  final List<OpcionProducto> opciones;

  const CarritoItem({
    required this.producto,
    required this.cantidad,
    this.opciones = const [],
  });

  /// Clave de la línea: mismo producto + mismas opciones = misma línea.
  String get lineId {
    final ids = opciones.map((o) => o.id).toList()..sort();
    return '${producto.id}|${ids.join(',')}';
  }

  /// Precio de una unidad ya con los extras de las opciones sumados.
  double get precioUnitario =>
      producto.precio + opciones.fold(0.0, (acc, o) => acc + o.precioExtra);

  double get subtotal => precioUnitario * cantidad;

  /// Opciones elegidas como texto, para mostrar bajo el nombre del producto.
  String get descripcionOpciones => opciones.map((o) => o.nombre).join(' · ');

  CarritoItem copyWith({int? cantidad}) => CarritoItem(
        producto: producto,
        cantidad: cantidad ?? this.cantidad,
        opciones: opciones,
      );
}

/// Estado del carrito. Está atado a UN restaurante (como en Rappi/iFood):
/// si agregas algo de otro restaurante, el carrito se reinicia.
class CarritoState {
  final int? restauranteId;
  final String restauranteNombre;
  final String? restauranteImagenUrl;
  final double costoDomicilio;
  final List<CarritoItem> items;

  const CarritoState({
    this.restauranteId,
    this.restauranteNombre = '',
    this.restauranteImagenUrl,
    this.costoDomicilio = 0,
    this.items = const [],
  });

  bool get isEmpty => items.isEmpty;
  bool get isNotEmpty => items.isNotEmpty;

  int get cantidadTotal => items.fold(0, (acc, i) => acc + i.cantidad);
  double get subtotal => items.fold(0.0, (acc, i) => acc + i.subtotal);
  double get total => subtotal + (isEmpty ? 0 : costoDomicilio);

  /// Cantidad total de un producto en el carrito, sumando todas sus líneas
  /// (con o sin personalización). 0 si no está.
  int cantidadDe(int productoId) =>
      items.where((i) => i.producto.id == productoId).fold(0, (a, i) => a + i.cantidad);

  /// Clave de la línea "simple" (sin opciones) de un producto.
  static String lineIdSimple(int productoId) => '$productoId|';
}

class CarritoNotifier extends Notifier<CarritoState> {
  @override
  CarritoState build() => const CarritoState();

  /// Agrega [cantidad] unidades de un producto con sus [opciones]. Si ya existe
  /// una línea idéntica (mismo producto + mismas opciones) suma la cantidad; si
  /// el carrito era de otro restaurante, lo reinicia con el restaurante nuevo.
  void agregar(
    Producto producto,
    Restaurante restaurante, {
    List<OpcionProducto> opciones = const [],
    int cantidad = 1,
  }) {
    final nuevo =
        CarritoItem(producto: producto, cantidad: cantidad, opciones: opciones);

    final mismoRestaurante =
        state.restauranteId == null || state.restauranteId == restaurante.id;
    final itemsBase = mismoRestaurante
        ? List<CarritoItem>.from(state.items)
        : <CarritoItem>[];

    final idx = itemsBase.indexWhere((i) => i.lineId == nuevo.lineId);
    if (idx == -1) {
      itemsBase.add(nuevo);
    } else {
      itemsBase[idx] =
          itemsBase[idx].copyWith(cantidad: itemsBase[idx].cantidad + cantidad);
    }

    state = CarritoState(
      restauranteId: restaurante.id,
      restauranteNombre: restaurante.nombre,
      restauranteImagenUrl: restaurante.imagenUrl,
      costoDomicilio: restaurante.costoDomicilio,
      items: itemsBase,
    );
  }

  // ---- Operaciones sobre una línea concreta (carrito/checkout) ----
  void incrementarLinea(String lineId) => _delta(lineId, 1);
  void decrementarLinea(String lineId) => _delta(lineId, -1);
  void quitarLinea(String lineId) =>
      _reemplazarItems(state.items.where((i) => i.lineId != lineId).toList());

  // ---- Atajos para productos sin opciones (stepper del menú) ----
  void incrementar(int productoId) =>
      _delta(CarritoState.lineIdSimple(productoId), 1);
  void decrementar(int productoId) =>
      _delta(CarritoState.lineIdSimple(productoId), -1);

  void vaciar() => state = const CarritoState();

  void _delta(String lineId, int delta) {
    final items = <CarritoItem>[];
    for (final i in state.items) {
      if (i.lineId == lineId) {
        final nueva = i.cantidad + delta;
        if (nueva > 0) items.add(i.copyWith(cantidad: nueva));
        // si llega a 0, se elimina (no se agrega)
      } else {
        items.add(i);
      }
    }
    _reemplazarItems(items);
  }

  /// Conserva el contexto del restaurante; si queda vacío, reinicia todo.
  void _reemplazarItems(List<CarritoItem> items) {
    if (items.isEmpty) {
      state = const CarritoState();
      return;
    }
    state = CarritoState(
      restauranteId: state.restauranteId,
      restauranteNombre: state.restauranteNombre,
      restauranteImagenUrl: state.restauranteImagenUrl,
      costoDomicilio: state.costoDomicilio,
      items: items,
    );
  }
}

final carritoProvider =
    NotifierProvider<CarritoNotifier, CarritoState>(CarritoNotifier.new);
