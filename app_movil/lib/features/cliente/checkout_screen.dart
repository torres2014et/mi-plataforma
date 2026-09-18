import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:latlong2/latlong.dart';

import '../../core/router/app_router.dart';
import '../../core/theme/app_colors.dart';
import '../../core/utils/formato.dart';
import '../../core/utils/geocoding.dart';
import '../../core/utils/mapa_tiles.dart';
import '../../models/pedido_item.dart';
import '../../providers/carrito_provider.dart';
import '../../providers/pedido_providers.dart';
import '../../providers/services_providers.dart';
import '../../services/ubicacion_service.dart';
import '../shared/widgets/brand_button.dart';
import '../shared/widgets/imagen_placeholder.dart';
import 'widgets/cantidad_stepper.dart';

enum _MetodoPago { efectivo, tarjeta }

/// Carrito + checkout: revisar productos, dirección, método de pago y confirmar.
class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  final _direccionCtrl = TextEditingController(text: 'Cra. 8 #10-45, Ubaté');
  _MetodoPago _pago = _MetodoPago.efectivo;
  bool _enviando = false;

  /// Punto de entrega (GPS, dirección elegida o tocado en el mapa).
  /// null = aún no se ha fijado.
  LatLng? _ubicacion;
  bool _ubicando = false;

  // Autocompletado de direcciones.
  List<SugerenciaDireccion> _sugerencias = [];
  bool _buscando = false;
  Timer? _debounce;

  // Debounce para no spamear Nominatim al arrastrar el mapa.
  Timer? _debounceReverse;

  @override
  void dispose() {
    _debounce?.cancel();
    _debounceReverse?.cancel();
    _direccionCtrl.dispose();
    super.dispose();
  }

  /// Llena el campo de dirección con el resultado de hacer reverse geocoding
  /// del [punto]. Si [conDebounce] es true (caso tap/arrastre del mapa), espera
  /// 600ms antes de pedirlo. Si no encuentra una dirección, deja lo que haya.
  Future<void> _hacerReverse(LatLng punto) async {
    final direccion = await direccionDe(punto);
    if (!mounted || direccion == null || direccion.isEmpty) return;
    _direccionCtrl.text = direccion;
    // Cierra las sugerencias para que no tape el mapa.
    setState(() => _sugerencias = []);
  }

  void _actualizarDireccionDesdeMapa(LatLng punto,
      {bool conDebounce = false}) {
    _debounceReverse?.cancel();
    if (conDebounce) {
      _debounceReverse =
          Timer(const Duration(milliseconds: 600), () => _hacerReverse(punto));
    } else {
      _hacerReverse(punto);
    }
  }

  /// Toma la ubicación actual del teléfono como punto de entrega.
  Future<void> _usarMiUbicacion() async {
    setState(() => _ubicando = true);
    try {
      final pos = await ref.read(ubicacionServiceProvider).ubicacionActual();
      if (!mounted) return;
      setState(() => _ubicacion = pos);
      // Resuelve la dirección de ese punto y la mete en el campo.
      _actualizarDireccionDesdeMapa(pos);
      ScaffoldMessenger.of(context)
          .showSnackBar(_snackSuccess('Ubicación de entrega fijada 📍'));
    } on UbicacionException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(_snackError(e.mensaje));
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          _snackError('No se pudo obtener la ubicación: $e'),
        );
      }
    } finally {
      if (mounted) setState(() => _ubicando = false);
    }
  }

  /// Cada vez que el usuario escribe: espera un poco y busca sugerencias.
  void _alEscribir(String texto) {
    _debounce?.cancel();
    final t = texto.trim();
    if (t.length < 3) {
      setState(() => _sugerencias = []);
      return;
    }
    _debounce =
        Timer(const Duration(milliseconds: 450), () => _buscarSugerencias(t));
  }

  Future<void> _buscarSugerencias(String texto) async {
    setState(() => _buscando = true);
    final res = await buscarDirecciones(texto);
    if (!mounted) return;
    setState(() {
      _sugerencias = res;
      _buscando = false;
    });
  }

  /// El usuario elige una sugerencia de la lista.
  void _elegirSugerencia(SugerenciaDireccion s) {
    _debounce?.cancel();
    FocusScope.of(context).unfocus();
    _direccionCtrl.text = s.nombre;
    setState(() {
      _ubicacion = s.punto;
      _sugerencias = [];
    });
  }

  Future<void> _confirmar() async {
    final carrito = ref.read(carritoProvider);
    final direccion = _direccionCtrl.text.trim();

    if (direccion.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        _snackInfo('Escribe una dirección de entrega'),
      );
      return;
    }

    setState(() => _enviando = true);
    try {
      final items = carrito.items
          .map((ci) => PedidoItem(
                id: 0,
                productoId: ci.producto.id,
                nombreProducto: ci.producto.nombre,
                cantidad: ci.cantidad,
                precioUnitario: ci.precioUnitario,
                detalle: ci.descripcionOpciones.isEmpty
                    ? null
                    : ci.descripcionOpciones,
              ))
          .toList();

      await ref.read(pedidoServiceProvider).crearPedido(
            restauranteId: carrito.restauranteId!,
            items: items,
            costoDomicilio: carrito.costoDomicilio,
            direccionEntrega: direccion,
            latEntrega: _ubicacion?.latitude,
            lngEntrega: _ubicacion?.longitude,
          );

      ref.invalidate(misPedidosProvider);
      // El pedido nace sin domiciliario: debe aparecer en "Pedidos disponibles".
      ref.invalidate(pedidosDisponiblesProvider);
      ref.read(carritoProvider.notifier).vaciar();

      if (!mounted) return;
      context.go(Rutas.cliente);
      ScaffoldMessenger.of(context)
          .showSnackBar(_snackSuccess('¡Pedido confirmado! 🎉'));
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          _snackError('No se pudo crear el pedido: $e'),
        );
      }
    } finally {
      if (mounted) setState(() => _enviando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final carrito = ref.watch(carritoProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Tu pedido')),
      body: carrito.isEmpty
          ? const _CarritoVacio()
          : ListView(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
              children: [
                // Restaurante
                _CabeceraRestaurante(
                  nombre: carrito.restauranteNombre,
                  imagenUrl: carrito.restauranteImagenUrl,
                  items: carrito.cantidadTotal,
                ),
                const SizedBox(height: 18),

                // Items
                _Seccion(
                  titulo: 'Tu pedido',
                  child: Column(
                    children: [
                      for (var i = 0; i < carrito.items.length; i++) ...[
                        if (i != 0)
                          Divider(height: 20, color: AppColors.border),
                        _ItemRow(item: carrito.items[i]),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Dirección
                _Seccion(
                  titulo: 'Dirección de entrega',
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      TextField(
                        controller: _direccionCtrl,
                        textInputAction: TextInputAction.search,
                        onChanged: _alEscribir,
                        onSubmitted: _buscarSugerencias,
                        decoration: InputDecoration(
                          hintText: 'Escribe y elige tu dirección…',
                          prefixIcon: const Icon(Icons.location_on_outlined),
                          suffixIcon: _buscando
                              ? const Padding(
                                  padding: EdgeInsets.all(12),
                                  child: SizedBox(
                                    height: 18,
                                    width: 18,
                                    child: CircularProgressIndicator(
                                        strokeWidth: 2.2),
                                  ),
                                )
                              : const Icon(Icons.search),
                        ),
                      ),
                      if (_sugerencias.isNotEmpty) ...[
                        const SizedBox(height: 6),
                        _ListaSugerencias(
                          sugerencias: _sugerencias,
                          onElegir: _elegirSugerencia,
                        ),
                      ],
                      const SizedBox(height: 12),
                      _SelectorUbicacion(
                        punto: _ubicacion,
                        onMover: (p) {
                          setState(() => _ubicacion = p);
                          // Al tocar el mapa, también actualizamos el campo
                          // de dirección con debounce para no spamear el API.
                          _actualizarDireccionDesdeMapa(p, conDebounce: true);
                        },
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Icon(
                            _ubicacion == null
                                ? Icons.touch_app_outlined
                                : Icons.check_circle,
                            size: 18,
                            color: _ubicacion == null
                                ? AppColors.textSecondary
                                : AppColors.success,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              _ubicacion == null
                                  ? 'Toca el mapa, busca tu dirección o usa tu ubicación.'
                                  : 'Punto de entrega fijado — toca el mapa para ajustar.',
                              style: TextStyle(
                                color: _ubicacion == null
                                    ? AppColors.textSecondary
                                    : AppColors.success,
                                fontWeight: FontWeight.w600,
                                fontSize: 12.5,
                              ),
                            ),
                          ),
                          TextButton.icon(
                            onPressed: _ubicando ? null : _usarMiUbicacion,
                            icon: _ubicando
                                ? const SizedBox(
                                    height: 14,
                                    width: 14,
                                    child: CircularProgressIndicator(
                                        strokeWidth: 2.2),
                                  )
                                : const Icon(Icons.my_location, size: 16),
                            label: const Text('Mi ubicación'),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Método de pago
                _Seccion(
                  titulo: 'Método de pago',
                  child: Row(
                    children: [
                      _OpcionPago(
                        icono: Icons.payments_outlined,
                        label: 'Efectivo',
                        activo: _pago == _MetodoPago.efectivo,
                        onTap: () {
                          HapticFeedback.selectionClick();
                          setState(() => _pago = _MetodoPago.efectivo);
                        },
                      ),
                      const SizedBox(width: 10),
                      _OpcionPago(
                        icono: Icons.credit_card,
                        label: 'Tarjeta',
                        activo: _pago == _MetodoPago.tarjeta,
                        onTap: () {
                          HapticFeedback.selectionClick();
                          setState(() => _pago = _MetodoPago.tarjeta);
                        },
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Resumen de costos
                _Seccion(
                  titulo: 'Resumen',
                  child: Column(
                    children: [
                      _LineaResumen(
                          etiqueta: 'Subtotal', valor: carrito.subtotal),
                      const SizedBox(height: 8),
                      _LineaResumen(
                          etiqueta: 'Costo de domicilio',
                          valor: carrito.costoDomicilio),
                      Divider(height: 22, color: AppColors.border),
                      _LineaResumen(
                          etiqueta: 'Total',
                          valor: carrito.total,
                          destacar: true),
                    ],
                  ),
                ),
              ],
            ),
      bottomNavigationBar: carrito.isEmpty
          ? null
          : SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                child: BrandButton(
                  label: 'Confirmar pedido · ${formatoPesos(carrito.total)}',
                  cargando: _enviando,
                  onPressed: _confirmar,
                ),
              ),
            ),
    );
  }
}

/// Cabecera con el restaurante del que es el pedido.
class _CabeceraRestaurante extends StatelessWidget {
  final String nombre;
  final String? imagenUrl;
  final int items;
  const _CabeceraRestaurante(
      {required this.nombre, this.imagenUrl, required this.items});

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    return Row(
      children: [
        // Miniatura con sombra suave para darle peso visual.
        DecoratedBox(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.3),
                blurRadius: 12,
                offset: const Offset(0, 5),
              ),
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: SizedBox(
              height: 52,
              width: 52,
              child: ImagenPlaceholder(
                  texto: nombre, url: imagenUrl, height: 52, iconSize: 22),
            ),
          ),
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('TU PEDIDO EN',
                  style: TextStyle(
                      color: AppColors.textMuted,
                      fontWeight: FontWeight.w700,
                      fontSize: 10.5,
                      letterSpacing: 0.8)),
              const SizedBox(height: 3),
              Text(nombre,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: text.titleMedium?.copyWith(
                      fontWeight: FontWeight.w800, letterSpacing: -0.3)),
              const SizedBox(height: 2),
              Text('$items artículo${items == 1 ? "" : "s"}',
                  style: text.bodySmall?.copyWith(
                      color: AppColors.textSecondary,
                      fontWeight: FontWeight.w500)),
            ],
          ),
        ),
      ],
    );
  }
}

/// Tarjeta de sección con eyebrow en mayúsculas: lenguaje visual consistente
/// con el resto de la app (mismo patrón que "ENTREGAR EN" del detalle).
class _Seccion extends StatelessWidget {
  final String titulo;
  final Widget child;
  const _Seccion({required this.titulo, required this.child});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 6, bottom: 10),
          child: Text(titulo.toUpperCase(),
              style: const TextStyle(
                  color: AppColors.textMuted,
                  fontWeight: FontWeight.w700,
                  fontSize: 11,
                  letterSpacing: 0.8)),
        ),
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.border),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.18),
                blurRadius: 14,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: child,
        ),
      ],
    );
  }
}

