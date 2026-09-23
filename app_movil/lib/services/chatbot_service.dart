/// Contrato de acceso al asistente conversacional (chatbot IA).
///
/// Implementado por `ApiChatbotService` contra el mismo endpoint que usa la
/// web (`POST /chatbot/mensaje`), aquí autenticado con el token Sanctum en
/// vez de la sesión de Laravel. No hay versión `Fake`: el backend ya responde
/// con un mensaje de "no disponible" si `GEMINI_API_KEY` no está configurada,
/// así que no hace falta simular nada en la app.
abstract interface class ChatbotService {
  /// Envía [mensaje] del usuario y devuelve la respuesta en texto del bot.
  /// [restauranteId] es opcional: si se manda (por ejemplo, viendo el menú de
  /// un restaurante), el backend inyecta ese menú completo como contexto; si
  /// no, usa un resumen de los restaurantes activos.
  Future<String> enviarMensaje(String mensaje, {int? restauranteId});
}
