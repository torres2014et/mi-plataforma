import '../core/utils/parse.dart';
import 'opcion_producto.dart';

/// Grupo de personalización de un producto (p. ej. "Ingredientes", "Tamaño",
/// "Adiciones"). Mapea al backend Laravel (`snake_case`).
///
/// La cantidad de opciones que se pueden elegir la definen [seleccionMin] y
/// [seleccionMax]:
/// - `seleccionMax == 1` → **selección única** (estilo radio: tamaño, punto).
/// - `seleccionMax > 1`  → **selección múltiple** (estilo checkbox: ingredientes).
/// - `seleccionMin >= 1` → el grupo es **obligatorio** (hay que elegir antes de
///   poder agregar el producto al carrito).
class GrupoOpciones {
  final int id;
  final String nombre;
  final int seleccionMin;
  final int seleccionMax;
  final List<OpcionProducto> opciones;

  const GrupoOpciones({
    required this.id,
    required this.nombre,
    this.seleccionMin = 0,
    this.seleccionMax = 1,
    this.opciones = const [],
  });

  /// Solo se puede elegir una opción (radio).
  bool get esUnica => seleccionMax <= 1;

  /// Hay que elegir al menos una opción para continuar.
  bool get obligatorio => seleccionMin >= 1;

  /// Las opciones marcadas [OpcionProducto.porDefecto].
  List<OpcionProducto> get seleccionInicial =>
      opciones.where((o) => o.porDefecto).toList();

  factory GrupoOpciones.fromJson(Map<String, dynamic> json) => GrupoOpciones(
        id: toInt(json['id']),
        nombre: (json['nombre'] ?? '').toString(),
        seleccionMin: toInt(json['seleccion_min']),
        seleccionMax:
            json['seleccion_max'] == null ? 1 : toInt(json['seleccion_max']),
        opciones: (json['opciones'] as List<dynamic>? ?? const [])
            .map((e) => OpcionProducto.fromJson(e as Map<String, dynamic>))
            .toList(),
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'nombre': nombre,
        'seleccion_min': seleccionMin,
        'seleccion_max': seleccionMax,
        'opciones': opciones.map((o) => o.toJson()).toList(),
      };
}
