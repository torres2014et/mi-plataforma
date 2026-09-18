import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/router/app_router.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/formato.dart';
import '../../models/producto.dart';
import '../../models/restaurante.dart';
import '../../providers/carrito_provider.dart';
import '../../providers/restaurante_providers.dart';
import '../shared/widgets/aparicion_animada.dart';
import '../shared/widgets/fondo_estrellas.dart';
import '../shared/widgets/imagen_placeholder.dart';
import '../shared/widgets/tarjeta_presionable.dart';
import 'widgets/cantidad_stepper.dart';
import 'widgets/producto_sheet.dart';

/// Menú de un restaurante: cabecera colapsable, productos agrupados por
/// categoría con control de cantidad y barra inferior que lleva al checkout.
class MenuScreen extends ConsumerWidget {
  final int restauranteId;

  const MenuScreen({super.key, required this.restauranteId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final asyncRestaurante =
        ref.watch(restauranteDetalleProvider(restauranteId));
    final carrito = ref.watch(carritoProvider);

    return Scaffold(
      body: FondoEstrellas(
        child: asyncRestaurante.when(
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (e, _) => Center(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Text('No se pudo cargar el menú: $e',
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: AppColors.error)),
            ),
          ),
          data: (restaurante) => _Contenido(restaurante: restaurante),
        ),
      ),
      bottomNavigationBar: carrito.isEmpty
          ? null
          : _BarraCarrito(
              cantidad: carrito.cantidadTotal,
              total: carrito.total,
              onTap: () => context.push(Rutas.checkout),
            ),
    );
  }
}

class _Contenido extends StatelessWidget {
  final Restaurante restaurante;
  const _Contenido({required this.restaurante});

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    final categorias = restaurante.categoriasMenu;

