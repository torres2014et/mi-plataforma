import 'package:flutter/material.dart';

/// Estados de un pedido. Mapean exactamente a los valores del backend
/// (en snake_case): pendiente → confirmado → en_preparacion → en_camino →
/// entregado | cancelado.
enum EstadoPedido {
  pendiente,
  confirmado,
  enPreparacion,
  enCamino,
  entregado,
  cancelado;

  /// Parsea el valor snake_case que envía Laravel.
  static EstadoPedido fromApi(String? value) => switch (value) {
        'pendiente' => EstadoPedido.pendiente,
        'confirmado' => EstadoPedido.confirmado,
        'en_preparacion' => EstadoPedido.enPreparacion,
        'en_camino' => EstadoPedido.enCamino,
        'entregado' => EstadoPedido.entregado,
        'cancelado' => EstadoPedido.cancelado,
        _ => EstadoPedido.pendiente,
      };

  /// Valor snake_case para enviar al backend.
  String get apiValue => switch (this) {
        EstadoPedido.pendiente => 'pendiente',
        EstadoPedido.confirmado => 'confirmado',
        EstadoPedido.enPreparacion => 'en_preparacion',
        EstadoPedido.enCamino => 'en_camino',
        EstadoPedido.entregado => 'entregado',
        EstadoPedido.cancelado => 'cancelado',
      };

  /// Etiqueta legible para la UI.
  String get label => switch (this) {
        EstadoPedido.pendiente => 'Pendiente',
        EstadoPedido.confirmado => 'Confirmado',
        EstadoPedido.enPreparacion => 'En preparación',
        EstadoPedido.enCamino => 'En camino',
        EstadoPedido.entregado => 'Entregado',
        EstadoPedido.cancelado => 'Cancelado',
      };

  IconData get icono => switch (this) {
        EstadoPedido.pendiente => Icons.schedule,
        EstadoPedido.confirmado => Icons.check_circle_outline,
        EstadoPedido.enPreparacion => Icons.restaurant,
        EstadoPedido.enCamino => Icons.delivery_dining,
        EstadoPedido.entregado => Icons.done_all,
        EstadoPedido.cancelado => Icons.cancel_outlined,
      };

  /// Flujo normal de un pedido (sirve para dibujar el timeline más adelante).
  static const List<EstadoPedido> flujo = [
    EstadoPedido.pendiente,
    EstadoPedido.confirmado,
    EstadoPedido.enPreparacion,
    EstadoPedido.enCamino,
    EstadoPedido.entregado,
  ];

  bool get esFinal =>
      this == EstadoPedido.entregado || this == EstadoPedido.cancelado;

  bool get estaActivo => !esFinal;

  /// Siguiente estado en el [flujo] normal, o null si ya es final o no
  /// pertenece al flujo (p. ej. `cancelado`). Lo usa el domiciliario para
  /// avanzar el pedido paso a paso.
  EstadoPedido? get siguiente {
    final i = flujo.indexOf(this);
    if (i == -1 || i + 1 >= flujo.length) return null;
    return flujo[i + 1];
  }
}
