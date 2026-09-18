import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/utils/formato.dart';
import '../../../core/utils/mapa_tiles.dart';
import '../../../models/restaurante.dart';
import '../../shared/widgets/brand_button.dart';
import '../../shared/widgets/imagen_placeholder.dart';

/// Abre un **resumen** del restaurante como bottom sheet (se invoca al mantener
/// presionada su tarjeta en la lista). Muestra foto, estado, categoría, rating,
/// tiempo, envío, dirección y descripción, con un CTA para ir al menú.
///
/// [onVerMenu] se dispara tras cerrar la hoja (para navegar al menú).
Future<void> mostrarResumenRestaurante(
  BuildContext context, {
  required Restaurante restaurante,
  required VoidCallback onVerMenu,
}) {
  return showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (_) => _ResumenSheet(restaurante: restaurante, onVerMenu: onVerMenu),
  );
}

class _ResumenSheet extends StatelessWidget {
  final Restaurante restaurante;
  final VoidCallback onVerMenu;

  const _ResumenSheet({required this.restaurante, required this.onVerMenu});

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    final r = restaurante;
    final cerrado = !r.abierto;

    return Container(
      decoration: const BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: SafeArea(
        top: false,
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Manija + foto con scrim
              ClipRRect(
                borderRadius:
                    const BorderRadius.vertical(top: Radius.circular(24)),
                child: Stack(
                  children: [
                    SizedBox(
                      height: 160,
                      width: double.infinity,
                      child: ImagenPlaceholder(
                        texto: r.nombre,
                        categoria: r.categoria,
                        url: r.imagenUrl,
                        iconSize: 56,
                      ),
                    ),
                    Positioned.fill(
                      child: DecoratedBox(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                            colors: [
                              Colors.black.withValues(alpha: 0.1),
                              Colors.black.withValues(alpha: 0.6),
                            ],
                          ),
                        ),
                      ),
                    ),
                    // Manija
                    Positioned(
                      top: 10,
                      left: 0,
                      right: 0,
                      child: Center(
                        child: Container(
                          width: 40,
                          height: 4,
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.8),
                            borderRadius: BorderRadius.circular(2),
                          ),
                        ),
                      ),
                    ),
                    // Estado abierto/cerrado
                    Positioned(
                      top: 14,
                      right: 14,
                      child: _EstadoPill(abierto: r.abierto),
                    ),
                    // Nombre
                    Positioned(
                      left: 16,
                      right: 16,
                      bottom: 12,
                      child: Text(
                        r.nombre,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: text.titleLarge?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Chips de meta
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _MetaChip(
                          icon: Icons.star_rounded,
                          texto: r.rating.toStringAsFixed(1),
                          colorIcono: AppColors.star,
                        ),
                        _MetaChip(
                            icon: Icons.category_outlined, texto: r.categoria),
                        _MetaChip(
                            icon: Icons.schedule,
                            texto: '${r.tiempoEntregaMin} min'),
                        _MetaChip(
                          icon: Icons.delivery_dining,
                          texto: r.costoDomicilio == 0
                              ? 'Envío gratis'
                              : formatoPesos(r.costoDomicilio),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    Text(r.descripcion,
                        style: const TextStyle(color: AppColors.textSecondary)),
                    const SizedBox(height: 14),
                    // Dirección
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.location_on_outlined,
                            size: 18, color: AppColors.textSecondary),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(r.direccion,
                              style: const TextStyle(
                                  color: AppColors.textSecondary,
                                  fontSize: 13.5)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    // Mini-mapa: punto exacto de salida de los domicilios.
                    // Tocarlo abre el mapa a pantalla completa con zoom.
                    _MiniMapa(restaurante: r),
                    const SizedBox(height: 20),
                    // CTA
                    BrandButton(
                      label: cerrado ? 'Cerrado por ahora' : 'Ver menú',
                      icono: cerrado ? Icons.lock_clock : Icons.restaurant_menu,
                      onPressed: cerrado
                          ? null
                          : () {
                              Navigator.pop(context);
                              onVerMenu();
                            },
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Píldora de estado abierto/cerrado sobre la foto.
class _EstadoPill extends StatelessWidget {
  final bool abierto;
  const _EstadoPill({required this.abierto});

  @override
  Widget build(BuildContext context) {
    final color = abierto ? AppColors.success : AppColors.error;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.55),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 8,
            height: 8,
            decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          ),
          const SizedBox(width: 6),
          Text(abierto ? 'Abierto' : 'Cerrado',
              style: const TextStyle(
                  color: Colors.white,
                  fontSize: 12.5,
                  fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}

/// Marcador del restaurante (punto de salida de los domicilios).
Marker _marcadorSalida(LatLng punto) => Marker(
      point: punto,
      width: 44,
      height: 44,
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.brand,
          shape: BoxShape.circle,
          border: Border.all(color: Colors.white, width: 2.5),
          boxShadow: [
            BoxShadow(
              color: AppColors.brand.withValues(alpha: 0.5),
              blurRadius: 10,
            ),
          ],
        ),
        child: const Icon(Icons.storefront, color: Colors.white, size: 24),
      ),
    );

/// Mini-mapa (OpenStreetMap) centrado en el restaurante: marca el **punto
/// exacto de salida de los domicilios**. No es interactivo (para no pelear con
/// el scroll de la hoja); tocarlo abre el mapa a pantalla completa con zoom.
/// Las imágenes (tiles) se descargan de internet; sin conexión se ve el fondo
/// gris con el marcador.
class _MiniMapa extends StatelessWidget {
  final Restaurante restaurante;
  const _MiniMapa({required this.restaurante});

  @override
  Widget build(BuildContext context) {
    final punto = LatLng(restaurante.lat, restaurante.lng);
    return GestureDetector(
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute<void>(
          builder: (_) => _MapaUbicacionScreen(restaurante: restaurante),
        ),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: SizedBox(
          height: 150,
          child: Stack(
            children: [
              // El mapa no captura gestos (absorbe el GestureDetector de arriba).
              Positioned.fill(
                child: IgnorePointer(
                  child: FlutterMap(
                    options: MapOptions(
                      initialCenter: punto,
                      initialZoom: 15.5,
                      interactionOptions:
                          const InteractionOptions(flags: InteractiveFlag.none),
                    ),
                    children: [
                      ...MapaTiles.capas(EstiloMapa.calles),
                      MarkerLayer(markers: [_marcadorSalida(punto)]),
                    ],
                  ),
                ),
              ),
              // Etiqueta: punto de salida de domicilios.
              Positioned(
                left: 10,
                top: 10,
                child: _CintaMapa(
                  icono: Icons.delivery_dining,
                  texto: 'Salida de domicilios',
                ),
              ),
              // Pista de "tocar para ampliar".
              const Positioned(
                right: 10,
                bottom: 10,
                child: _CintaMapa(
                  icono: Icons.zoom_out_map,
                  texto: 'Toca para ampliar',
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Cinta semitransparente con ícono + texto, para etiquetas sobre el mapa.
class _CintaMapa extends StatelessWidget {
  final IconData icono;
  final String texto;
  const _CintaMapa({required this.icono, required this.texto});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.6),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icono, color: Colors.white, size: 14),
          const SizedBox(width: 5),
          Text(texto,
              style: const TextStyle(
                  color: Colors.white,
                  fontSize: 11.5,
                  fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

/// Mapa a pantalla completa, interactivo (zoom/arrastre), centrado en el punto
/// de salida de los domicilios (la ubicación del restaurante). Incluye botones
/// de zoom y una tarjeta inferior con la dirección.
class _MapaUbicacionScreen extends StatefulWidget {
  final Restaurante restaurante;
  const _MapaUbicacionScreen({required this.restaurante});

  @override
  State<_MapaUbicacionScreen> createState() => _MapaUbicacionScreenState();
}

class _MapaUbicacionScreenState extends State<_MapaUbicacionScreen> {
  final MapController _controller = MapController();
  static const double _min = 4;
  static const double _max = 18.5;

  void _zoom(double delta) {
    final cam = _controller.camera;
    final nuevo = (cam.zoom + delta).clamp(_min, _max);
    _controller.move(cam.center, nuevo);
  }

  @override
  Widget build(BuildContext context) {
    final r = widget.restaurante;
    final punto = LatLng(r.lat, r.lng);

    return Scaffold(
      appBar: AppBar(
        title: Text(r.nombre, maxLines: 1, overflow: TextOverflow.ellipsis),
      ),
      body: Stack(
        children: [
          FlutterMap(
            mapController: _controller,
            options: MapOptions(
              initialCenter: punto,
              initialZoom: 16.5,
              minZoom: _min,
              maxZoom: _max,
              interactionOptions: const InteractionOptions(
                flags: InteractiveFlag.pinchZoom |
                    InteractiveFlag.drag |
                    InteractiveFlag.doubleTapZoom |
                    InteractiveFlag.flingAnimation,
              ),
            ),
            children: [
              ...MapaTiles.capas(EstiloMapa.calles),
              MarkerLayer(markers: [_marcadorSalida(punto)]),
            ],
          ),
          // Botones de zoom
          Positioned(
            right: 16,
            bottom: 130,
            child: Column(
              children: [
                _BotonZoom(
                    icono: Icons.add, onTap: () => _zoom(1), heroTag: 'zin'),
                const SizedBox(height: 10),
                _BotonZoom(
                    icono: Icons.remove,
                    onTap: () => _zoom(-1),
                    heroTag: 'zout'),
              ],
            ),
          ),
          // Tarjeta inferior con la dirección / punto de salida
          Positioned(
            left: 16,
            right: 16,
            bottom: 24,
            child: Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.card,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.border),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.3),
                    blurRadius: 16,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(9),
                    decoration: BoxDecoration(
                      color: AppColors.brand.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.delivery_dining,
                        color: AppColors.brand, size: 22),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Desde aquí salen los domicilios',
                            style: TextStyle(
                                fontWeight: FontWeight.w700, fontSize: 14)),
                        const SizedBox(height: 2),
                        Text(r.direccion,
                            style: const TextStyle(
                                color: AppColors.textSecondary, fontSize: 13)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Botón circular de zoom para el mapa a pantalla completa.
class _BotonZoom extends StatelessWidget {
  final IconData icono;
  final VoidCallback onTap;
  final String heroTag;
  const _BotonZoom(
      {required this.icono, required this.onTap, required this.heroTag});

  @override
  Widget build(BuildContext context) {
    return FloatingActionButton.small(
      heroTag: heroTag,
      onPressed: onTap,
      backgroundColor: AppColors.surface,
      foregroundColor: AppColors.textPrimary,
      child: Icon(icono),
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