    return CustomScrollView(
      slivers: [
        // Cabecera colapsable con imagen + scrim de 3 stops, botones flotantes
        // tipo "vidrio" y la píldora de estado superpuesta sobre la foto.
        SliverAppBar(
          expandedHeight: 260,
          pinned: true,
          stretch: true,
          backgroundColor: AppColors.background,
          foregroundColor: Colors.white,
          leadingWidth: 56,
          leading: Padding(
            padding: const EdgeInsets.only(left: 12, top: 8, bottom: 8),
            child: _BotonVidrio(
              icono: Icons.arrow_back_rounded,
              onTap: () => context.pop(),
            ),
          ),
          actions: [
            Padding(
              padding: const EdgeInsets.only(right: 12, top: 8, bottom: 8),
              child: _BotonVidrio(
                icono: Icons.favorite_border_rounded,
                onTap: () {},
              ),
            ),
          ],
          flexibleSpace: FlexibleSpaceBar(
            stretchModes: const [
              StretchMode.zoomBackground,
              StretchMode.fadeTitle,
            ],
            titlePadding:
                const EdgeInsets.only(left: 64, bottom: 14, right: 64),
            title: Text(restaurante.nombre,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 16,
                    letterSpacing: -0.2)),
            background: Stack(
              fit: StackFit.expand,
              children: [
                ImagenPlaceholder(
                  texto: restaurante.nombre,
                  categoria: restaurante.categoria,
                  url: restaurante.imagenUrl,
                  iconSize: 72,
                ),
                // Scrim de 3 stops: un toque arriba (legibilidad de iconos),
                // limpio al medio (foto respira) y denso abajo (texto blanco).
                const DecoratedBox(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      stops: [0.0, 0.45, 1.0],
                      colors: [
                        Color(0x40000000),
                        Color(0x1A000000),
                        Color(0xCC000000),
                      ],
                    ),
                  ),
                ),
                // Píldora "Abierto/Cerrado" por encima del título del header.
                Positioned(
                  left: 20,
                  bottom: 58,
                  child: _EstadoApertura(abierto: restaurante.abierto),
                ),
              ],
            ),
          ),
        ),

        // Info del restaurante (descripción + chips de meta)
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 4),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(restaurante.descripcion,
                    style: const TextStyle(
                        color: AppColors.textSecondary, height: 1.4)),
                const SizedBox(height: 14),
                // Meta del restaurante: categoría + rating + tiempo + envío.
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    _MetaChip(
                      icon: Icons.category_outlined,
                      texto: restaurante.categoria,
                    ),
                    _MetaChip(
                      icon: Icons.star_rounded,
                      texto: restaurante.rating.toStringAsFixed(1),
                      colorIcono: AppColors.star,
                    ),
                    _MetaChip(
                      icon: Icons.schedule,
                      texto: '${restaurante.tiempoEntregaMin} min',
                    ),
                    _MetaChip(
                      icon: Icons.delivery_dining,
                      texto: restaurante.costoDomicilio == 0
                          ? 'Envío gratis'
                          : formatoPesos(restaurante.costoDomicilio),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                // Dirección del restaurante
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.location_on_outlined,
                        size: 18, color: AppColors.textSecondary),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(restaurante.direccion,
                          style: const TextStyle(
                              color: AppColors.textSecondary, fontSize: 13.5)),
                    ),
                  ],
                ),
                const Divider(height: 32),
              ],
            ),
          ),
        ),

        // Secciones por categoría con conteo y barra de marca con gradiente.
        for (final categoria in categorias) ...[
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 10),
              child: Row(
                children: [
                  Container(
                    width: 4,
                    height: 20,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [Brand.c400, Brand.c600],
                      ),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Text(categoria,
                      style: text.titleMedium?.copyWith(
                          fontWeight: FontWeight.w800,
                          letterSpacing: -0.2)),
                  const SizedBox(width: 8),
                  Text(
                    '· ${restaurante.productos.where((p) => p.categoria == categoria).length}',
                    style: const TextStyle(
                        color: AppColors.textMuted,
                        fontWeight: FontWeight.w600,
                        fontSize: 13),
                  ),
                ],
              ),
            ),
          ),
          SliverList.list(
            children: [
              for (final (i, p) in restaurante.productos
                  .where((p) => p.categoria == categoria)
                  .toList()
                  .indexed)
                AparicionAnimada(
                  delay: AparicionAnimada.escalonado(i),
                  child: _ProductoTile(producto: p, restaurante: restaurante),
                ),
            ],
          ),
        ],
        const SliverToBoxAdapter(child: SizedBox(height: 24)),
      ],
    );
  }
}

/// Píldora de estado: verde "Abierto" / roja "Cerrado".
class _EstadoApertura extends StatelessWidget {
  final bool abierto;
  const _EstadoApertura({required this.abierto});

