import 'dart:async';
import 'dart:convert';
import 'dart:io';

/// Descubrimiento automático del backend en la red Wi-Fi actual.
///
/// Problema que resuelve: la IP del PC cambia cada vez que te mueves de red, y
/// tocaría reescribir la URL del servidor a mano. En vez de eso, la app mira su
/// propia IP (p. ej. `192.168.101.11`), deduce la subred (`192.168.101.0/24`) y
/// **prueba todos los hosts** preguntando por `/api/ping`. El primero que
/// responde con la firma de nuestro backend es el servidor.
///
/// Funciona solo en red local (cliente y backend en la misma Wi-Fi). Para
/// pruebas entre casas se usa la URL pública de ngrok, que no cambia sola.

/// Firma que devuelve `GET /api/ping` del backend (ver `routes/api.php`).
const _firma = 'domicilios-ubate';

/// Escanea la(s) subred(es) /24 del dispositivo buscando el backend en [puerto].
/// Devuelve la URL base (con `/api`) del primero que responde, o `null`.
Future<String?> descubrirServidorEnRed({
  int puerto = 8000,
  Duration timeoutHost = const Duration(milliseconds: 700),
}) async {
  final prefijos = await _prefijosDeRed();
  if (prefijos.isEmpty) return null;

  // Probar de a lotes para no abrir cientos de sockets a la vez; devolver
  // apenas un host del lote responda.
  const tamLote = 40;
  for (final prefijo in prefijos) {
    final hosts = [for (var i = 1; i <= 254; i++) '$prefijo.$i'];
    for (var inicio = 0; inicio < hosts.length; inicio += tamLote) {
      final fin =
          (inicio + tamLote) > hosts.length ? hosts.length : inicio + tamLote;
      final lote = hosts.sublist(inicio, fin);
      final resultados = await Future.wait(
        lote.map((h) => _esNuestroBackend(h, puerto, timeoutHost)),
      );
      for (var k = 0; k < resultados.length; k++) {
        if (resultados[k]) return 'http://${lote[k]}:$puerto/api';
      }
    }
  }
  return null;
}

/// Prefijos /24 (`a.b.c`) de las interfaces IPv4 privadas del dispositivo.
/// Ordena primero las `192.168.x` (Wi-Fi doméstica típica) para encontrar antes.
Future<List<String>> _prefijosDeRed() async {
  final prefijos = <String>{};
  try {
    final interfaces = await NetworkInterface.list(
      type: InternetAddressType.IPv4,
      includeLoopback: false,
      includeLinkLocal: false,
    );
    for (final ni in interfaces) {
      for (final addr in ni.addresses) {
        final ip = addr.address;
        if (!_esPrivada(ip)) continue;
        final partes = ip.split('.');
        if (partes.length == 4) {
          prefijos.add('${partes[0]}.${partes[1]}.${partes[2]}');
        }
      }
    }
  } catch (_) {
    // Sin permiso o sin interfaces: no se puede descubrir.
  }
  final lista = prefijos.toList()
    ..sort((a, b) {
      final pa = a.startsWith('192.168.') ? 0 : (a.startsWith('172.') ? 1 : 2);
      final pb = b.startsWith('192.168.') ? 0 : (b.startsWith('172.') ? 1 : 2);
      return pa.compareTo(pb);
    });
  return lista;
}

bool _esPrivada(String ip) {
  if (ip.startsWith('192.168.')) return true;
  if (ip.startsWith('10.')) return true;
  // 172.16.0.0 – 172.31.255.255
  if (ip.startsWith('172.')) {
    final segundo = int.tryParse(ip.split('.').elementAt(1)) ?? 0;
    return segundo >= 16 && segundo <= 31;
  }
  return false;
}

/// `true` si `http://host:puerto/api/ping` responde 200 con nuestra firma.
Future<bool> _esNuestroBackend(String host, int puerto, Duration timeout) =>
    respondeFirma('http://$host:$puerto/api', timeout: timeout);

/// `true` si `<urlBase>/ping` responde 200 con la firma de nuestro backend.
/// Usa un [HttpClient] con timeout corto propio (no el `connectTimeout` global
/// de dio, que es largo) para fallar rápido cuando el host no existe.
Future<bool> respondeFirma(
  String urlBase, {
  Duration timeout = const Duration(milliseconds: 1200),
}) async {
  final client = HttpClient()..connectionTimeout = timeout;
  try {
    final req = await client
        .getUrl(Uri.parse('$urlBase/ping'))
        .timeout(timeout);
    req.headers.set(HttpHeaders.acceptHeader, 'application/json');
    // Si la URL es de ngrok-free, salta su página de advertencia.
    req.headers.set('ngrok-skip-browser-warning', 'true');
    final res = await req.close().timeout(timeout);
    if (res.statusCode != 200) return false;
    final body = await res.transform(utf8.decoder).join().timeout(timeout);
    return body.contains(_firma);
  } catch (_) {
    return false;
  } finally {
    client.close(force: true);
  }
}
