import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';

/// Estilo del mapa: calles (claro y con nombres) o satélite (fotos reales).
enum EstiloMapa { calles, satelite }

/// Teselas de mapa **sin API key**, compartidas por todos los mapas de la app
/// (web y Flutter usan las mismas, para que se vean igual):
/// - **Calles**: CartoDB Voyager — claro, detallado y con nombres de calles.
/// - **Satélite**: imágenes reales de Esri + una capa de vías/nombres encima
///   (así se ven los techos de las casas para marcar la dirección exacta).
class MapaTiles {
  static const _ua = 'com.ubate.domicilios_ubate';
  static const _calles =
      'https://a.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png';
  static const _satImg =
      'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
  static const _satVias =
      'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}';

  /// Capas de teselas para [estilo]. El satélite devuelve dos capas (imagen +
  /// nombres de vías encima); las calles, una. Se esparcen en `children`.
  static List<Widget> capas(EstiloMapa estilo) {
    if (estilo == EstiloMapa.satelite) {
      return [
        TileLayer(
            urlTemplate: _satImg, userAgentPackageName: _ua, maxNativeZoom: 19),
        TileLayer(
            urlTemplate: _satVias, userAgentPackageName: _ua, maxNativeZoom: 19),
      ];
    }
    return [
      TileLayer(
          urlTemplate: _calles, userAgentPackageName: _ua, maxNativeZoom: 20),
    ];
  }
}

/// Botón flotante para alternar Calles ↔ Satélite sobre un mapa interactivo.
/// Se coloca con un `Positioned` dentro de un `Stack` que envuelve el mapa.
class BotonEstiloMapa extends StatelessWidget {
  final EstiloMapa estilo;
  final VoidCallback onToggle;
  const BotonEstiloMapa(
      {super.key, required this.estilo, required this.onToggle});

  @override
  Widget build(BuildContext context) {
    final esSat = estilo == EstiloMapa.satelite;
    return Material(
      color: Colors.black.withValues(alpha: 0.55),
      borderRadius: BorderRadius.circular(10),
      child: InkWell(
        borderRadius: BorderRadius.circular(10),
        onTap: onToggle,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(esSat ? Icons.map_outlined : Icons.satellite_alt,
                  size: 16, color: Colors.white),
              const SizedBox(width: 6),
              Text(esSat ? 'Calles' : 'Satélite',
                  style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.w700)),
            ],
          ),
        ),
      ),
    );
  }
}