  @override
  Widget build(BuildContext context) {
    final color = abierto ? AppColors.success : AppColors.error;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.4)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 8,
            height: 8,
            decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          ),
          const SizedBox(width: 7),
          Text(abierto ? 'Abierto' : 'Cerrado',
              style: TextStyle(
                  fontSize: 13, color: color, fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}

class _MetaChip extends StatelessWidget {
  final IconData icon;
  final String texto;
  final Color? colorIcono;
  const _MetaChip({required this.icon, required this.texto, this.colorIcono});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
      decoration: BoxDecoration(
        color: AppColors.surfaceVariant,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 15, color: colorIcono ?? AppColors.textSecondary),
          const SizedBox(width: 6),
          Text(texto,
              style: const TextStyle(
                  fontSize: 13,
                  color: AppColors.textPrimary,
                  fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

class _ProductoTile extends ConsumerWidget {
  final Producto producto;
  final Restaurante restaurante;

  const _ProductoTile({required this.producto, required this.restaurante});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cantidad = ref.watch(
      carritoProvider.select((c) => c.cantidadDe(producto.id)),
    );

    void abrirSheet() {
      HapticFeedback.lightImpact();
      mostrarProductoSheet(
        context,
        ref,
        producto: producto,
        restaurante: restaurante,
      );
    }

    return TarjetaPresionable(
      onTap: abrirSheet,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 10, 20, 10),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(producto.nombre,
                      style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 15,
                          letterSpacing: -0.1)),
                  const SizedBox(height: 4),
                  Text(producto.descripcion,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                          color: AppColors.textSecondary,
                          fontSize: 13,
                          height: 1.35)),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Text(formatoPesos(producto.precio),
                          style: const TextStyle(
                              color: AppColors.brand,
                              fontWeight: FontWeight.w800,
                              fontSize: 15.5,
                              letterSpacing: -0.2)),
                      if (producto.personalizable) ...[
                        const SizedBox(width: 8),
                        const _SelloPersonalizable(),
                      ],
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 14),
            // Miniatura + control flotante (botón + / stepper).
            SizedBox(
              width: 96,
              height: 104,
              child: Stack(
                clipBehavior: Clip.none,
                alignment: Alignment.topCenter,
                children: [
                  // Sombra suave debajo de la miniatura para darle peso.
                  Container(
                    height: 96,
                    width: 96,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.28),
                          blurRadius: 14,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                  ),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(14),
                    child: ImagenPlaceholder(
                      texto: producto.nombre,
                      categoria: producto.categoria,
                      url: producto.imagenUrl,
                      height: 96,
                      iconSize: 30,
                    ),
                  ),
                  // Insignia con la cantidad total en el carrito (todas las
                  // líneas de este producto), útil para productos personalizables.
                  if (cantidad > 0)
                    Positioned(
                      top: 4,
                      right: 4,
                      child: _ContadorBadge(cantidad: cantidad),
                    ),
                  Positioned(
                    bottom: 0,
                    child: _buildControl(context, ref, cantidad, abrirSheet),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// El producto personalizable siempre abre el sheet; el simple usa el
  /// +/stepper rápido directamente sobre el carrito.
  Widget _buildControl(
    BuildContext context,
    WidgetRef ref,
    int cantidad,
    VoidCallback abrirSheet,
  ) {
    if (producto.personalizable) {
      return _BotonAgregar(onTap: abrirSheet);
    }
    if (cantidad == 0) {
      return _BotonAgregar(onTap: () => _agregarSimple(context, ref));
    }
    return Material(
      color: Colors.transparent,
      child: CantidadStepper(
        size: 30,
        cantidad: cantidad,
        onMas: () => ref.read(carritoProvider.notifier).incrementar(producto.id),
        onMenos: () =>
            ref.read(carritoProvider.notifier).decrementar(producto.id),
      ),
    );
  }

  Future<void> _agregarSimple(BuildContext context, WidgetRef ref) async {
    final ok = await confirmarCarritoOtroRestaurante(context, ref, restaurante);
    if (!ok) return;
    ref.read(carritoProvider.notifier).agregar(producto, restaurante);
  }
}

/// Sello "Personalizable" que invita a tocar el producto para elegir opciones.
class _SelloPersonalizable extends StatelessWidget {
  const _SelloPersonalizable();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: AppColors.brand.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(7),
      ),
      child: const Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.tune, size: 12, color: AppColors.brand),
          SizedBox(width: 4),
          Text('Personalizable',
              style: TextStyle(
                  color: AppColors.brand,
                  fontSize: 11,
                  fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}

/// Insignia circular con la cantidad de un producto en el carrito.
class _ContadorBadge extends StatelessWidget {
  final int cantidad;
  const _ContadorBadge({required this.cantidad});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
      decoration: BoxDecoration(
        color: AppColors.brand,
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: AppColors.background, width: 1.5),
      ),
      child: Text('$cantidad',
          style: const TextStyle(
              color: Colors.white,
              fontSize: 11,
              fontWeight: FontWeight.w800)),
    );
  }
}

