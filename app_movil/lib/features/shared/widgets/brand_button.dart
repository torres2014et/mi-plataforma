import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';

/// Botón primario de marca, igual que el `.btn-primary` de la web: gradiente
/// naranja, glow, texto blanco bold y un sutil efecto de escala al presionar.
///
/// Además, cuando está habilitado y a ancho completo, un **brillo deslizante**
/// (shimmer) cruza el botón cada pocos segundos para darle el aire premium de
/// los CTAs comerciales. Todo con animación nativa, sin dependencias.
///
/// Soporta ícono, estado de carga y ancho completo, para reemplazar los CTAs
/// principales sin perder funcionalidad.
class BrandButton extends StatefulWidget {
  final String label;

  /// Si es null (o [cargando] es true), el botón se ve deshabilitado.
  final VoidCallback? onPressed;
  final IconData? icono;
  final bool cargando;

  /// Ocupa todo el ancho disponible (CTA de pantalla). Por defecto true.
  final bool expandir;

  const BrandButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.icono,
    this.cargando = false,
    this.expandir = true,
  });

  @override
  State<BrandButton> createState() => _BrandButtonState();
}

class _BrandButtonState extends State<BrandButton>
    with SingleTickerProviderStateMixin {
  bool _presionado = false;

  /// Controla el brillo que cruza el botón. El sweep ocurre en la primera
  /// mitad del ciclo (Interval) y luego descansa, dando un "glint" periódico.
  late final AnimationController _shineCtrl;
  late final Animation<double> _shine;

  @override
  void initState() {
    super.initState();
    _shineCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 2600),
    )..repeat();
    _shine = CurvedAnimation(
      parent: _shineCtrl,
      curve: const Interval(0, 0.5, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _shineCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final habilitado = widget.onPressed != null && !widget.cargando;
    final mostrarBrillo = habilitado && widget.expandir;

    return GestureDetector(
      onTapDown: habilitado ? (_) => setState(() => _presionado = true) : null,
      onTapUp: habilitado ? (_) => setState(() => _presionado = false) : null,
      onTapCancel:
          habilitado ? () => setState(() => _presionado = false) : null,
      onTap: habilitado ? widget.onPressed : null,
      child: AnimatedScale(
        scale: _presionado ? 0.96 : 1,
        duration: const Duration(milliseconds: 100),
        child: AnimatedOpacity(
          opacity: habilitado ? 1 : 0.5,
          duration: const Duration(milliseconds: 150),
          // clipBehavior none: el glow (BoxShadow) se dibuja fuera del botón y
          // no debe recortarse; el brillo se recorta por su propio ClipRRect.
          child: Stack(
            clipBehavior: Clip.none,
            children: [
              _boton(habilitado),
              if (mostrarBrillo)
                Positioned.fill(child: _brilloDeslizante()),
            ],
          ),
        ),
      ),
    );
  }

  /// El botón en sí: gradiente + glow + contenido.
  Widget _boton(bool habilitado) {
    return Container(
      width: widget.expandir ? double.infinity : null,
      height: 54,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Brand.c500, Brand.c600],
        ),
        borderRadius: BorderRadius.circular(12),
        boxShadow: habilitado
            ? [
                BoxShadow(
                  color: Brand.c500.withValues(alpha: 0.4),
                  blurRadius: 20,
                  offset: const Offset(0, 6),
                ),
              ]
            : null,
      ),
      child: widget.cargando
          ? const SizedBox(
              height: 22,
              width: 22,
              child: CircularProgressIndicator(
                  strokeWidth: 2.4, color: Colors.white),
            )
          : Row(
              mainAxisSize:
                  widget.expandir ? MainAxisSize.max : MainAxisSize.min,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                if (widget.icono != null) ...[
                  Icon(widget.icono, color: Colors.white, size: 20),
                  const SizedBox(width: 8),
                ],
                Flexible(
                  child: Text(
                    widget.label,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w700,
                      fontSize: 16,
                    ),
                  ),
                ),
              ],
            ),
    );
  }

  /// Banda de luz diagonal que cruza el botón. Recortada al radio del botón.
  Widget _brilloDeslizante() {
    return ClipRRect(
      borderRadius: BorderRadius.circular(12),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final ancho = constraints.maxWidth;
          const banda = 56.0;
          return AnimatedBuilder(
            animation: _shine,
            builder: (context, _) {
              // Va de fuera (izquierda) a fuera (derecha) en cada sweep.
              final dx = -banda + _shine.value * (ancho + banda * 2);
              return Stack(
                children: [
                  Positioned(
                    left: dx,
                    top: -40,
                    child: Transform.rotate(
                      angle: -0.45, // ~-26°, glint diagonal
                      child: Container(
                        width: banda,
                        height: 140,
                        decoration: const BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              Colors.transparent,
                              Color(0x4DFFFFFF), // blanco ~30%
                              Colors.transparent,
                            ],
                            stops: [0.0, 0.5, 1.0],
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              );
            },
          );
        },
      ),
    );
  }
}
