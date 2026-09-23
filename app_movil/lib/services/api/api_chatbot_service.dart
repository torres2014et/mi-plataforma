import '../chatbot_service.dart';
import 'api_client.dart';

/// Implementación real de [ChatbotService] contra el backend Laravel.
///
/// A diferencia del resto de `Api*Service`, esta respuesta no viene envuelta
/// en `{ "data": ... }` (no es un API Resource): el controller devuelve
/// `{ "respuesta": "..." }` directo, igual para web y app.
class ApiChatbotService implements ChatbotService {
  final ApiClient _api;

  ApiChatbotService(this._api);

  @override
  Future<String> enviarMensaje(
    String mensaje, {
    int? restauranteId,
    List<({String rol, String texto})> historial = const [],
  }) async {
    try {
      final res = await _api.dio.post('/chatbot/mensaje', data: {
        'mensaje': mensaje,
        if (restauranteId != null) 'restaurante_id': restauranteId,
        if (historial.isNotEmpty)
          'historial': [
            for (final t in historial) {'rol': t.rol, 'texto': t.texto},
          ],
      });
      return res.data['respuesta'] as String;
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }
}
