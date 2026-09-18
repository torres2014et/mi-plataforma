import 'package:dio/dio.dart';

import '../../models/user.dart';
import '../../models/user_role.dart';
import '../auth_service.dart';
import 'api_client.dart';

/// Implementación real de [AuthService] contra el backend Laravel (Sanctum).
///
/// Guarda el token en [ApiClient] tras un login/registro exitoso y lo borra al
/// cerrar sesión. Las pantallas no cambian: siguen usando la interfaz.
class ApiAuthService implements AuthService {
  final ApiClient _api;

  ApiAuthService(this._api);

  @override
  Future<User> login({
    required String email,
    required String password,
    required UserRole rolDeseado,
  }) async {
    try {
      final res = await _api.dio.post('/login', data: {
        'email': email.trim(),
        'password': password,
        'device_name': 'app-movil',
      });
      final data = res.data as Map<String, dynamic>;
      await _api.guardarToken(data['token'] as String);
      return User.fromJson(data['user'] as Map<String, dynamic>);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<User> registrar({
    required String nombre,
    required String email,
    required String telefono,
    required String password,
    required UserRole rol,
  }) async {
    try {
      final res = await _api.dio.post('/register', data: {
        'name': nombre.trim(),
        'email': email.trim(),
        'telefono': telefono.trim().isEmpty ? null : telefono.trim(),
        'password': password,
        'password_confirmation': password,
        'rol': rol.apiValue,
      });
      final data = res.data as Map<String, dynamic>;
      await _api.guardarToken(data['token'] as String);
      return User.fromJson(data['user'] as Map<String, dynamic>);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<User?> sesionActual() async {
    // Sin token guardado no hay nada que restaurar.
    if (!_api.tieneToken) return null;
    try {
      final res = await _api.dio.get('/me');
      final body = res.data;
      // `/me` devuelve un UserResource (envuelto en `{ "data": {...} }`).
      final userJson = (body is Map && body['data'] is Map)
          ? body['data'] as Map<String, dynamic>
          : body as Map<String, dynamic>;
      return User.fromJson(userJson);
    } on DioException catch (e) {
      // Token vencido/revocado: lo limpiamos para no reintentar en cada arranque.
      if (e.response?.statusCode == 401) {
        await _api.limpiarToken();
      }
      // Cualquier otro fallo (sin red, servidor caído): no restauramos, pero
      // dejamos el token por si la próxima vez sí hay conexión.
      return null;
    } catch (_) {
      return null;
    }
  }

  @override
  Future<void> logout() async {
    try {
      await _api.dio.post('/logout');
    } catch (_) {
      // Aunque falle la llamada (sin red), limpiamos la sesión local igual.
    } finally {
      await _api.limpiarToken();
    }
  }
}