class _ItemRow extends ConsumerWidget {
  final CarritoItem item;
  const _ItemRow({required this.item});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final p = item.producto;
    return Row(
      children: [
        // Miniatura del producto con insignia "Nx" en la esquina (igual que el
        // contador del menú): refuerza la cantidad sin pelear con el stepper.
        Stack(
          clipBehavior: Clip.none,
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: SizedBox(
                height: 50,
                width: 50,
                child: ImagenPlaceholder(
                    texto: p.nombre,
                    categoria: p.categoria,
                    url: p.imagenUrl,
                    height: 50,
                    iconSize: 20),
              ),
            ),
            Positioned(
              top: -4,
              right: -4,
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                decoration: BoxDecoration(
                  color: AppColors.brand,
                  borderRadius: BorderRadius.circular(999),
                  border: Border.all(color: AppColors.card, width: 1.5),
                ),
                child: Text('${item.cantidad}×',
                    style: const TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.w800)),
              ),
            ),
          ],
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.baseline,
                textBaseline: TextBaseline.alphabetic,
                children: [
                  Expanded(
                    child: Text(p.nombre,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                            fontWeight: FontWeight.w700,
                            letterSpacing: -0.2)),
                  ),
                  Text(formatoPesos(item.subtotal),
                      style: const TextStyle(
                          fontWeight: FontWeight.w800,
                          letterSpacing: -0.2)),
                ],
              ),
              if (item.descripcionOpciones.isNotEmpty) ...[
                const SizedBox(height: 3),
                Text(item.descripcionOpciones,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                        color: AppColors.textMuted,
                        fontSize: 12,
                        height: 1.35)),
              ],
              const SizedBox(height: 8),
              Row(
                children: [
                  Text(formatoPesos(item.precioUnitario),
                      style: const TextStyle(
                          color: AppColors.textSecondary, fontSize: 13)),
                  const Spacer(),
                  CantidadStepper(
                    size: 28,
                    cantidad: item.cantidad,
                    onMas: () => ref
                        .read(carritoProvider.notifier)
                        .incrementarLinea(item.lineId),
                    onMenos: () => ref
                        .read(carritoProvider.notifier)
                        .decrementarLinea(item.lineId),
                  ),
                  const SizedBox(width: 4),
                  IconButton(
                    tooltip: 'Quitar del carrito',
                    visualDensity: VisualDensity.compact,
                    icon: const Icon(Icons.delete_outline,
                        size: 20, color: AppColors.error),
                    onPressed: () => ref
                        .read(carritoProvider.notifier)
                        .quitarLinea(item.lineId),
                  ),
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _OpcionPago extends StatelessWidget {
  final IconData icono;
  final String label;
  final bool activo;
  final VoidCallback onTap;

  const _OpcionPago({
    required this.icono,
    required this.label,
    required this.activo,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 220),
          curve: Curves.easeOut,
          padding: const EdgeInsets.symmetric(vertical: 14),
          decoration: BoxDecoration(
            gradient: activo
                ? LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [
                      Brand.c500.withValues(alpha: 0.22),
                      Brand.c600.withValues(alpha: 0.10),
                    ],
                  )
                : null,
            color: activo ? null : AppColors.surfaceVariant,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: activo ? AppColors.brand : AppColors.border,
              width: activo ? 1.5 : 1,
            ),
            boxShadow: activo
                ? [
                    BoxShadow(
                      color: Brand.c500.withValues(alpha: 0.3),
                      blurRadius: 14,
                      offset: const Offset(0, 5),
                    ),
                  ]
                : null,
          ),
          child: Column(
            children: [
              Icon(icono,
                  color: activo ? AppColors.brand : AppColors.textSecondary),
              const SizedBox(height: 6),
              Text(label,
                  style: TextStyle(
                    color: activo ? AppColors.brand : AppColors.textSecondary,
                    fontWeight: activo ? FontWeight.w800 : FontWeight.w600,
                    letterSpacing: -0.1,
                  )),
            ],
          ),
        ),
      ),
    );
  }
}

