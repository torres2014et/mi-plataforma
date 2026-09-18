import 'dart:math' as math;
import 'dart:ui';

import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';

/// Fondo "aurora" animado y **sutil**, pensado para ir detrás de contenido
/// dentro de la app (no del login): orbes de marca que derivan lentamente con
/// un desenfoque fuerte sobre el fondo casi negro. El [child] va encima.
///
/// Es todo pintura (sin imágenes ni red), así que funciona sin internet.
class FondoAurora extends StatefulWidget {
  final Widget? child;

  const FondoAurora({super.key, this.child});

  @override
  State<FondoAurora> createState() => _FondoAuroraState();
}

class _FondoAuroraState extends State<FondoAurora>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 14),
    )..repeat();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.background,
      child: Stack(
        fit: StackFit.expand,
        children: [
          RepaintBoundary(
            child: ImageFiltered(
              imageFilter: ImageFilter.blur(sigmaX: 55, sigmaY: 55),
              child: AnimatedBuilder(
                animation: _ctrl,
                builder: (context, _) {
                  final t = _ctrl.value * 2 * math.pi;
                  return Stack(
                    fit: StackFit.expand,
                    children: [
                      _orbe(
                        Alignment(0.85 * math.sin(t), -0.7 + 0.5 * math.cos(t)),
                        Brand.c500,
                        360,
                        0.45,
                      ),
                      _orbe(
                        Alignment(-0.8 + 0.6 * math.cos(t * 0.8),
                            -0.3 + 0.55 * math.sin(t * 0.9)),
                        Brand.c700,
                        420,
                        0.40,
                      ),
                      _orbe(
                        Alignment(
                            0.9 * math.sin(t * 1.2 + 1), 0.7 + 0.35 * math.cos(t)),
                        Brand.c600,
                        300,
                        0.38,
                      ),
                    ],
                  );
                },
              ),
            ),
          ),
          if (widget.child != null) widget.child!,
        ],
      ),
    );
  }

  Widget _orbe(Alignment align, Color color, double d, double op) {
    return Align(
      alignment: align,
      child: Container(
        width: d,
        height: d,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          gradient: RadialGradient(
            colors: [color.withValues(alpha: op), color.withValues(alpha: 0)],
          ),
        ),
      ),
    );
  }
}
