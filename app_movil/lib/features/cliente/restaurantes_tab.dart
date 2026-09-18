import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/router/app_router.dart';
import '../../core/theme/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/restaurante_providers.dart';
import '../shared/widgets/aparicion_animada.dart';
import '../shared/widgets/shimmer_carga.dart';
import 'widgets/restaurante_card.dart';
import 'widgets/restaurante_resumen_sheet.dart';

/// Tab "Restaurantes": cabecera, buscador, chips de categoría y la lista con
/// spinner de carga.
class RestaurantesTab extends ConsumerWidget {
  const RestaurantesTab({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider);
    final asyncRestaurantes = ref.watch(restaurantesFiltradosProvider);
    final text = Theme.of(context).textTheme;

    return SafeArea(
      bottom: false,
      child: RefreshIndicator(
        color: AppColors.brand,
        onRefresh: () async => ref.refresh(restaurantesProvider.future),
        child: CustomScrollView(
          slivers: [
            // Encabezado: ubicación eyebrow + saludo + avatar
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 18, 20, 12),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Eyebrow de ubicación: mayúsculas pequeñas para
                          // jerarquizar — el saludo queda como protagonista.
                          Row(
                            children: [
                              const Icon(Icons.location_on_rounded,
                                  size: 14, color: AppColors.brand),
                              const SizedBox(width: 4),
                              Text('UBATÉ, CUNDINAMARCA',
                                  style: text.bodySmall?.copyWith(
                                      color: AppColors.textMuted,
                                      fontWeight: FontWeight.w700,
                                      fontSize: 11,
                                      letterSpacing: 0.8)),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text('Hola, ${user?.nombre ?? ''} 👋',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: text.titleLarge?.copyWith(
                                  fontWeight: FontWeight.w800,
                                  fontSize: 24,
                                  letterSpacing: -0.4)),
                        ],
                      ),
                    ),
                    const SizedBox(width: 12),
                    _AvatarMini(iniciales: user?.iniciales ?? '?'),
                  ],
                ),
              ),
            ),

            // Buscador
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 4, 20, 8),
                child: TextField(
                  onChanged: (v) => ref
                      .read(busquedaRestauranteProvider.notifier)
                      .actualizar(v),
                  decoration: const InputDecoration(
                    hintText: 'Buscar restaurante o categoría',
                    prefixIcon: Icon(Icons.search),
                  ),
                ),
              ),
            ),

            // Chips de categoría
            const SliverToBoxAdapter(child: _CategoriaChips()),

            // Contenido (carga / error / lista)
            asyncRestaurantes.when(
              loading: () => const _SkeletonRestaurantes(),
              error: (e, _) => SliverFillRemaining(
                hasScrollBody: false,
                child: _Mensaje(
                  icono: Icons.error_outline,
                  titulo: 'Ups, algo falló',
                  detalle: '$e',
                ),
              ),
              data: (restaurantes) {
                if (restaurantes.isEmpty) {
                  return const SliverFillRemaining(
                    hasScrollBody: false,
                    child: _Mensaje(
                      icono: Icons.search_off,
                      titulo: 'Sin resultados',
                      detalle: 'No encontramos restaurantes para tu búsqueda.',
                    ),
                  );
                }
                return SliverPadding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                  sliver: SliverList.separated(
                    itemCount: restaurantes.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 14),
                    itemBuilder: (context, i) {
                      final r = restaurantes[i];
                      return AparicionAnimada(
                        delay: AparicionAnimada.escalonado(i),
                        child: RestauranteCard(
                          restaurante: r,
                          onTap: () => context.push(Rutas.menu(r.id)),
                          onLongPress: () => mostrarResumenRestaurante(
                            context,
                            restaurante: r,
                            onVerMenu: () => context.push(Rutas.menu(r.id)),
                          ),
                        ),
                      );
                    },
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}

/// Avatar circular pequeño con iniciales (decorativo, en la cabecera).
class _AvatarMini extends StatelessWidget {
  final String iniciales;
  const _AvatarMini({required this.iniciales});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 46,
      width: 46,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Brand.c400, Brand.c600],
        ),
        shape: BoxShape.circle,
        border: Border.all(
            color: Colors.white.withValues(alpha: 0.18), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Brand.c500.withValues(alpha: 0.45),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.25),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Text(iniciales,
          style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w800,
              fontSize: 15,
              letterSpacing: -0.2)),
    );
  }
}

