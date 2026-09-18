import 'package:flutter/material.dart';

/// Hace aparecer su [child] con un fundido + leve deslizamiento hacia arriba.
/// Con [delay] se escalonan varios (p. ej. los ítems de una lista) para el
/// efecto premium de "cascada". Solo anima una vez, al montarse.
///
/// Helper [escalonado] para calcular el [delay] por índice con un tope, así las
/// listas largas no acumulan retrasos enormes.
class AparicionAnimada extends StatefulWidget {
  final Widget child;
  final Duration delay;
  final Duration duracion;

  /// Desplazamiento vertical inicial (px) desde donde sube al aparecer.
  final double desplazamiento;

  const AparicionAnimada({
    super.key,
    required this.child,
    this.delay = Duration.zero,
    this.duracion = const Duration(milliseconds: 420),
    this.desplazamiento = 18,
  });

  /// Retraso escalonado para el ítem [index] (60 ms por ítem, tope ~6 ítems)
  /// para que la cascada se sienta sin que los de abajo tarden demasiado.
  static Duration escalonado(int index) =>
      Duration(milliseconds: (index.clamp(0, 6)) * 60);

  @override
  State<AparicionAnimada> createState() => _AparicionAnimadaState();
}

class _AparicionAnimadaState extends State<AparicionAnimada>
    with SingleTickerProviderStateMixin {
  late final AnimationController _c;
  late final Animation<double> _curva;

  @override
  void initState() {
    super.initState();
    _c = AnimationController(vsync: this, duration: widget.duracion);
    _curva = CurvedAnimation(parent: _c, curve: Curves.easeOutCubic);
    if (widget.delay == Duration.zero) {
      _c.forward();
    } else {
      Future.delayed(widget.delay, () {
        if (mounted) _c.forward();
      });
    }
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _curva,
      builder: (context, child) {
        return Opacity(
          opacity: _curva.value.clamp(0, 1),
          child: Transform.translate(
            offset: Offset(0, widget.desplazamiento * (1 - _curva.value)),
            child: child,
          ),
        );
      },
      child: widget.child,
    );
  }
}
