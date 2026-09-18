import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/theme/app_colors.dart';
import '../../models/estado_pedido.dart';
import '../../models/pedido.dart';
import '../../models/user.dart';
import '../../providers/auth_provider.dart';
import '../../providers/pedido_providers.dart';
import '../shared/widgets/fondo_aurora.dart';

/// Perfil del cliente: cabecera "hero" con avatar sobre un fondo aurora en
/// movimiento, estadísticas reales de sus pedidos, secciones de cuenta y el
/// botón de cerrar sesión.
class PerfilTab extends ConsumerWidget {
  const PerfilTab({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider);

    return Scaffold(
      body: FondoAurora(
        child: user == null
            ? const SizedBox.shrink()
            : SafeArea(
                bottom: false,
                child: ListView(
                  padding: const EdgeInsets.fromLTRB(20, 8, 20, 32),
                  children: [
                    _Encabezado(user: user),
                    const SizedBox(height: 20),
                    const _EstadisticasCard(),
                    const SizedBox(height: 24),
                    const _Seccion(
                      titulo: 'Cuenta',
                      opciones: [
                        _Opcion(Icons.person_outline, 'Editar perfil'),
                        _Opcion(Icons.location_on_outlined, 'Mis direcciones'),
                        _Opcion(Icons.credit_card_outlined, 'Métodos de pago'),
                      ],
                    ),
                    const SizedBox(height: 18),
                    const _Seccion(
                      titulo: 'Preferencias',
                      opciones: [
                        _Opcion(Icons.notifications_outlined, 'Notificaciones',
                            conSwitch: true),
                        _Opcion(Icons.language_outlined, 'Idioma',
                            trailingTexto: 'Español'),
                      ],
                    ),
                    const SizedBox(height: 18),
                    const _Seccion(
                      titulo: 'Soporte',
                      opciones: [
                        _Opcion(Icons.help_outline, 'Centro de ayuda'),
                        _Opcion(Icons.description_outlined,
                            'Términos y condiciones'),
                        _Opcion(Icons.info_outline, 'Acerca de'),
                      ],
                    ),
                    const SizedBox(height: 24),
                    _BotonCerrarSesion(
                      onTap: () => ref.read(authProvider.notifier).logout(),
                    ),
                    const SizedBox(height: 16),
                    Center(
                      child: Text(
                        'Domicilios Ubaté · v1.0.0 (demo)',
                        style: TextStyle(
                            color: AppColors.textMuted, fontSize: 12),
                      ),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}

/// Cabecera con avatar grande, nombre, rol y correo (sobre la aurora).
class _Encabezado extends StatelessWidget {
  final User user;
  const _Encabezado({required this.user});

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    return Column(
      children: [
        const SizedBox(height: 12),
        Container(
          height: 96,
          width: 96,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [AppColors.brand, AppColors.brandDark],
            ),
            shape: BoxShape.circle,
            border: Border.all(color: Colors.white.withValues(alpha: 0.15), width: 3),
            boxShadow: [
              BoxShadow(
                color: AppColors.brand.withValues(alpha: 0.5),
                blurRadius: 28,
                offset: const Offset(0, 10),
              ),
            ],
          ),
          child: Text(
            user.iniciales,
            style: const TextStyle(
                color: Colors.white, fontSize: 34, fontWeight: FontWeight.w800),
          ),
        ),
        const SizedBox(height: 16),
        Text(user.nombre,
            textAlign: TextAlign.center,
            style: text.headlineSmall?.copyWith(fontWeight: FontWeight.w800)),
        const SizedBox(height: 8),
        _RolPill(label: user.rol.label),
        const SizedBox(height: 8),
        Text(user.email,
            style: text.bodyMedium?.copyWith(color: AppColors.textSecondary)),
      ],
    );
  }
}

class _RolPill extends StatelessWidget {
  final String label;
  const _RolPill({required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
      decoration: BoxDecoration(
        color: AppColors.brand.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: AppColors.brand.withValues(alpha: 0.4)),
      ),
      child: Text(
        label,
        style: const TextStyle(
            color: AppColors.brand, fontSize: 13, fontWeight: FontWeight.w700),
      ),
    );
  }
}

/// Tarjeta de estadísticas con datos reales de los pedidos del cliente.
class _EstadisticasCard extends ConsumerWidget {
  const _EstadisticasCard();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final pedidos =
        ref.watch(misPedidosProvider).asData?.value ?? const <Pedido>[];
    final total = pedidos.length;
    final enCurso = pedidos.where((p) => p.estado.estaActivo).length;
    final entregados =
        pedidos.where((p) => p.estado == EstadoPedido.entregado).length;

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 18),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          _Stat(valor: total, etiqueta: 'Pedidos'),
          _Divisor(),
          _Stat(valor: enCurso, etiqueta: 'En curso'),
          _Divisor(),
          _Stat(valor: entregados, etiqueta: 'Entregados'),
        ],
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  final int valor;
  final String etiqueta;
  const _Stat({required this.valor, required this.etiqueta});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(
        children: [
          Text('$valor',
              style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                  color: AppColors.brand)),
          const SizedBox(height: 2),
          Text(etiqueta,
              style: const TextStyle(
                  color: AppColors.textSecondary, fontSize: 12.5)),
        ],
      ),
    );
  }
}

