import '../core/utils/parse.dart';
import 'producto.dart';

/// Restaurante. Mapea al modelo Restaurante de Laravel.
///
/// [productos] puede venir vacío cuando se lista (el menú se carga aparte) y
/// lleno cuando se pide el detalle.
class Restaurante {
  final int id;
  final String nombre;
  final String descripcion;
  final String categoria;
  final String? imagenUrl;
  final double rating;
  final int tiempoEntregaMin;

  /// Minutos que el restaurante tarda en cocinar el pedido. Se suma al tiempo
  /// de viaje para el estimado de llegada mientras el pedido se prepara.
  final int tiempoPreparacionMin;
  final double costoDomicilio;
  final bool abierto;
  final String direccion;
  final double lat;
  final double lng;
  final List<Producto> productos;

  const Restaurante({
    required this.id,
    required this.nombre,
    required this.descripcion,
    required this.categoria,
    required this.rating,
    required this.tiempoEntregaMin,
    required this.costoDomicilio,
    this.tiempoPreparacionMin = 15,
    required this.direccion,
    required this.lat,
    required this.lng,
    this.imagenUrl,
    this.abierto = true,
    this.productos = const [],
  });

  /// Categorías del menú presentes en este restaurante (para los tabs/secciones).
  List<String> get categoriasMenu {
    final set = <String>{};
    for (final p in productos) {
      set.add(p.categoria);
    }
    return set.toList();
  }

  factory Restaurante.fromJson(Map<String, dynamic> json) {
    return Restaurante(
      id: toInt(json['id']),
      nombre: (json['nombre'] ?? '').toString(),
      descripcion: (json['descripcion'] ?? '').toString(),
      categoria: (json['categoria'] ?? 'General').toString(),
      imagenUrl: json['imagen_url']?.toString(),
      rating: toDouble(json['rating']),
      tiempoEntregaMin: toInt(json['tiempo_entrega_min']),
      tiempoPreparacionMin: json['tiempo_preparacion_min'] == null
          ? 15
          : toInt(json['tiempo_preparacion_min']),
      costoDomicilio: toDouble(json['costo_domicilio']),
      abierto: json['abierto'] == null ? true : toBool(json['abierto']),
      direccion: (json['direccion'] ?? '').toString(),
      lat: toDouble(json['lat']),
      lng: toDouble(json['lng']),
      productos: (json['productos'] as List<dynamic>? ?? [])
          .map((e) => Producto.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'nombre': nombre,
        'descripcion': descripcion,
        'categoria': categoria,
        'imagen_url': imagenUrl,
        'rating': rating,
        'tiempo_entrega_min': tiempoEntregaMin,
        'tiempo_preparacion_min': tiempoPreparacionMin,
        'costo_domicilio': costoDomicilio,
        'abierto': abierto,
        'direccion': direccion,
        'lat': lat,
        'lng': lng,
        'productos': productos.map((p) => p.toJson()).toList(),
      };

  Restaurante copyWith({List<Producto>? productos}) {
    return Restaurante(
      id: id,
      nombre: nombre,
      descripcion: descripcion,
      categoria: categoria,
      imagenUrl: imagenUrl,
      rating: rating,
      tiempoEntregaMin: tiempoEntregaMin,
      tiempoPreparacionMin: tiempoPreparacionMin,
      costoDomicilio: costoDomicilio,
      abierto: abierto,
      direccion: direccion,
      lat: lat,
      lng: lng,
      productos: productos ?? this.productos,
    );
  }
}
