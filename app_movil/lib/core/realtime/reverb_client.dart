import 'dart:async';
import 'dart:convert';

import 'package:web_socket_channel/web_socket_channel.dart';

import '../../services/api/api_client.dart';
import '../config.dart';

/// Cliente mínimo del **protocolo Pusher** (el que habla Laravel Reverb) en
/// **Dart puro** (sin plugin nativo), para la Fase 3.
///
/// Hace el handshake, autentica canales **privados** contra
/// `POST /broadcasting/auth` usando el token Sanctum del [ApiClient], se
/// suscribe y expone cada evento del canal como un `Stream`. Responde los
/// `pusher:ping` para mantener viva la conexión.
///
/// Diseñado para fallar suave: [conectar] devuelve `null` si no logra el
/// handshake a tiempo, y quien lo use cae a *polling* (Fase 2) sin romperse.
class ReverbClient {
  final ApiClient _api;
  final WebSocketChannel _ch;
  final Stream<dynamic> _frames; // broadcast de frames entrantes (ya en texto)
  final String socketId;

  ReverbClient._(this._api, this._ch, this._frames, this.socketId);

  /// Intenta conectar y completar el handshake (`pusher:connection_established`).
  /// Devuelve `null` ante cualquier fallo o si excede [timeout].
  static Future<ReverbClient?> conectar(
    ApiClient api, {
    Duration timeout = const Duration(seconds: 6),
  }) async {
    try {
      final wsUrl = ApiConfig.reverbWsUrl(api.urlActual);
      final ch = WebSocketChannel.connect(Uri.parse(wsUrl));
      await ch.ready.timeout(timeout);

      final frames = ch.stream.asBroadcastStream();
      final establecido = await frames
          .firstWhere((m) => _nombreEvento(m) == 'pusher:connection_established')
          .timeout(timeout);

      final data = jsonDecode(jsonDecode(establecido as String)['data'] as String);
      final socketId = data['socket_id'] as String;

      final client = ReverbClient._(api, ch, frames, socketId);
      return client;
    } catch (_) {
      return null;
    }
  }

  /// Se suscribe a un canal **privado** (p. ej. `private-pedido.5`) y emite un
  /// evento por cada mensaje del canal (sin importar su nombre): la idea es usar
  /// la señal como "algo cambió" y refrescar el dato por REST. Lanza si la
  /// autenticación del canal falla (quien llama puede caer a polling).
  Stream<Map<String, dynamic>> suscribirPrivado(String canal) async* {
    // Firma del canal privado: la pide el backend con el token Sanctum.
    final res = await _api.dio.post('/broadcasting/auth', data: {
      'socket_id': socketId,
      'channel_name': canal,
    });
    final auth = (res.data as Map)['auth'];

    _ch.sink.add(jsonEncode({
      'event': 'pusher:subscribe',
      'data': {'auth': auth, 'channel': canal},
    }));

    await for (final raw in _frames) {
      final msg = jsonDecode(raw as String) as Map<String, dynamic>;
      final ev = msg['event'] as String?;
      if (ev == null) continue;

      if (ev.startsWith('pusher:')) {
        if (ev == 'pusher:ping') {
          _ch.sink.add(jsonEncode({'event': 'pusher:pong', 'data': {}}));
        }
        continue;
      }
      if (msg['channel'] == canal) {
        yield msg;
      }
    }
  }

  Future<void> cerrar() async {
    try {
      await _ch.sink.close();
    } catch (_) {
      // cerrar es best-effort
    }
  }

  static String? _nombreEvento(dynamic frame) {
    try {
      return (jsonDecode(frame as String) as Map<String, dynamic>)['event']
          as String?;
    } catch (_) {
      return null;
    }
  }
}
