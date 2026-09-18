import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';

/// Control − [cantidad] + para sumar/restar unidades de un producto.
class CantidadStepper extends StatelessWidget {
  final int cantidad;
  final VoidCallback onMas;
  final VoidCallback onMenos;
  final double size;

  const CantidadStepper({
    super.key,
    required this.cantidad,
    required this.onMas,
    required this.onMenos,
    this.size = 32,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _boton(Icons.remove, onMenos),
          SizedBox(
            width: size,
            child: Text(
              '$cantidad',
              textAlign: TextAlign.center,
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
          ),
          _boton(Icons.add, onMas),
        ],
      ),
    );
  }

  Widget _boton(IconData icono, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: SizedBox(
        width: size,
        height: size,
        child: Icon(icono, size: 18, color: AppColors.brand),
      ),
    );
  }
}
