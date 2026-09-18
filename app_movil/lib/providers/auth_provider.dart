import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/user.dart';
import '../models/user_role.dart';
import 'services_providers.dart';

/// Estado de sesión: el [User] actual o `null` si no hay sesión.
///
/// El router (go_router) escucha este provider para redirigir según el rol.
class AuthNotifier extends Notifier<User?> {
  @override
  User? build() => null;

  /// Inicia sesión contra el backend. El rol real lo decide la **cuenta** (no el
  /// selector): si la cuenta no es del rol elegido en el selector, se rechaza y
  /// se limpia la sesión (así "Domiciliario" exige una cuenta de domiciliario).
  Future<void> login({
    required String email,
    required String password,
    required UserRole rolDeseado,
  }) async {
    final service = ref.read(authServiceProvider);
    final user = await service.login(
      email: email,
      password: password,
      rolDeseado: rolDeseado,
    );
    if (user.rol != rolDeseado) {
      // La cuenta existe pero es de otro rol: cerrar (borra el token guardado).
      await service.logout();
      throw Exception(
        'Esta cuenta es de ${user.rol.label}, no de ${rolDeseado.label}. '
        'Elige el rol correcto o entra con otra cuenta.',
      );
    }
    state = user;
    // Registra el token FCM ya con la sesión iniciada (no-op si FCM inactivo).
    await ref.read(fcmServiceProvider).registrar();
  }

  /// Registro de prueba. Crea el usuario y deja la sesión iniciada; el router
  /// redirige solo al home del rol correspondiente.
  Future<void> registrar({
    required String nombre,
    required String email,
    required String telefono,
    required String password,
    required UserRole rol,
  }) async {
    final service = ref.read(authServiceProvider);
    final user = await service.registrar(
      nombre: nombre,
      email: email,
      telefono: telefono,
      password: password,
      rol: rol,
    );
    state = user;
    await ref.read(fcmServiceProvider).registrar();
  }

  /// Restaura la sesión guardada al abrir la app (lo dispara el splash). Si hay
  /// un token válido en el teléfono, deja la sesión iniciada y el router redirige
  /// solo al home del rol; si no, no hace nada y se queda en el login. Nunca
  /// lanza: ante cualquier fallo, simplemente no restaura.
  Future<void> restaurarSesion() async {
    try {
      final user = await ref.read(authServiceProvider).sesionActual();
      if (user != null) {
        state = user;
        await ref.read(fcmServiceProvider).registrar();
      }
    } catch (_) {
      // Silencioso: si no se puede restaurar, el usuario inicia sesión normal.
    }
  }

  Future<void> logout() async {
    // Borra el token FCM mientras todavía hay sesión (el endpoint exige auth).
    await ref.read(fcmServiceProvider).eliminar();
    await ref.read(authServiceProvider).logout();
    state = null;
  }
}

final authProvider = NotifierProvider<AuthNotifier, User?>(AuthNotifier.new);

/// Atajos de conveniencia.
final estaAutenticadoProvider = Provider<bool>((ref) {
  return ref.watch(authProvider) != null;
});

final rolActualProvider = Provider<UserRole?>((ref) {
  return ref.watch(authProvider)?.rol;
});
