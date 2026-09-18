import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';
import '../../../models/estado_pedido.dart';

/// Timeline vertical del avance del pedido (pendiente → … → entregado).
///
/// Marca como completados los pasos hasta el estado actual. Si el pedido fue
/// cancelado, muestra un estado final rojo.
class EstadoTimeline extends StatelessWidget {
  final EstadoPedido estadoActual;

  const EstadoTimeline({super.key, required this.estadoActual});

  @override
  Widget build(BuildContext context) {
    if (estadoActual == EstadoPedido.cancelado) {
      return _Paso(
        estado: EstadoPedido.cancelado,
        completado: true,
        esActual: true,
        esUltimo: true,
        color: AppColors.error,
      );
    }

    final indiceActual = EstadoPedido.flujo.indexOf(estadoActual);

    return Column(
      children: [
        for (int i = 0; i < EstadoPedido.flujo.length; i++)
          _Paso(
            estado: EstadoPedido.flujo[i],
            completado: i <= indiceActual,
            esActual: i == indiceActual,
            esUltimo: i == EstadoPedido.flujo.length - 1,
            color: AppColors.brand,
          ),
      ],
    );
  }
}

class _Paso extends StatelessWidget {
  final EstadoPedido estado;
  final bool completado;
  final bool esActual;
  final bool esUltimo;
  final Color color;

  const _Paso({
    required this.estado,
    required this.completado,
    required this.esActual,
    required this.esUltimo,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    final activo = completado ? color : AppColors.border;

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Columna del indicador (punto + línea)
          Column(
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: completado
                      ? color.withValues(alpha: 0.15)
                      : AppColors.surface,
                  shape: BoxShape.circle,
                  border: Border.all(color: activo, width: 2),
                ),
                child: Icon(estado.icono, size: 17, color: activo),
              ),
              if (!esUltimo)
                Expanded(
                  child: Container(
                    width: 2,
                    color: completado ? color : AppColors.border,
                  ),
                ),
            ],
          ),
          const SizedBox(width: 14),
          // Texto del paso
          Padding(
            padding: EdgeInsets.only(top: 6, bottom: esUltimo ? 0 : 18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  estado.label,
                  style: TextStyle(
                    fontWeight: esActual ? FontWeight.w800 : FontWeight.w600,
                    color: completado
                        ? AppColors.textPrimary
                        : AppColors.textMuted,
                  ),
                ),
                if (esActual && estado != EstadoPedido.entregado)
                  const Text('En curso…',
                      style: TextStyle(
                          fontSize: 12, color: AppColors.textSecondary)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