/// Fila horizontal de chips de categoría para filtrar la lista.
class _CategoriaChips extends ConsumerWidget {
  const _CategoriaChips();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final categorias = ref.watch(categoriasRestauranteProvider);
    final seleccionada = ref.watch(categoriaRestauranteProvider);
    if (categorias.isEmpty) return const SizedBox.shrink();

    // 'Todos' (valor '') + las categorías reales.
    final items = ['', ...categorias];

    return SizedBox(
      height: 44,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.fromLTRB(20, 4, 20, 4),
        itemCount: items.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, i) {
          final cat = items[i];
          final label = cat.isEmpty ? 'Todos' : cat;
          final activo =
              cat.isEmpty ? seleccionada.isEmpty : seleccionada == cat;
          return GestureDetector(
            onTap: () {
              HapticFeedback.selectionClick();
              ref
                  .read(categoriaRestauranteProvider.notifier)
                  .seleccionar(cat);
            },
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 220),
              curve: Curves.easeOut,
              padding: const EdgeInsets.symmetric(horizontal: 18),
              alignment: Alignment.center,
              decoration: BoxDecoration(
                gradient: activo
                    ? const LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [Brand.c400, Brand.c600],
                      )
                    : null,
                color: activo ? null : AppColors.surfaceVariant,
                borderRadius: BorderRadius.circular(999),
                border: Border.all(
                  color: activo
                      ? Colors.white.withValues(alpha: 0.18)
                      : AppColors.border,
                ),
                boxShadow: activo
                    ? [
                        BoxShadow(
                          color: Brand.c500.withValues(alpha: 0.4),
                          blurRadius: 14,
                          offset: const Offset(0, 5),
                        ),
                      ]
                    : null,
              ),
              child: Text(
                label,
                style: TextStyle(
                  color: activo ? Colors.white : AppColors.textSecondary,
                  fontWeight: activo ? FontWeight.w800 : FontWeight.w600,
                  fontSize: 13.5,
                  letterSpacing: -0.1,
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}

/// Placeholders con shimmer mientras cargan los restaurantes (estilo Rappi).
class _SkeletonRestaurantes extends StatelessWidget {
  const _SkeletonRestaurantes();

  @override
  Widget build(BuildContext context) {
    return SliverPadding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      sliver: SliverList.separated(
        itemCount: 4,
        separatorBuilder: (_, __) => const SizedBox(height: 14),
        itemBuilder: (_, __) => const _SkeletonCard(),
      ),
    );
  }
}

/// Silueta de una [RestauranteCard]: imagen + dos líneas + chips.
class _SkeletonCard extends StatelessWidget {
  const _SkeletonCard();

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.border),
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const ShimmerCaja(height: 150, radio: BorderRadius.zero),
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 14, 14, 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                ShimmerCaja(height: 16, width: 170),
                SizedBox(height: 10),
                ShimmerCaja(height: 12, width: 230),
                SizedBox(height: 16),
                Row(
                  children: [
                    ShimmerCaja(height: 26, width: 90),
                    SizedBox(width: 8),
                    ShimmerCaja(height: 26, width: 80),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Mensaje extends StatelessWidget {
  final IconData icono;
  final String titulo;
  final String detalle;

  const _Mensaje({
    required this.icono,
    required this.titulo,
    required this.detalle,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(32),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icono, size: 48, color: AppColors.textMuted),
          const SizedBox(height: 12),
          Text(titulo,
              style: Theme.of(context)
                  .textTheme
                  .titleMedium
                  ?.copyWith(fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text(detalle,
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppColors.textSecondary)),
        ],
      ),
    );
  }
}
