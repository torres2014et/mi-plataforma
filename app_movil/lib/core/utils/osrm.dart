import 'dart:convert';
import 'dart:io';

import 'package:latlong2/latlong.dart';

import '../../models/recorrido.dart';

/// Pide a OSRM (servidor público de demo) la ruta por calles entre dos puntos
/// y devuelve sus coordenadas junto con la distancia y duración estimadas.
///
/// Fase 1: usamos el OSRM público para tener una ruta realista sin backend.
/// Si no hay internet o falla, devolvemos una línea recta con la distancia
/// (haversine) y un tiempo estimado a ~25 km/h, para que el mapa y los datos
/// sigan funcionando.
///
/// En Fase 2 esto puede salir del propio backend o de un OSRM propio.
Future<Recorrido> obtenerRutaOsrm(LatLng origen, LatLng destino) async {
  final url = Uri.parse(
    'https://router.project-osrm.org/route/v1/driving/'
    '${origen.longitude},${origen.latitude};'
    '${destino.longitude},${destino.latitude}'
    '?overview=full&geometries=geojson',
  );

  HttpClient? client;
  try {
    client = HttpClient()..connectionTimeout = const Duration(seconds: 8);
    final req = await client.getUrl(url);
    req.headers.set(HttpHeaders.userAgentHeader, 'domicilios_ubate');
    final resp = await req.close();
    if (resp.statusCode != 200) return _recta(origen, destino);

    final body = await resp.transform(utf8.decoder).join();
    final json = jsonDecode(body) as Map<String, dynamic>;
    final routes = json['routes'] as List<dynamic>?;
    if (routes == null || routes.isEmpty) return _recta(origen, destino);

    final ruta = routes.first as Map<String, dynamic>;
    // geometry.coordinates viene como [[lng, lat], ...].
    final coords = ruta['geometry']['coordinates'] as List<dynamic>;
    final puntos = coords
        .map((c) => LatLng(
              (c[1] as num).toDouble(),
              (c[0] as num).toDouble(),
            ))
        .toList();
    if (puntos.length < 2) return _recta(origen, destino);

    return Recorrido(
      puntos: puntos,
      distanciaMetros: (ruta['distance'] as num).toDouble(),
      duracionSegundos: (ruta['duration'] as num).toDouble(),
    );
  } catch (_) {
    // Sin conexión o respuesta inválida → línea recta estimada.
    return _recta(origen, destino);
  } finally {
    client?.close();
  }
}

/// Recorrido en línea recta con distancia y tiempo estimados (sin red).
Recorrido _recta(LatLng origen, LatLng destino) {
  final metros = const Distance()(origen, destino);
  return Recorrido(
    puntos: [origen, destino],
    distanciaMetros: metros,
    duracionSegundos: metros / 6.94, // ~25 km/h
  );
}
