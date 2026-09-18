import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';
import '../../../models/user_role.dart';

/// Toggle de dos opciones (Cliente / Domiciliario) para elegir el rol con el
/// que se inicia sesión o se registra. Compartido por login y registro.
class SelectorRol extends StatelessWidget {
  final UserRole rol;
  final ValueChanged<UserRole> onChanged;

  const SelectorRol({super.key, required this.rol, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          _opcion(UserRole.cliente, Icons.person_outline, 'Cliente'),
          _opcion(UserRole.domiciliario, Icons.delivery_dining, 'Domiciliario'),
        ],
      ),
    );
  }

  Widget _opcion(UserRole valor, IconData icono, String label) {
    final activo = rol == valor;
    return Expanded(
      child: GestureDetector(
        onTap: () => onChanged(valor),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 150),
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: activo ? AppColors.brand : Colors.transparent,
            borderRadius: BorderRadius.circular(11),
            boxShadow: activo
                ? [
                    BoxShadow(
                      color: AppColors.brand.withValues(alpha: 0.4),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ]
                : null,
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icono,
                  size: 18,
                  color: activo ? Colors.white : AppColors.textSecondary),
              const SizedBox(width: 6),
              Flexible(
                child: Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontWeight: FontWeight.w600,
                    color: activo ? Colors.white : AppColors.textSecondary,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
