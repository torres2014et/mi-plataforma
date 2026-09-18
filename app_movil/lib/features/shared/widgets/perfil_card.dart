import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';
import '../../../models/user.dart';

/// Tarjeta con los datos del usuario en sesión: avatar con iniciales (o foto),
/// nombre, rol y datos de contacto. La usan el perfil del cliente y el home
/// del domiciliario para que "se vean los datos" de quien entró.
class PerfilCard extends StatelessWidget {
  final User user;
  const PerfilCard({super.key, required this.user});

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        children: [
          Row(
            children: [
              _Avatar(user: user),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(user.nombre,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: text.titleLarge
                            ?.copyWith(fontWeight: FontWeight.w800)),
                    const SizedBox(height: 6),
                    _RolPill(label: user.rol.label),
                  ],
                ),
              ),
            ],
          ),
          const Divider(height: 28),
          _InfoRow(icono: Icons.email_outlined, valor: user.email),
          if (user.telefono != null && user.telefono!.isNotEmpty) ...[
            const SizedBox(height: 12),
            _InfoRow(icono: Icons.phone_outlined, valor: user.telefono!),
          ],
        ],
      ),
    );
  }
}

class _Avatar extends StatelessWidget {
  final User user;
  const _Avatar({required this.user});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 64,
      width: 64,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppColors.brand, AppColors.brandDark],
        ),
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(
            color: AppColors.brand.withValues(alpha: 0.45),
            blurRadius: 18,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Text(
        user.iniciales,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 24,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class _RolPill extends StatelessWidget {
  final String label;
  const _RolPill({required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: AppColors.brand.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: AppColors.brand.withValues(alpha: 0.4)),
      ),
      child: Text(
        label,
        style: const TextStyle(
          color: AppColors.brand,
          fontSize: 12,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icono;
  final String valor;
  const _InfoRow({required this.icono, required this.valor});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icono, size: 18, color: AppColors.textSecondary),
        const SizedBox(width: 10),
        Expanded(
          child: Text(valor,
              style: const TextStyle(color: AppColors.textSecondary)),
        ),
      ],
    );
  }
}
