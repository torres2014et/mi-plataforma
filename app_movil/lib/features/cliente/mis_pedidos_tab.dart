import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/router/app_router.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/formato.dart';
import '../../models/estado_pedido.dart';
import '../../models/pedido.dart';
import '../../providers/pedido_providers.dart';
import '../shared/widgets/aparicion_animada.dart';
import '../shared/widgets/estado_chip.dart';
import '../shared/widgets/fondo_aurora.dart';
import '../shared/widgets/imagen_placeholder.dart';
import '../shared/widgets/shimmer_carga.dart';
import '../shared/widgets/tarjeta_presionable.dart';

/// Filtros del listado de pedidos.
enum _Filtro { todos, enCurso, entregados }

/// Tab "Mis pedidos": filtro segmentado + lista de pedidos del cliente.
class MisPedidosTab extends ConsumerStatefulWidget {
  const MisPedidosTab({super.key});

  @override
  ConsumerState<MisPedidosTab> createState() => _MisPedidosTabState();
}

class _MisPedidosTabState extends ConsumerState<MisPedidosTab> {
  _Filtro _filtro = _Filtro.todos;

  bool _coincide(Pedido p) => switch (_filtro) {
        _Filtro.todos => true,
        _Filtro.enCurso => p.estado.estaActivo,
        _Filtro.entregados => p.estado == EstadoPedido.entregado,
      };

  @override
  Widget build(BuildContext context) {
    final asyncPedidos = ref.watch(misPedidosProvider);
    final text = Theme.of(context).textTheme;

    return FondoAurora(
      child: SafeArea(
        bottom: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 12),
            child: Text('Mis pedidos',
                style: text.titleLarge?.copyWith(fontWeight: FontWeight.w800)),
          ),
          _Segmentos(
            actual: _filtro,
            onCambio: (f) => setState(() => _filtro = f),
          ),
          const SizedBox(height: 4),
          Expanded(
            child: asyncPedidos.when(
              loading: () => const _SkeletonPedidos(),
              error: (e, _) => Center(
                child: Text('Error: $e',
                    style: const TextStyle(color: AppColors.error)),
              ),
              data: (pedidos) {
                final lista = pedidos.where(_coincide).toList();
                return RefreshIndicator(
                  color: AppColors.brand,
                  onRefresh: () async =>
                      ref.refresh(misPedidosProvider.future),
                  child: lista.isEmpty
                      ? _Vacio(filtro: _filtro)
                      : ListView.separated(
                          padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                          itemCount: lista.length,
                          separatorBuilder: (_, __) =>
                              const SizedBox(height: 12),
                          itemBuilder: (context, i) => AparicionAnimada(
                            delay: AparicionAnimada.escalonado(i),
                            child: _PedidoCard(pedido: lista[i]),
                          ),
                        ),
                );
              },
            ),
          ),
        ],
        ),
      ),
    );
  }
}

/// Control segmentado de tres opciones.
class _Segmentos extends StatelessWidget {
  final _Filtro actual;
  final ValueChanged<_Filtro> onCambio;
  const _Segmentos({required this.actual, required this.onCambio});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 38,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 20),
        children: [
          _chip('Todos', _Filtro.todos),
          const SizedBox(width: 8),
          _chip('En curso', _Filtro.enCurso),
          const SizedBox(width: 8),
          _chip('Entregados', _Filtro.entregados),
        ],
      ),
    );
  }

  Widget _chip(String label, _Filtro filtro) {
    final activo = actual == filtro;
    return GestureDetector(
      onTap: () => onCambio(filtro),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 16),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: activo ? AppColors.brand : AppColors.surfaceVariant,
          borderRadius: BorderRadius.circular(999),
          border: Border.all(color: activo ? AppColors.brand : AppColors.border),
        ),
        child: Text(label,
            style: TextStyle(
              color: activo ? Colors.white : AppColors.textSecondary,
              fontWeight: FontWeight.w600,
              fontSize: 13.5,
            )),
      ),
    );
  }
}

class _PedidoCard extends ConsumerWidget {
  final Pedido pedido;
  const _PedidoCard({required this.pedido});

  String get _resumenItems =>
      pedido.items.map((i) => '${i.cantidad}× ${i.nombreProducto}').join(' · ');

