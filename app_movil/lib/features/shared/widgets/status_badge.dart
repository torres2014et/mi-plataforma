import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';

/// Categorías de color de un badge, igual que los `.badge-*` de la web.
enum BadgeKind { green, orange, blue, red, gray }

/// Badge de estado tipo píldora: fondo translúcido + borde + texto del mismo
/// color. Reutilizable para cualquier estado (pedidos, disponibilidad, etc.).
///
/// Los colores salen de [AppColors] para no hardcodear; ver [EstadoChip] para
/// el mapeo concreto de los estados de pedido.
class StatusBadge extends StatelessWidget {
  final String text;
  final BadgeKind kind;
  final IconData? icono;

  const StatusBadge(
    this.text, {
    super.key,
    this.kind = BadgeKind.gray,
    this.icono,
  });

  Color get _color => switch (kind) {
        BadgeKind.green => AppColors.success,
        BadgeKind.orange => AppColors.warning,
        BadgeKind.blue => AppColors.info,
        BadgeKind.red => AppColors.error,
        BadgeKind.gray => AppColors.textSecondary,
      };

  @override
  Widget build(BuildContext context) {
    final c = _color;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: c.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: c.withValues(alpha: 0.4)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icono != null) ...[
            Icon(icono, size: 13, color: c),
            const SizedBox(width: 4),
          ],
          Text(
            text,
            style: TextStyle(
              color: c,
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}