class _Divisor extends StatelessWidget {
  @override
  Widget build(BuildContext context) =>
      Container(width: 1, height: 34, color: AppColors.border);
}

/// Un grupo de opciones bajo un título, todas dentro de una misma tarjeta.
class _Seccion extends StatelessWidget {
  final String titulo;
  final List<_Opcion> opciones;
  const _Seccion({required this.titulo, required this.opciones});

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 8),
          child: Text(titulo,
              style: text.titleSmall?.copyWith(
                  fontWeight: FontWeight.w700,
                  color: AppColors.textSecondary)),
        ),
        Container(
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.border),
          ),
          child: Column(
            children: [
              for (var i = 0; i < opciones.length; i++) ...[
                opciones[i],
                if (i != opciones.length - 1)
                  Divider(
                      height: 1,
                      thickness: 1,
                      indent: 56,
                      color: AppColors.border),
              ],
            ],
          ),
        ),
      ],
    );
  }
}

/// Fila de opción: ícono en círculo tintado + título + acción a la derecha
/// (chevron, texto o switch). Cosmética en Fase 1.
class _Opcion extends StatefulWidget {
  final IconData icono;
  final String titulo;
  final bool conSwitch;
  final String? trailingTexto;

  const _Opcion(this.icono, this.titulo,
      {this.conSwitch = false, this.trailingTexto});

  @override
  State<_Opcion> createState() => _OpcionState();
}

class _OpcionState extends State<_Opcion> {
  bool _activo = true;

  @override
  Widget build(BuildContext context) {
    Widget trailing;
    if (widget.conSwitch) {
      trailing = Switch(
        value: _activo,
        activeThumbColor: AppColors.brand,
        onChanged: (v) => setState(() => _activo = v),
      );
    } else if (widget.trailingTexto != null) {
      trailing = Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(widget.trailingTexto!,
              style: const TextStyle(color: AppColors.textMuted)),
          const SizedBox(width: 4),
          const Icon(Icons.chevron_right, color: AppColors.textMuted),
        ],
      );
    } else {
      trailing = const Icon(Icons.chevron_right, color: AppColors.textMuted);
    }

    return InkWell(
      borderRadius: BorderRadius.circular(16),
      onTap: widget.conSwitch
          ? () => setState(() => _activo = !_activo)
          : () => ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Disponible próximamente')),
              ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        child: Row(
          children: [
            Container(
              height: 36,
              width: 36,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: AppColors.brand.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(widget.icono, size: 20, color: AppColors.brand),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(widget.titulo,
                  style: const TextStyle(
                      fontWeight: FontWeight.w600, fontSize: 15)),
            ),
            trailing,
          ],
        ),
      ),
    );
  }
}

class _BotonCerrarSesion extends StatelessWidget {
  final VoidCallback onTap;
  const _BotonCerrarSesion({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return OutlinedButton.icon(
      onPressed: onTap,
      icon: const Icon(Icons.logout, color: AppColors.error),
      label: const Text('Cerrar sesión',
          style: TextStyle(color: AppColors.error, fontWeight: FontWeight.w700)),
      style: OutlinedButton.styleFrom(
        backgroundColor: AppColors.error.withValues(alpha: 0.08),
        side: BorderSide(color: AppColors.error.withValues(alpha: 0.5)),
      ),
    );
  }
}
