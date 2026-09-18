import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';

/// Muestra una estrella + el número de rating (ej. ★ 4.7).
class RatingEstrella extends StatelessWidget {
  final double rating;
  final double size;

  const RatingEstrella({super.key, required this.rating, this.size = 16});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(Icons.star_rounded, color: AppColors.star, size: size),
        const SizedBox(width: 3),
        Text(
          rating.toStringAsFixed(1),
          style: TextStyle(
            fontSize: size - 2,
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
        ),
      ],
    );
  }
}
