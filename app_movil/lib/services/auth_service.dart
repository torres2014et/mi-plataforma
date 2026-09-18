import '../models/user.dart';
import '../models/user_role.dart';

/// Contrato de autenticación que conoce la UI.
///
/// Hoy lo implementa [FakeAuthService]. En la Fase 2 se crea un
/// `ApiAuthService implements AuthService` (con dio + Sanctum) y solo se cambia
/// el provider; las pantallas no se tocan.
abstract interface class AuthService {
  /// Inicia sesión. En la versión fake cualquier credencial es válida y el
  /// [rolDeseado] decide como qué entra el usuario.
  Future<User> login({
    required String email,
    required String password,
    required UserRole rolDeseado,
  });

  /// Registra un usuario nuevo y devuelve la sesión iniciada. En la versión
  /// fake no persiste nada: solo construye el [User] y lo entrega.
  Future<User> registrar({
    required String nombre,
    required String email,
    required String telefono,
    required String password,
    required UserRole rol,
  });

  /// Restaura la sesión guardada al abrir la app: si hay un token válido,
  /// devuelve el [User] correspondiente; si no hay sesión (o el token ya no
  /// vale), devuelve `null`. La versión fake no persiste nada, así que siempre
  /// devuelve `null` (hay que iniciar sesión en cada arranque).
  Future<User?> sesionActual();

  Future<void> logout();
}
