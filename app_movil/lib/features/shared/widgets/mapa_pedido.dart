import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/utils/mapa_tiles.dart';

/// Mapa con la ruta restaurante → punto de entrega usando OpenStreetMap.
///
/// Dibuja la ruta por calles ([ruta], de OSRM) y, si se pasa [domiciliario],
/// un marcador que se mueve por esa ruta. El marcador se anima entre cada
/// posición que llega para que el recorrido se vea fluido. El mapa hace zoom
/// automático para encuadrar toda la ruta al cargar (y al llegar la ruta real),
/// pero no se reajusta cuando el domiciliario se mueve.
///
/// Nota: las imágenes del mapa (tiles) y la ruta se descargan de internet; sin
/// conexión se ven los marcadores y una línea recta sobre fondo gris.
class MapaPedido extends StatefulWidget {
  final LatLng restaurante;
  final LatLng entrega;

  /// Puntos de la ruta por calles. Si trae menos de 2, se usa la línea recta
  /// restaurante → entrega.
  final List<LatLng> ruta;

  /// Posición actual del domiciliario. Si es `null`, no se muestra su marcador.
  final LatLng? domiciliario;

  /// Ícono del marcador del domiciliario (según su medio: moto, bici, auto…).
  final IconData iconoDomiciliario;

  final double height;

  const MapaPedido({
    super.key,
    required this.restaurante,
    required this.entrega,
    this.ruta = const [],
    this.domiciliario,
    this.iconoDomiciliario = Icons.delivery_dining,
    this.height = 220,
  });

  @override
  State<MapaPedido> createState() => _MapaPedidoState();
}

class _MapaPedidoState extends State<MapaPedido>
    with SingleTickerProviderStateMixin {
  final MapController _mapController = MapController();
  late final AnimationController _ctrl;
  LatLng? _desde;
  LatLng? _hasta;

  bool _mapaListo = false;
  List<LatLng> _ultimaRuta = const [];
  EstiloMapa _estilo = EstiloMapa.calles;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 260),
    );
    _desde = widget.domiciliario;
    _hasta = widget.domiciliario;
    if (widget.domiciliario != null) _ctrl.value = 1;
  }

  @override
  void didUpdateWidget(MapaPedido old) {
    super.didUpdateWidget(old);
    if (widget.domiciliario != null &&
        widget.domiciliario != old.domiciliario) {
      _desde = _posActual(); // arranca desde donde está pintado ahora mismo
      _hasta = widget.domiciliario;
      _ctrl.forward(from: 0);
    }
  }

  /// Posición interpolada del domiciliario según el avance de la animación.
  LatLng _posActual() {
    final d = _desde;
    final h = _hasta;
    if (d == null || h == null) {
      return widget.domiciliario ?? widget.restaurante;
    }
    final t = _ctrl.value;
    return LatLng(
      d.latitude + (h.latitude - d.latitude) * t,
      d.longitude + (h.longitude - d.longitude) * t,
    );
  }

  /// Encuadra la cámara para que se vean todos los [puntos] de la ruta.
  void _encuadrar(List<LatLng> puntos) {
    if (!_mapaListo || puntos.length < 2) return;
    _mapController.fitCamera(
      CameraFit.coordinates(
        coordinates: puntos,
        padding: const EdgeInsets.all(36),
        maxZoom: 16.5,
      ),
    );
  }

  /// `true` si dos rutas son, a efectos del encuadre, la misma.
  bool _mismaRuta(List<LatLng> a, List<LatLng> b) {
    if (a.length != b.length) return false;
    if (a.isEmpty) return true;
    return a.first == b.first && a.last == b.last;
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final puntosRuta = widget.ruta.length >= 2
        ? widget.ruta
        : [widget.restaurante, widget.entrega];
    final centro = LatLng(
      (widget.restaurante.latitude + widget.entrega.latitude) / 2,
      (widget.restaurante.longitude + widget.entrega.longitude) / 2,
    );

    // Reencuadrar solo cuando cambia la ruta (no cuando el domiciliario avanza).
    if (!_mismaRuta(puntosRuta, _ultimaRuta)) {
      _ultimaRuta = puntosRuta;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) _encuadrar(puntosRuta);
      });
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: SizedBox(
        height: widget.height,
        child: Stack(
          children: [
            FlutterMap(
          mapController: _mapController,
          options: MapOptions(
            initialCenter: centro,
            initialZoom: 14.5,
            onMapReady: () {
              _mapaListo = true;
              _encuadrar(_ultimaRuta);
            },
            interactionOptions: const InteractionOptions(
              flags: InteractiveFlag.pinchZoom | InteractiveFlag.drag,
            ),
          ),
          children: [
            ...MapaTiles.capas(_estilo),
            PolylineLayer(
              polylines: [
                Polyline(
                  points: puntosRuta,
                  strokeWidth: 4,
                  color: AppColors.brand,
                ),
              ],
            ),
            // El marcador móvil se repinta con la animación, no toda la capa.
            AnimatedBuilder(
              animation: _ctrl,
              builder: (context, _) {
                return MarkerLayer(
                  markers: [
                    _marcador(
                        widget.restaurante, Icons.storefront, AppColors.info),
                    _marcador(
                        widget.entrega, Icons.location_on, AppColors.brand),
                    if (widget.domiciliario != null)
                      _marcador(_posActual(), widget.iconoDomiciliario,
                          AppColors.success),
                  ],
                );
              },
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

  Marker _marcador(LatLng punto, IconData icono, Color color) {
    return Marker(
      point: punto,
      width: 40,
      height: 40,
      child: Container(
        decoration: BoxDecoration(
          color: color,
          shape: BoxShape.circle,
          border: Border.all(color: Colors.white, width: 2),
        ),
        child: Icon(icono, color: Colors.white, size: 22),
      ),
    );
  }
}