class _LineaResumen extends StatelessWidget {
  final String etiqueta;
  final double valor;
  final bool destacar;

  const _LineaResumen({
    required this.etiqueta,
    required this.valor,
    this.destacar = false,
  });

  @override
  Widget build(BuildContext context) {
    if (destacar) {
      return Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        crossAxisAlignment: CrossAxisAlignment.baseline,
        textBaseline: TextBaseline.alphabetic,
        children: [
          Text(etiqueta,
              style: const TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 16,
                  letterSpacing: -0.2)),
          Text(formatoPesos(valor),
              style: const TextStyle(
                  color: AppColors.brand,
                  fontWeight: FontWeight.w800,
                  fontSize: 19,
                  letterSpacing: -0.3)),
        ],
      );
    }
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(etiqueta,
            style: const TextStyle(color: AppColors.textSecondary)),
        Text(formatoPesos(valor),
            style: const TextStyle(
                color: AppColors.textPrimary, fontWeight: FontWeight.w600)),
      ],
    );
  }
}

/// Lista desplegable de sugerencias de dirección (autocompletado).
class _ListaSugerencias extends StatelessWidget {
  final List<SugerenciaDireccion> sugerencias;
  final ValueChanged<SugerenciaDireccion> onElegir;

  const _ListaSugerencias({required this.sugerencias, required this.onElegir});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        children: [
          for (var i = 0; i < sugerencias.length; i++) ...[
            if (i != 0) Divider(height: 1, color: AppColors.border),
            InkWell(
              onTap: () => onElegir(sugerencias[i]),
              borderRadius: BorderRadius.circular(12),
              child: Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                child: Row(
                  children: [
                    const Icon(Icons.place_outlined,
                        size: 18, color: AppColors.textSecondary),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        sugerencias[i].nombre,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 13),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

/// Mapa selector del punto de entrega: siempre visible. Si [punto] es null no
/// hay pin (centra en Ubaté); tocar el mapa o buscar/usar GPS lo fija. Al
/// cambiar mucho el punto (buscar dirección o GPS) recentra la cámara.
class _SelectorUbicacion extends StatefulWidget {
  final LatLng? punto;
  final ValueChanged<LatLng> onMover;

  const _SelectorUbicacion({required this.punto, required this.onMover});

  @override
  State<_SelectorUbicacion> createState() => _SelectorUbicacionState();
}

class _SelectorUbicacionState extends State<_SelectorUbicacion> {
  // Centro de Ubaté por defecto, cuando aún no hay punto.
  static const LatLng _ubate = LatLng(5.3078, -73.8155);
  final MapController _mapCtrl = MapController();
  EstiloMapa _estilo = EstiloMapa.calles;

  @override
  void didUpdateWidget(_SelectorUbicacion old) {
    super.didUpdateWidget(old);
    final nuevo = widget.punto;
    if (nuevo == null) return;
    // Recentra solo ante saltos grandes (buscar dirección / GPS), no en cada
    // toque fino sobre el mapa.
    final salto = old.punto == null
        ? double.infinity
        : const Distance()(old.punto!, nuevo);
    if (salto > 120) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) _mapCtrl.move(nuevo, 16);
      });
    }
  }

  @override
  void dispose() {
    _mapCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final punto = widget.punto;
    return ClipRRect(
      borderRadius: BorderRadius.circular(12),
      child: SizedBox(
        height: 160,
        child: Stack(
          children: [
            FlutterMap(
          mapController: _mapCtrl,
          options: MapOptions(
            initialCenter: punto ?? _ubate,
            initialZoom: punto == null ? 14 : 16,
            onTap: (_, p) => widget.onMover(p),
            interactionOptions: const InteractionOptions(
              flags: InteractiveFlag.pinchZoom | InteractiveFlag.drag,
            ),
          ),
          children: [
            ...MapaTiles.capas(_estilo),
            if (punto != null)
              MarkerLayer(
                markers: [
                  Marker(
                    point: punto,
                    width: 44,
                    height: 44,
                    child: Container(
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [Brand.c400, Brand.c600],
                        ),
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.white, width: 2.5),
                        boxShadow: [
                          BoxShadow(
                            color: Brand.c500.withValues(alpha: 0.55),
                            blurRadius: 14,
                            offset: const Offset(0, 4),
                          ),
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.3),
                            blurRadius: 6,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: const Icon(Icons.location_on,
                          color: Colors.white, size: 22),
                    ),
                  ),
                ],
              ),
          ],
            ),
            Positioned(
              top: 8,
              right: 8,
              child: BotonEstiloMapa(
                estilo: _estilo,
                onToggle: () => setState(() => _estilo =
                    _estilo == EstiloMapa.calles
                        ? EstiloMapa.satelite
                        : EstiloMapa.calles),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _CarritoVacio extends StatelessWidget {
  const _CarritoVacio();

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Padding(
        padding: EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.shopping_cart_outlined,
                size: 56, color: AppColors.textMuted),
            SizedBox(height: 12),
            Text('Tu carrito está vacío',
                style: TextStyle(color: AppColors.textSecondary)),
          ],
        ),
      ),
    );
  }
}

/// SnackBar premium: tarjeta flotante con borde de color e ícono.
SnackBar _snack(String texto,
    {required IconData icono, required Color color}) {
  return SnackBar(
    behavior: SnackBarBehavior.floating,
    backgroundColor: AppColors.surface,
    elevation: 8,
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(12),
      side: BorderSide(color: color.withValues(alpha: 0.4)),
    ),
    content: Row(
      children: [
        Icon(icono, color: color, size: 20),
        const SizedBox(width: 10),
        Expanded(
          child: Text(texto,
              style: const TextStyle(color: AppColors.textPrimary)),
        ),
      ],
    ),
  );
}

SnackBar _snackSuccess(String texto) =>
    _snack(texto, icono: Icons.check_circle_rounded, color: AppColors.success);
SnackBar _snackError(String texto) =>
    _snack(texto, icono: Icons.error_outline_rounded, color: AppColors.error);
SnackBar _snackInfo(String texto) =>
    _snack(texto, icono: Icons.info_outline_rounded, color: AppColors.brand);
