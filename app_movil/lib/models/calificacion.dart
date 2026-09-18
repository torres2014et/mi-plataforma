import '../core/utils/parse.dart';

/// Calificación que el cliente deja a un pedido entregado.
/// Mapea al modelo Calificacion de Laravel.
class Calificacion {
  final int id;
  final int pedidoId;
  final int restauranteId;

  /// Estrellas de 1 a 5.
  final int estrellas;
  final String comentario;
  final DateTime? createdAt;

  const Calificacion({
    required this.id,
    required this.pedidoId,
    required this.restauranteId,
    required this.estrellas,
    this.comentario = '',
    this.createdAt,
  });

  factory Calificacion.fromJson(Map<String, dynamic> json) {
    return Calificacion(
      id: toInt(json['id']),
      pedidoId: toInt(json['pedido_id']),
      restauranteId: toInt(json['restaurante_id']),
      estrellas: toInt(json['estrellas']),
      comentario: (json['comentario'] ?? '').toString(),
      createdAt: toDateOrNull(json['created_at']),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'pedido_id': pedidoId,
        'restaurante_id': restauranteId,
        'estrellas': estrellas,
        'comentario': comentario,
        'created_at': createdAt?.toIso8601String(),
      };
}
