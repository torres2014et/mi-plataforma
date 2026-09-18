import '../core/utils/parse.dart';

/// Una opción individual dentro de un [GrupoOpciones] (p. ej. "Lechuga",
/// "Tamaño grande", "Tocineta extra"). Puede sumar un [precioExtra] (0 = gratis)
/// y venir marcada [porDefecto]. Mapea al backend Laravel (`snake_case`).
class OpcionProducto {
  final int id;
  final String nombre;

  /// Lo que suma esta opción al precio del producto (en pesos). 0 si es gratis.
  final double precioExtra;

  /// Si viene seleccionada de entrada al abrir el personalizador.
  final bool porDefecto;

  const OpcionProducto({
    required this.id,
    required this.nombre,
    this.precioExtra = 0,
    this.porDefecto = false,
  });

  factory OpcionProducto.fromJson(Map<String, dynamic> json) => OpcionProducto(
        id: toInt(json['id']),
        nombre: (json['nombre'] ?? '').toString(),
        precioExtra: toDouble(json['precio_extra']),
        porDefecto:
            json['por_defecto'] == null ? false : toBool(json['por_defecto']),
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'nombre': nombre,
        'precio_extra': precioExtra,
        'por_defecto': porDefecto,
      };
}