  String get _fecha {
    final d = pedido.createdAt;
    if (d == null) return '';
    final now = DateTime.now();
    final hoy = d.year == now.year && d.month == now.month && d.day == now.day;
    final hh = d.hour.toString().padLeft(2, '0');
    final mm = d.minute.toString().padLeft(2, '0');
    return hoy
        ? 'Hoy $hh:$mm'
        : '${d.day.toString().padLeft(2, '0')}/${d.month.toString().padLeft(2, '0')} · $hh:$mm';
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final text = Theme.of(context).textTheme;
    // Estado en vivo: la tarjeta avanza sola conforme el pedido cambia de
    // estado (cae al estado del snapshot mientras el stream emite el primero).
    final estado =
        ref.watch(pedidoEnVivoProvider(pedido.id)).asData?.value.estado ??
            pedido.estado;
    final flujo = EstadoPedido.flujo;
    final idx = flujo.indexOf(estado);
    final progreso = idx >= 0 ? (idx + 1) / flujo.length : 0.0;

    return TarjetaPresionable(
      onTap: () => context.push(Rutas.pedido(pedido.id)),
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.22),
              blurRadius: 16,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        clipBehavior: Clip.antiAlias,
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: SizedBox(
                      height: 54,
                      width: 54,
                      child: ImagenPlaceholder(
                        texto: pedido.restauranteNombre,
                        url: pedido.restauranteImagenUrl,
                        height: 54,
                        iconSize: 24,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(pedido.restauranteNombre,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: text.titleMedium?.copyWith(
                                      fontWeight: FontWeight.w700)),
                            ),
                            const SizedBox(width: 8),
                            EstadoChip(estado: estado),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(_resumenItems,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: text.bodySmall
                                ?.copyWith(color: AppColors.textSecondary)),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              // Barra de progreso para pedidos en curso. Anima el avance al
              // cambiar de estado para que se note el progreso en vivo.
              if (estado.estaActivo) ...[
                ClipRRect(
                  borderRadius: BorderRadius.circular(999),
                  child: TweenAnimationBuilder<double>(
                    tween: Tween(begin: 0, end: progreso),
                    duration: const Duration(milliseconds: 500),
                    curve: Curves.easeOut,
                    builder: (_, valor, __) => LinearProgressIndicator(
                      value: valor,
                      minHeight: 5,
                      backgroundColor: AppColors.surfaceVariant,
                      valueColor:
                          const AlwaysStoppedAnimation(AppColors.brand),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
              ],
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('$_fecha · #${pedido.id}',
                      style: text.bodySmall
                          ?.copyWith(color: AppColors.textMuted)),
                  Text(formatoPesos(pedido.total),
                      style: text.titleMedium
                          ?.copyWith(fontWeight: FontWeight.w800)),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Placeholders con shimmer mientras cargan los pedidos.
class _SkeletonPedidos extends StatelessWidget {
  const _SkeletonPedidos();

  @override
  Widget build(BuildContext context) {
    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      itemCount: 5,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (_, __) => Container(
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border),
        ),
        padding: const EdgeInsets.all(14),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: const [
            ShimmerCaja(height: 54, width: 54, radio: null),
            SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ShimmerCaja(height: 15, width: 150),
                  SizedBox(height: 10),
                  ShimmerCaja(height: 12, width: 200),
                  SizedBox(height: 14),
                  ShimmerCaja(height: 12, width: 120),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Vacio extends StatelessWidget {
  final _Filtro filtro;
  const _Vacio({required this.filtro});

  String get _texto => switch (filtro) {
        _Filtro.enCurso => 'No tienes pedidos en curso',
        _Filtro.entregados => 'Aún no tienes entregas',
        _Filtro.todos => 'Aún no tienes pedidos',
      };

  @override
  Widget build(BuildContext context) {
    return ListView(
      // ListView para que el RefreshIndicator/scroll siga disponible.
      children: [
        const SizedBox(height: 120),
        const Icon(Icons.receipt_long_outlined,
            size: 56, color: AppColors.textMuted),
        const SizedBox(height: 12),
        Center(
          child: Text(_texto,
              style: const TextStyle(color: AppColors.textSecondary)),
        ),
      ],
    );
  }
}
