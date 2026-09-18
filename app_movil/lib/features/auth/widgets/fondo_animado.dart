import 'dart:math' as math;
import 'dart:ui';

import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';

/// Fondo del login/registro: **mesh gradient animado** (estilo iOS 18 / Apple
/// Vision Pro). Cinco blobs blureados de paleta amplia — naranja brand,
/// naranja oscuro, magenta, púrpura profundo y ámbar — derivan en patrones
/// independientes de seno/coseno y se mezclan como una pintura líquida sobre
/// un fondo casi negro. Cinematográfico, sin distraer al formulario.
///
/// Es todo pintura (sin imágenes ni red), así que funciona offline. El [child]
/// se dibuja encima.
class FondoAnimado extends StatefulWidget {
  final Widget child;

  const FondoAnimado({super.key, required this.child});

  @override
  State<FondoAnimado> createState() => _FondoAnimadoState();
}

class _FondoAnimadoState extends State<FondoAnimado>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;

  // Paleta del mesh: dos tonos de marca + magenta + púrpura para profundidad
  // fría + ámbar para highlights cálidos.
  static const _naranja = Brand.c500;
  static const _naranjaOscuro = Brand.c700;
  static const _magenta = Color(0xFFC1276B);
  static const _purpura = Color(0xFF4A1D7A);
  static const _ambar = Brand.c300;

  @override
  void initState() {
    super.initState();
    // Ciclo largo: los blobs derivan lentamente, no llaman la atención.
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 18),
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
          // Mesh: blobs detrás de un blur fuerte para que se mezclen.
          RepaintBoundary(
            child: ImageFiltered(
              imageFilter: ImageFilter.blur(sigmaX: 90, sigmaY: 90),
              child: AnimatedBuilder(
                animation: _ctrl,
                builder: (context, _) {
                  final t = _ctrl.value * 2 * math.pi;
                  return Stack(
                    fit: StackFit.expand,
                    children: [
                      // Blob 1 — naranja principal, arriba derecha
                      _blob(
                        Alignment(
                          0.75 + 0.35 * math.sin(t * 0.85),
                          -0.85 + 0.30 * math.cos(t * 0.7),
                        ),
                        _naranja,
                        520,
                        0.70,
                      ),
                      // Blob 2 — magenta, izquierda media (movimiento opuesto)
                      _blob(
                        Alignment(
                          -0.80 + 0.45 * math.cos(t * 0.6),
                          -0.10 + 0.40 * math.sin(t * 0.9),
                        ),
                        _magenta,
                        500,
                        0.60,
                      ),
                      // Blob 3 — púrpura profundo, abajo izquierda
                      _blob(
                        Alignment(
                          -0.60 + 0.55 * math.sin(t * 1.1 + 0.7),
                          0.85 + 0.25 * math.cos(t * 0.75),
                        ),
                        _purpura,
                        560,
                        0.65,
                      ),
                      // Blob 4 — naranja oscuro, abajo derecha
                      _blob(
                        Alignment(
                          0.70 + 0.35 * math.cos(t * 0.95 + 1.4),
                          0.70 + 0.30 * math.sin(t * 0.8),
                        ),
                        _naranjaOscuro,
                        480,
                        0.55,
                      ),
                      // Blob 5 — ámbar, highlight central (más pequeño y rápido)
                      _blob(
                        Alignment(
                          0.10 + 0.65 * math.sin(t * 1.3),
                          0.05 + 0.55 * math.cos(t * 1.15),
                        ),
                        _ambar,
                        360,
                        0.42,
                      ),
                    ],
                  );
                },
              ),
            ),
          ),
          // Velo oscuro encima para que el formulario tenga contraste sin
          // perder la riqueza del mesh.
          IgnorePointer(
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black.withValues(alpha: 0.15),
                    Colors.black.withValues(alpha: 0.05),
                    Colors.black.withValues(alpha: 0.25),
                  ],
                  stops: const [0.0, 0.5, 1.0],
                ),
              ),
            ),
          ),
          widget.child,
        ],
      ),
    );
  }

  /// Un blob: círculo con [RadialGradient] que se desvanece en los bordes.
  /// El blur global se encarga de mezclarlo con los demás.
  Widget _blob(Alignment align, Color color, double d, double op) {
    return Align(
      alignment: align,
      child: Container(
        width: d,
        height: d,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          gradient: RadialGradient(
            colors: [
              color.withValues(alpha: op),
              color.withValues(alpha: 0),
            ],
          ),
        ),
      ),
    );
  }
}
