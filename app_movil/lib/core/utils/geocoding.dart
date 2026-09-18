import 'dart:convert';
import 'dart:io';

import 'package:latlong2/latlong.dart';

/// Una coincidencia de dirección devuelta por el buscador (autocompletado).
class SugerenciaDireccion {
  /// Texto legible de la dirección (display_name de Nominatim).
  final String nombre;
  final LatLng punto;

  const SugerenciaDireccion({required this.nombre, required this.punto});
}

/// Busca direcciones que coincidan con [texto] (autocompletado tipo mapa).
///
/// Usa Nominatim (OSM), priorizando Colombia y la zona de Ubaté con un
/// `viewbox`. Devuelve hasta 6 sugerencias; lista vacía si no hay o falla.
/// Sin dependencias nuevas (mismo enfoque que `osrm.dart`).
Future<List<SugerenciaDireccion>> buscarDirecciones(String texto) async {
  final url = Uri.parse(
    'https://nominatim.openstreetmap.org/search'
    '?q=${Uri.encodeQueryComponent(texto)}'
    '&format=jsonv2&limit=6&accept-language=es'
    '&countrycodes=co'
    // Caja alrededor de Ubaté para priorizar (sin restringir: bounded=0).
    '&viewbox=-73.90,5.40,-73.72,5.22&bounded=0',
  );

  HttpClient? client;
  try {
    client = HttpClient()..connectionTimeout = const Duration(seconds: 8);
    final req = await client.getUrl(url);
    // Nominatim exige un User-Agent identificable.
    req.headers.set(HttpHeaders.userAgentHeader, 'domicilios_ubate/1.0');
    final resp = await req.close();
    if (resp.statusCode != 200) return const [];

    final body = await resp.transform(utf8.decoder).join();
    final lista = jsonDecode(body) as List<dynamic>;
    return lista
        .map((e) {
          final m = e as Map<String, dynamic>;
          return SugerenciaDireccion(
            nombre: (m['display_name'] ?? '').toString(),
            punto: LatLng(
              double.parse(m['lat'].toString()),
              double.parse(m['lon'].toString()),
            ),
          );
        })
        .toList();
  } catch (_) {
    return const [];
  } finally {
    client?.close();
  }
}

/// Reverse geocoding: convierte un [LatLng] en una dirección legible.
///
/// Usa Nominatim reverse. Devuelve la dirección "amigable" (calle + número +
/// barrio + ciudad, sin el país) o `null` si no se pudo resolver. Esto es lo
/// que permite que al tocar el mapa (o usar el GPS) se llene solo el campo de
/// dirección del checkout, como hace Rappi/Uber Eats.
Future<String?> direccionDe(LatLng punto) async {
  final url = Uri.parse(
    'https://nominatim.openstreetmap.org/reverse'
    '?lat=${punto.latitude}&lon=${punto.longitude}'
    '&format=jsonv2&accept-language=es&zoom=18&addressdetails=1',
  );

  HttpClient? client;
  try {
    client = HttpClient()..connectionTimeout = const Duration(seconds: 8);
    final req = await client.getUrl(url);
    req.headers.set(HttpHeaders.userAgentHeader, 'domicilios_ubate/1.0');
    final resp = await req.close();
    if (resp.statusCode != 200) return null;

    final body = await resp.transform(utf8.decoder).join();
    final m = jsonDecode(body) as Map<String, dynamic>;
    final addr = (m['address'] ?? const {}) as Map<String, dynamic>;

    // Construir una dirección corta y útil. Si Nominatim trajo calle + número,
    // la usamos preferentemente; si no, caemos al display_name truncado.
    final calle = (addr['road'] ?? addr['pedestrian'] ?? '').toString();
    final numero = (addr['house_number'] ?? '').toString();
    final barrio = (addr['suburb'] ?? addr['neighbourhood'] ?? '').toString();
    final ciudad = (addr['city'] ??
            addr['town'] ??
            addr['village'] ??
            addr['municipality'] ??
            '')
        .toString();

    final partes = <String>[];
    if (calle.isNotEmpty) {
      partes.add(numero.isEmpty ? calle : '$calle #$numero');
    }
    if (barrio.isNotEmpty) partes.add(barrio);
    if (ciudad.isNotEmpty) partes.add(ciudad);

    if (partes.isNotEmpty) return partes.join(', ');

    // Fallback: display_name viene como "calle, barrio, ciudad, depto, país".
    // Quitamos los dos últimos para acortar.
    final display = (m['display_name'] ?? '').toString();
    if (display.isEmpty) return null;
    final segs = display.split(',').map((s) => s.trim()).toList();
    return segs.length > 3
        ? segs.take(segs.length - 2).join(', ')
        : display;
  } catch (_) {
    return null;
  } finally {
    client?.close();
  }
}
