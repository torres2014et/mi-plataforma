import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';

/// Logo de la marca: insignia con el degradado naranja y glow que lleva un
/// **scooter de domicilios** grande y centrado. Es vectorial (Material Icons +
/// pintura): escala sin pixelarse y respeta la paleta de [AppColors]. Lo usan
/// login y registro.
class LogoMarca extends StatelessWidget {
  /// Lado de la insignia en px lógicos.
  final double size;
  const LogoMarca({super.key, this.size = 76});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppColors.brandLight, AppColors.brandDark],
        ),
        borderRadius: BorderRadius.circular(size * 0.3),
        boxShadow: [
          BoxShadow(
            color: AppColors.brand.withValues(alpha: 0.5),
            blurRadius: size * 0.32,
            offset: Offset(0, size * 0.11),
          ),
        ],
      ),
      // Brillo sutil arriba para dar volumen a la insignia.
      foregroundDecoration: BoxDecoration(
        borderRadius: BorderRadius.circular(size * 0.3),
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [
            Colors.white.withValues(alpha: 0.18),
            Colors.transparent,
          ],
        ),
      ),
      child: Icon(Icons.delivery_dining,
          color: Colors.white, size: size * 0.66),
    );
  }
}
