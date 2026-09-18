import 'dart:math' as math;
import 'dart:ui' as ui;

import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';

/// Fondo de **constelaciones** animado y sutil: estrellas que derivan lentamente,
/// titilan, y se unen con líneas tenues cuando están cerca (efecto red/red de
/// constelación). Pensado para ir detrás de contenido sobre el fondo casi negro.
///
/// Es todo pintura (sin imágenes ni red), así que funciona sin internet. El
/// tiempo continuo lo da un [Stopwatch] (el bucle del controller no salta). El
/// [child] se dibuja encima.
class FondoEstrellas extends StatefulWidget {
  final Widget? child;

  /// Cantidad de estrellas. Más = más denso (y un poco más costoso de pintar).
  final int cantidad;

  const FondoEstrellas({super.key, this.child, this.cantidad = 60});

  @override
  State<FondoEstrellas> createState() => _FondoEstrellasState();
}

class _FondoEstrellasState extends State<FondoEstrellas>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  final Stopwatch _reloj = Stopwatch();
  late final List<_Estrella> _estrellas;

  @override
  void initState() {
    super.initState();
    _estrellas = _generar();
    _reloj.start();
    _ctrl = AnimationController(vsync: this, duration: const Duration(seconds: 1))
      ..repeat();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  /// Genera las estrellas una sola vez, con semilla fija (estables entre frames).
  List<_Estrella> _generar() {
    final rnd = math.Random(42);
    return List.generate(widget.cantidad, (_) {
      // Algunas estrellas tienen tinte de marca; la mayoría blancas.
      final color = rnd.nextDouble() < 0.22 ? Brand.c400 : Colors.white;
      final ang = rnd.nextDouble() * 2 * math.pi;
      final vel = 0.004 + rnd.nextDouble() * 0.010; // muy lento (frac/seg)
      return _Estrella(
        x: rnd.nextDouble(),
        y: rnd.nextDouble(),
        vx: math.cos(ang) * vel,
        vy: math.sin(ang) * vel,
        radio: 0.7 + rnd.nextDouble() * 1.8, // 0.7..2.5 px
        brillo: 0.35 + rnd.nextDouble() * 0.5, // 0.35..0.85
        twVel: 0.3 + rnd.nextDouble() * 0.9, // titileo (ciclos/seg)
        twFase: rnd.nextDouble(),
        color: color,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.background,
      child: Stack(
        fit: StackFit.expand,
        children: [
          RepaintBoundary(
            child: AnimatedBuilder(
              animation: _ctrl,
              builder: (context, _) {
                return CustomPaint(
                  size: Size.infinite,
                  painter: _ConstelacionPainter(
                    estrellas: _estrellas,
                    t: _reloj.elapsedMilliseconds / 1000.0,
                  ),
                );
              },
            ),
          ),
          if (widget.child != null) widget.child!,
        ],
      ),
    );
  }
}

/// Una estrella: posición y velocidad en fracción de pantalla (0..1), tamaño,
/// brillo base y parámetros de titileo.
class _Estrella {
  final double x;
  final double y;
  final double vx;
  final double vy;
  final double radio;
  final double brillo;
  final double twVel;
  final double twFase;
  final Color color;

  const _Estrella({
    required this.x,
    required this.y,
    required this.vx,
    required this.vy,
    required this.radio,
    required this.brillo,
    required this.twVel,
    required this.twFase,
    required this.color,
  });
}

class _ConstelacionPainter extends CustomPainter {
  final List<_Estrella> estrellas;
  final double t;

  const _ConstelacionPainter({required this.estrellas, required this.t});

  /// Distancia máxima (px) para unir dos estrellas con una línea.
  static const double _maxLinea = 110;

  double _wrap(double v) {
    final m = v % 1.0;
    return m < 0 ? m + 1.0 : m;
  }

  @override
  void paint(Canvas canvas, Size size) {
    // Posición de cada estrella en este frame (deriva continua, con wrap).
    final puntos = <Offset>[];
    for (final e in estrellas) {
      final px = _wrap(e.x + e.vx * t) * size.width;
      final py = _wrap(e.y + e.vy * t) * size.height;
      puntos.add(Offset(px, py));
    }

    // Líneas de constelación entre estrellas cercanas (opacidad por distancia).
    final linePaint = Paint()..strokeWidth = 0.8;
    for (var i = 0; i < puntos.length; i++) {
      for (var j = i + 1; j < puntos.length; j++) {
        final d = (puntos[i] - puntos[j]).distance;
        if (d < _maxLinea) {
          final a = (1 - d / _maxLinea) * 0.16;
          linePaint.color = Colors.white.withValues(alpha: a);
          canvas.drawLine(puntos[i], puntos[j], linePaint);
        }
      }
    }

    // Estrellas (con leve titileo y un halo en las más grandes).
    for (var i = 0; i < estrellas.length; i++) {
      final e = estrellas[i];
      final tw = 0.55 + 0.45 * math.sin(t * e.twVel + e.twFase * 2 * math.pi);
      final alpha = (e.brillo * tw).clamp(0.0, 1.0);

      if (e.radio > 1.7) {
        final halo = Paint()
          ..shader = ui.Gradient.radial(puntos[i], e.radio * 4, [
            e.color.withValues(alpha: alpha * 0.35),
            e.color.withValues(alpha: 0),
          ]);
        canvas.drawCircle(puntos[i], e.radio * 4, halo);
      }

      canvas.drawCircle(
        puntos[i],
        e.radio,
        Paint()..color = e.color.withValues(alpha: alpha),
      );
    }
  }

  @override
  bool shouldRepaint(_ConstelacionPainter old) => true;
}