/// Botón circular "+" para agregar un producto. Gradiente de marca, doble
/// sombra (glow + drop), escala al presionar y haptic ligero al tocar.
class _BotonAgregar extends StatefulWidget {
  final VoidCallback onTap;
  const _BotonAgregar({required this.onTap});

  @override
  State<_BotonAgregar> createState() => _BotonAgregarState();
}

class _BotonAgregarState extends State<_BotonAgregar> {
  bool _presionado = false;

  void _set(bool v) {
    if (_presionado != v) setState(() => _presionado = v);
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => _set(true),
      onTapUp: (_) => _set(false),
      onTapCancel: () => _set(false),
      onTap: () {
        HapticFeedback.lightImpact();
        widget.onTap();
      },
      child: AnimatedScale(
        scale: _presionado ? 0.9 : 1,
        duration: const Duration(milliseconds: 110),
        curve: Curves.easeOut,
        child: Container(
          height: 38,
          width: 38,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Brand.c400, Brand.c600],
            ),
            shape: BoxShape.circle,
            border: Border.all(color: AppColors.background, width: 2),
            boxShadow: [
              BoxShadow(
                color: Brand.c500.withValues(alpha: 0.5),
                blurRadius: 14,
                offset: const Offset(0, 6),
              ),
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.25),
                blurRadius: 6,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: const Icon(Icons.add_rounded, color: Colors.white, size: 22),
        ),
      ),
    );
  }
}

/// Barra inferior que resume el carrito y lleva al checkout (gradiente + glow).
class _BarraCarrito extends StatelessWidget {
  final int cantidad;
  final double total;
  final VoidCallback onTap;

  const _BarraCarrito({
    required this.cantidad,
    required this.total,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
        child: TarjetaPresionable(
          onTap: () {
            HapticFeedback.mediumImpact();
            onTap();
          },
          child: Container(
            height: 56,
            padding: const EdgeInsets.symmetric(horizontal: 16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Brand.c400, Brand.c600],
              ),
              borderRadius: BorderRadius.circular(14),
              boxShadow: [
                BoxShadow(
                  color: Brand.c500.withValues(alpha: 0.45),
                  blurRadius: 22,
                  offset: const Offset(0, 8),
                ),
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.3),
                  blurRadius: 8,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: Row(
              children: [
                AnimatedSwitcher(
                  duration: const Duration(milliseconds: 220),
                  transitionBuilder: (child, anim) => ScaleTransition(
                    scale: anim,
                    child: FadeTransition(opacity: anim, child: child),
                  ),
                  child: Container(
                    key: ValueKey(cantidad),
                    padding: const EdgeInsets.symmetric(
                        horizontal: 10, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.25),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text('$cantidad',
                        style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w800)),
                  ),
                ),
                const SizedBox(width: 12),
                const Text('Ver carrito',
                    style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w700,
                        fontSize: 16,
                        letterSpacing: -0.2)),
                const Spacer(),
                Text(formatoPesos(total),
                    style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w800,
                        fontSize: 16,
                        letterSpacing: -0.2)),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// Botón circular "de vidrio" para flotar sobre la imagen del header:
/// fondo translúcido con blur (BackdropFilter), borde sutil y haptic ligero.
class _BotonVidrio extends StatelessWidget {
  final IconData icono;
  final VoidCallback onTap;
  const _BotonVidrio({required this.icono, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return ClipOval(
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 12, sigmaY: 12),
        child: Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: Colors.black.withValues(alpha: 0.35),
            shape: BoxShape.circle,
            border: Border.all(color: Colors.white.withValues(alpha: 0.18)),
          ),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              customBorder: const CircleBorder(),
              onTap: () {
                HapticFeedback.lightImpact();
                onTap();
              },
              child: Icon(icono, color: Colors.white, size: 20),
            ),
          ),
        ),
      ),
    );
  }
}
