import '../../models/user.dart';
import '../../models/user_role.dart';
import '../auth_service.dart';

/// Login de prueba: acepta cualquier correo/contraseña y entra con el rol
/// elegido. Útil para probar las dos vistas (cliente / domiciliario) sin
/// backend.
class FakeAuthService implements AuthService {
  @override
  Future<User> login({
    required String email,
    required String password,
    required UserRole rolDeseado,
  }) async {
    await Future.delayed(const Duration(milliseconds: 900)); // simula la red

    // Nombre "bonito" derivado del correo, solo para la demo.
    final nombre = email.contains('@')
        ? email.split('@').first.replaceAll(RegExp(r'[._]'), ' ')
        : 'Usuario Demo';

    return User(
      id: rolDeseado == UserRole.domiciliario ? 2 : 1,
      nombre: _capitalizar(nombre),
      email: email,
      rol: rolDeseado,
      telefono: '300 000 0000',
    );
  }

  @override
  Future<User> registrar({
    required String nombre,
    required String email,
    required String telefono,
    required String password,
    required UserRole rol,
  }) async {
    await Future.delayed(const Duration(milliseconds: 1100)); // simula la red

    // En la demo reusamos los IDs conocidos (cliente = 1, domiciliario = 2)
    // para que los pedidos de prueba sigan asociándose bien.
    return User(
      id: rol == UserRole.domiciliario ? 2 : 1,
      nombre: _capitalizar(nombre.trim()),
      email: email.trim(),
      rol: rol,
      telefono: telefono.trim(),
    );
  }

  @override
  Future<User?> sesionActual() async {
    // La versión fake no persiste sesión: siempre se entra desde el login.
    return null;
  }

  @override
  Future<void> logout() async {
    await Future.delayed(const Duration(milliseconds: 300));
  }

  String _capitalizar(String texto) => texto
      .split(' ')
      .where((p) => p.isNotEmpty)
      .map((p) => p[0].toUpperCase() + p.substring(1))
      .join(' ');
}
