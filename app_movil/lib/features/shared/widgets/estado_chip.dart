import 'package:flutter/material.dart';

import '../../../models/estado_pedido.dart';
import 'status_badge.dart';

/// Chip con el ícono + etiqueta del estado de un pedido, en forma de píldora.
///
/// Mapea cada estado al color de la web:
/// verde=entregado · naranja=pendiente/preparando · azul=confirmado/en camino ·
/// rojo=cancelado. Render delegado en [StatusBadge].
class EstadoChip extends StatelessWidget {
  final EstadoPedido estado;

  const EstadoChip({super.key, required this.estado});

  BadgeKind get _kind => switch (estado) {
        EstadoPedido.entregado => BadgeKind.green,
        EstadoPedido.cancelado => BadgeKind.red,
        EstadoPedido.confirmado || EstadoPedido.enCamino => BadgeKind.blue,
        EstadoPedido.pendiente ||
        EstadoPedido.enPreparacion =>
          BadgeKind.orange,
      };

  @override
  Widget build(BuildContext context) {
    return StatusBadge(estado.label, kind: _kind, icono: estado.icono);
  }
}
