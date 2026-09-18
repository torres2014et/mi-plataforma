import '../core/utils/parse.dart';

/// Línea de un pedido. Mapea al modelo PedidoItem de Laravel.
///
/// Guarda el nombre y el precio "congelados" al momento de la compra (así el
/// pedido no cambia aunque el producto suba de precio después).
class PedidoItem {
  final int id;
  final int productoId;
  final String nombreProducto;
  final int cantidad;
  final double precioUnitario;

  /// Opciones de personalización elegidas, ya "congeladas" como texto
  /// (p. ej. "Sin cebolla · Tamaño grande · Tocineta extra"). `null` si no hubo.
  final String? detalle;

  const PedidoItem({
    required this.id,
    required this.productoId,
    required this.nombreProducto,
    required this.cantidad,
    required this.precioUnitario,
    this.detalle,
  });

  double get subtotal => precioUnitario * cantidad;

  factory PedidoItem.fromJson(Map<String, dynamic> json) {
    final detalleRaw = (json['detalle'] ?? json['notas'])?.toString();
    return PedidoItem(
      id: toInt(json['id']),
      productoId: toInt(json['producto_id']),
      nombreProducto:
          (json['nombre_producto'] ?? json['producto']?['nombre'] ?? '')
              .toString(),
      cantidad: toInt(json['cantidad']),
      precioUnitario: toDouble(json['precio_unitario']),
      detalle: (detalleRaw == null || detalleRaw.isEmpty) ? null : detalleRaw,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'producto_id': productoId,
        'nombre_producto': nombreProducto,
        'cantidad': cantidad,
        'precio_unitario': precioUnitario,
        'detalle': detalle,
      };
}
