import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

/// Envuelve contenido tocable y lo **hunde levemente al presionar** (misma
/// micro-interacción que [BrandButton]). Reemplaza al `InkWell` cuando se quiere
/// el feedback de escala en vez del ripple. Si [onTap] es null, no reacciona
/// (p. ej. una tarjeta deshabilitada).
///
/// Soporta [onLongPress] (mantener presionado): al dispararse da una leve
/// vibración háptica, útil para abrir un resumen/acciones sin salir de la lista.
class TarjetaPresionable extends StatefulWidget {
  final Widget child;
  final VoidCallback? onTap;
  final VoidCallback? onLongPress;

  /// Escala al estar presionado (1 = sin cambio).
  final double escala;

  const TarjetaPresionable({
    super.key,
    required this.child,
    this.onTap,
    this.onLongPress,
    this.escala = 0.97,
  });

  @override
  State<TarjetaPresionable> createState() => _TarjetaPresionableState();
}

class _TarjetaPresionableState extends State<TarjetaPresionable> {
  bool _presionado = false;

  void _set(bool v) {
    if (_presionado != v) setState(() => _presionado = v);
  }

  @override
  Widget build(BuildContext context) {
    final activo = widget.onTap != null || widget.onLongPress != null;
    return GestureDetector(
      onTapDown: activo ? (_) => _set(true) : null,
      onTapUp: activo ? (_) => _set(false) : null,
      onTapCancel: activo ? () => _set(false) : null,
      onTap: widget.onTap,
      onLongPress: widget.onLongPress == null
          ? null
          : () {
              _set(false);
              HapticFeedback.mediumImpact();
              widget.onLongPress!();
            },
      child: AnimatedScale(
        scale: _presionado ? widget.escala : 1,
        duration: const Duration(milliseconds: 110),
        curve: Curves.easeOut,
        child: widget.child,
      ),
    );
  }
}
