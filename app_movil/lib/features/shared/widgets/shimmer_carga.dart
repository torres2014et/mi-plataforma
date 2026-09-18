import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';

/// Caja con efecto **shimmer**: un brillo que recorre un placeholder gris,
/// estilo Rappi/iFood, para los estados de carga. Cada caja anima sola; varias
/// dentro del mismo árbol son baratas (solo pintan un gradiente que se desliza).
class ShimmerCaja extends StatefulWidget {
  final double? width;
  final double height;
  final BorderRadius? radio;
  final bool circular;

  const ShimmerCaja({
    super.key,
    this.width,
    required this.height,
    this.radio,
    this.circular = false,
  });

  @override
  State<ShimmerCaja> createState() => _ShimmerCajaState();
}

class _ShimmerCajaState extends State<ShimmerCaja>
    with SingleTickerProviderStateMixin {
  late final AnimationController _c;

  @override
  void initState() {
    super.initState();
    _c = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1300),
    )..repeat();
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _c,
      builder: (context, _) {
        return Container(
          // Sin ancho explícito (y no circular) llena el ancho disponible; un
          // Container vacío sin ancho colapsaría a 0.
          width: widget.circular ? widget.width : (widget.width ?? double.infinity),
          height: widget.height,
          decoration: BoxDecoration(
            shape: widget.circular ? BoxShape.circle : BoxShape.rectangle,
            borderRadius: widget.circular
                ? null
                : (widget.radio ?? BorderRadius.circular(10)),
            gradient: LinearGradient(
              colors: const [
                AppColors.surfaceVariant,
                Color(0xFF3A3A40), // brillo (zinc un poco más claro)
                AppColors.surfaceVariant,
              ],
              stops: const [0.25, 0.5, 0.75],
              transform: _DeslizarGradiente(_c.value),
            ),
          ),
        );
      },
    );
  }
}

/// Desliza el gradiente horizontalmente de izquierda a derecha según [t] (0..1).
class _DeslizarGradiente extends GradientTransform {
  final double t;
  const _DeslizarGradiente(this.t);

  @override
  Matrix4? transform(Rect bounds, {TextDirection? textDirection}) {
    // De -ancho a +ancho para que el brillo entre y salga del lado.
    return Matrix4.translationValues((t * 2 - 1) * bounds.width, 0, 0);
  }
}
