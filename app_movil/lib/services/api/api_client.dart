import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../core/config.dart';
import '../../core/utils/descubrir_servidor.dart' show descubrirServidorEnRed, respondeFirma;

/// Cliente HTTP compartido por todos los `Api*Service` (Fase 2).
///
/// - Configura `dio` con la URL base y los timeouts de [ApiConfig].
/// - Guarda el **token Sanctum** en almacenamiento seguro y lo inyecta como
///   `Authorization: Bearer <token>` en cada petición.
/// - Traduce los errores de red/validación del backend a un [Exception] con
///   mensaje legible para mostrar en la UI.
class ApiClient {
  final Dio dio;
  final FlutterSecureStorage _storage;
  String? _token;

  ApiClient._(this.dio, this._storage);

  static const _tokenKey = 'sanctum_token';
  static const _urlKey = 'api_base_url';

  factory ApiClient.crear() {
    final dio = Dio(BaseOptions(
      baseUrl: ApiConfig.baseUrl,
      connectTimeout: ApiConfig.timeout,
      receiveTimeout: ApiConfig.timeout,
      headers: {
        'Accept': 'application/json',
        // Evita la página de advertencia intersticial de ngrok-free (que
        // devolvería HTML en vez del JSON). Inofensivo fuera de ngrok.
        'ngrok-skip-browser-warning': 'true',
      },
    ));

    final client = ApiClient._(dio, const FlutterSecureStorage());

    dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) {
        if (client._token != null) {
          options.headers['Authorization'] = 'Bearer ${client._token}';
        }
        handler.next(options);
      },
    ));

    return client;
  }

  bool get tieneToken => _token != null;

  /// URL base que está usando ahora mismo (sin el sufijo, tal cual la usa dio).
  String get urlActual => dio.options.baseUrl;

  /// Carga la configuración persistida (al arrancar la app): la URL del servidor
  /// guardada (si la hay) y el token de sesión. Se llama una vez en `main()`
  /// antes de levantar la app, para que todo arranque ya apuntando al backend
  /// correcto y con la sesión lista para restaurarse.
  Future<void> cargarConfig() async {
    final urlGuardada = await _storage.read(key: _urlKey);
    if (urlGuardada != null && urlGuardada.isNotEmpty) {
      dio.options.baseUrl = urlGuardada;
    }
    _token = await _storage.read(key: _tokenKey);
  }

  /// Cambia la URL del servidor (la que escribe el usuario en el login) y la
  /// persiste. Normaliza la entrada con [ApiConfig.normalizarUrl]. Devuelve la
  /// URL final que quedó aplicada.
  Future<String> cambiarUrl(String entrada) async {
    final url = ApiConfig.normalizarUrl(entrada);
    dio.options.baseUrl = url;
    await _storage.write(key: _urlKey, value: url);
    return url;
  }

  /// Comprueba rápido si la URL actual responde (pega a `/ping`). Sirve para
  /// decidir si hay que autodescubrir el servidor tras cambiar de Wi-Fi. Usa un
  /// timeout corto propio (no el `connectTimeout` largo de dio) para que, si el
  /// servidor guardado ya no existe, falle en ~1 s y se pueda escanear la red.
  Future<bool> probarConexion() async {
    return respondeFirma(
      dio.options.baseUrl,
      timeout: const Duration(milliseconds: 1200),
    );
  }

  /// Busca el backend en la red Wi-Fi actual (escanea la subred). Si lo
  /// encuentra, aplica y persiste esa URL y la devuelve; si no, devuelve `null`.
  /// Así, al moverte de red, la app reencuentra el servidor sin tocar nada.
  Future<String?> autodescubrir({int puerto = 8000}) async {
    final url = await descubrirServidorEnRed(puerto: puerto);
    if (url == null) return null;
    dio.options.baseUrl = url;
    await _storage.write(key: _urlKey, value: url);
    return url;
  }

  /// Asegura una conexión utilizable: si la URL guardada no responde, intenta
  /// autodescubrir. Devuelve `true` si al final hay un servidor que responde.
  Future<bool> asegurarConexion() async {
    if (await probarConexion()) return true;
    final url = await autodescubrir();
    return url != null;
  }

  /// Carga el token persistido (al arrancar la app), si lo hay.
  Future<void> cargarToken() async {
    _token = await _storage.read(key: _tokenKey);
  }

  Future<void> guardarToken(String token) async {
    _token = token;
    await _storage.write(key: _tokenKey, value: token);
  }

  Future<void> limpiarToken() async {
    _token = null;
    await _storage.delete(key: _tokenKey);
  }

  /// Convierte una excepción de `dio` en un [Exception] con mensaje útil.
  /// Extrae `message` del JSON de Laravel (y el primer error de validación).
  static Exception comoError(Object e) {
    if (e is DioException) {
      final data = e.response?.data;
      if (data is Map) {
        // Errores de validación: { "errors": { "campo": ["msg"] } }
        final errores = data['errors'];
        if (errores is Map && errores.isNotEmpty) {
          final primero = errores.values.first;
          if (primero is List && primero.isNotEmpty) {
            return Exception(primero.first.toString());
          }
        }
        if (data['message'] != null) {
          return Exception(data['message'].toString());
        }
      }
      if (e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.connectionError) {
        return Exception('No se pudo conectar con el servidor. '
            'Revisa que el backend esté corriendo y la red.');
      }
      return Exception('Error de red (${e.response?.statusCode ?? '?'}).');
    }
    return Exception(e.toString());
  }
}
