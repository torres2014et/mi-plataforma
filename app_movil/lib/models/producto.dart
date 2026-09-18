import '../core/utils/parse.dart';
import 'grupo_opciones.dart';

/// Producto del menú de un restaurante. Mapea al modelo Producto de Laravel.
class Producto {
  final int id;
  final int restauranteId;
  final String nombre;
  final String descripcion;
  final double precio;
  final String? imagenUrl;

  /// Categoría dentro del menú (p. ej. "Hamburguesas", "Bebidas").
  final String categoria;
  final bool disponible;

  /// Grupos de personalización (ingredientes, tamaño, adiciones...). Vacío si
  /// el producto no se personaliza.
  final List<GrupoOpciones> gruposOpciones;

  const Producto({
    required this.id,
    required this.restauranteId,
    required this.nombre,
    required this.descripcion,
    required this.precio,
    required this.categoria,
    this.imagenUrl,
    this.disponible = true,
    this.gruposOpciones = const [],
  });

  /// `true` si el producto tiene opciones que elegir (abre el personalizador).
  bool get personalizable => gruposOpciones.isNotEmpty;

  factory Producto.fromJson(Map<String, dynamic> json) {
    return Producto(
      id: toInt(json['id']),
      restauranteId: toInt(json['restaurante_id']),
      nombre: (json['nombre'] ?? '').toString(),
      descripcion: (json['descripcion'] ?? '').toString(),
      precio: toDouble(json['precio']),
      categoria: (json['categoria'] ?? 'General').toString(),
      imagenUrl: json['imagen_url']?.toString(),
      disponible: json['disponible'] == null ? true : toBool(json['disponible']),
      gruposOpciones: (json['grupos_opciones'] as List<dynamic>? ?? const [])
          .map((e) => GrupoOpciones.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'restaurante_id': restauranteId,
        'nombre': nombre,
        'descripcion': descripcion,
        'precio': precio,
        'categoria': categoria,
        'imagen_url': imagenUrl,
        'disponible': disponible,
        'grupos_opciones': gruposOpciones.map((g) => g.toJson()).toList(),
      };
}
