import 'dart:ui';

import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/utils/formato.dart';
import '../../../models/restaurante.dart';
import '../../shared/widgets/imagen_placeholder.dart';
import '../../shared/widgets/tarjeta_presionable.dart';

/// Tarjeta de un restaurante en el listado del cliente.
class RestauranteCard extends StatelessWidget {
  final Restaurante restaurante;
  final VoidCallback? onTap;

  /// Mantener presionado: abre un resumen del restaurante (funciona incluso si
  /// está cerrado, para poder ver su información).
  final VoidCallback? onLongPress;

  const RestauranteCard({
    super.key,
    required this.restaurante,
    this.onTap,
    this.onLongPress,
  });

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    final cerrado = !restaurante.abierto;

    return TarjetaPresionable(
      onTap: cerrado ? null : onTap,
      onLongPress: onLongPress,
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: AppColors.border),
          // Sombra doble: drop suave a media altura + glow profundo abajo
          // para que la tarjeta flote sobre el fondo de la lista.
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.35),
              blurRadius: 24,
              offset: const Offset(0, 12),
            ),
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.2),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Imagen + badges
            Stack(
              children: [
                ImagenPlaceholder(
                  texto: restaurante.nombre,
                  categoria: restaurante.categoria,
                  url: restaurante.imagenUrl,
                  height: 156,
                ),
                // Degradado de 3 stops: limpio arriba para que la foto luzca,
                // semi al medio y denso abajo para los badges.
                const Positioned.fill(
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        stops: [0.0, 0.55, 1.0],
                        colors: [
                          Colors.transparent,
                          Color(0x33000000),
                          Color(0x99000000),
                        ],
                      ),
                    ),
                  ),
                ),
                if (cerrado)
                  Positioned.fill(
                    child: Container(
                      color: Colors.black.withValues(alpha: 0.55),
                      alignment: Alignment.center,
                      child: const _Pildora(
                        texto: 'CERRADO',
                        color: Colors.white,
                        fondo: Colors.black54,
                      ),
                    ),
                  ),
                // Rating (arriba derecha)
                Positioned(
                  top: 10,
                  right: 10,
                  child: _BadgeOscuro(
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.star_rounded,
                            color: AppColors.star, size: 15),
                        const SizedBox(width: 3),
                        Text(restaurante.rating.toStringAsFixed(1),
                            style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w700,
                                fontSize: 12.5)),
                      ],
                    ),
                  ),
                ),
                // Tiempo de entrega (abajo izquierda)
                Positioned(
                  bottom: 10,
                  left: 10,
                  child: _BadgeOscuro(
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.schedule,
                            color: Colors.white, size: 14),
                        const SizedBox(width: 4),
                        Text('${restaurante.tiempoEntregaMin} min',
                            style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                                fontSize: 12.5)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 13, 14, 14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(restaurante.nombre,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: text.titleMedium?.copyWith(
                                fontWeight: FontWeight.w800,
                                letterSpacing: -0.3)),
                      ),
                      if (!cerrado)
                        const _PuntoEstado(
                            color: AppColors.success, texto: 'Abierto'),
                    ],
                  ),
                  const SizedBox(height: 5),
                  Text(restaurante.descripcion,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: text.bodySmall?.copyWith(
                          color: AppColors.textSecondary,
                          height: 1.35)),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      _ChipInfo(
                        icon: Icons.delivery_dining,
                        texto: restaurante.costoDomicilio == 0
                            ? 'Envío gratis'
                            : formatoPesos(restaurante.costoDomicilio),
                        destacar: restaurante.costoDomicilio == 0,
                      ),
                      const SizedBox(width: 8),
                      _ChipInfo(
                        icon: Icons.category_outlined,
                        texto: restaurante.categoria,
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Badge "de vidrio" sobre la imagen: blur + fondo negro translúcido +
/// borde sutil blanco. Más premium que un negro plano.
class _BadgeOscuro extends StatelessWidget {
  final Widget child;
  const _BadgeOscuro({required this.child});

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(10),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 8, sigmaY: 8),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
          decoration: BoxDecoration(
            color: Colors.black.withValues(alpha: 0.45),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
                color: Colors.white.withValues(alpha: 0.14), width: 0.8),
          ),
          child: child,
        ),
      ),
    );
  }
}

class _Pildora extends StatelessWidget {
  final String texto;
  final Color color;
  final Color fondo;
  const _Pildora(
      {required this.texto, required this.color, required this.fondo});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
      decoration: BoxDecoration(
        color: fondo,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(texto,
          style: TextStyle(
              color: color, fontWeight: FontWeight.w800, letterSpacing: 1)),
    );
  }
}

class _PuntoEstado extends StatelessWidget {
  final Color color;
  final String texto;
  const _PuntoEstado({required this.color, required this.texto});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 7,
          height: 7,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        const SizedBox(width: 5),
        Text(texto,
            style: TextStyle(
                color: color, fontSize: 12, fontWeight: FontWeight.w600)),
      ],
    );
  }
}

class _ChipInfo extends StatelessWidget {
  final IconData icon;
  final String texto;

  /// Resalta el chip en verde — útil para "Envío gratis" como gancho visual.
  final bool destacar;

  const _ChipInfo({
    required this.icon,
    required this.texto,
    this.destacar = false,
  });

  @override
  Widget build(BuildContext context) {
    final color = destacar ? AppColors.success : AppColors.textSecondary;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: destacar
            ? AppColors.success.withValues(alpha: 0.14)
            : AppColors.surfaceVariant,
        borderRadius: BorderRadius.circular(10),
        border: destacar
            ? Border.all(color: AppColors.success.withValues(alpha: 0.35))
            : null,
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: color),
          const SizedBox(width: 5),
          Text(texto,
              style: TextStyle(
                  fontSize: 12,
                  color: color,
                  fontWeight: destacar ? FontWeight.w700 : FontWeight.w500)),
        ],
      ),
    );
  }
}
