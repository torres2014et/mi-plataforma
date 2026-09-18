import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/login_screen.dart';
import '../../features/auth/register_screen.dart';
import '../../features/cliente/checkout_screen.dart';
import '../../features/cliente/cliente_home_screen.dart';
import '../../features/cliente/menu_screen.dart';
import '../../features/cliente/pedido_detalle_screen.dart';
import '../../features/domiciliario/domiciliario_home_screen.dart';
import '../../features/splash/splash_screen.dart';
import '../../models/user_role.dart';
import '../../providers/auth_provider.dart';

/// Rutas con nombre, para no repetir strings sueltos por el código.
abstract final class Rutas {
  static const splash = '/splash';
  static const login = '/login';
  static const registro = '/registro';

  // Cliente
  static const cliente = '/cliente';
  static const checkout = '/cliente/checkout';
  static String menu(int restauranteId) => '/cliente/restaurante/$restauranteId';
  static String pedido(int pedidoId) => '/cliente/pedido/$pedidoId';

  // Domiciliario
  static const domiciliario = '/domiciliario';
}

/// Envuelve una pantalla con una transición suave (fundido + leve deslizamiento
/// hacia arriba) que se aplica cada vez que se navega a una vista.
CustomTransitionPage<void> _pagina(GoRouterState state, Widget child) {
  return CustomTransitionPage<void>(
    key: state.pageKey,
    transitionDuration: const Duration(milliseconds: 320),
    reverseTransitionDuration: const Duration(milliseconds: 220),
    child: child,
    transitionsBuilder: (context, animation, secondaryAnimation, child) {
      final curva =
          CurvedAnimation(parent: animation, curve: Curves.easeOutCubic);
      return FadeTransition(
        opacity: curva,
        child: SlideTransition(
          position: Tween<Offset>(
            begin: const Offset(0, 0.03),
            end: Offset.zero,
          ).animate(curva),
          child: child,
        ),
      );
    },
  );
}

/// Router de la app. Redirige según la sesión y el rol:
/// - sin sesión   → /login
/// - cliente      → /cliente (y sus sub-rutas)
/// - domiciliario → /domiciliario
///
/// `refreshListenable` hace que go_router reevalúe la redirección cada vez que
/// cambia el estado de autenticación (login / logout).
final routerProvider = Provider<GoRouter>((ref) {
  final refresh = ValueNotifier<int>(0);
  ref.listen(authProvider, (_, __) => refresh.value++);
  ref.onDispose(refresh.dispose);

  return GoRouter(
    initialLocation: Rutas.splash,
    refreshListenable: refresh,
    redirect: (context, state) {
      final user = ref.read(authProvider);
      final loc = state.matchedLocation;
      final enSplash = loc == Rutas.splash;
      final enLogin = loc == Rutas.login;
      final enRegistro = loc == Rutas.registro;

      // El splash decide solo cuándo navegar; no lo redirigimos.
      if (enSplash) return null;

      // Sin sesión: solo el login y el registro.
      if (user == null) {
        return (enLogin || enRegistro) ? null : Rutas.login;
      }

      // Con sesión, login/registro ya no aplican: al home del rol.
      if (enRegistro) {
        return user.rol == UserRole.domiciliario
            ? Rutas.domiciliario
            : Rutas.cliente;
      }

      final esDomiciliario = user.rol == UserRole.domiciliario;
      final destino =
          esDomiciliario ? Rutas.domiciliario : Rutas.cliente;

      // Si está en el login, lo mandamos a su home.
      if (enLogin) return destino;

      // Evitar que un rol entre a la sección del otro (pero sí permitir
      // navegar entre las sub-rutas de su propia sección).
      if (esDomiciliario && loc.startsWith(Rutas.cliente)) {
        return Rutas.domiciliario;
      }
      if (!esDomiciliario && loc.startsWith(Rutas.domiciliario)) {
        return Rutas.cliente;
      }
      return null;
    },
    routes: [
      GoRoute(
        path: Rutas.splash,
        pageBuilder: (context, state) => _pagina(state, const SplashScreen()),
      ),
      GoRoute(
        path: Rutas.login,
        pageBuilder: (context, state) => _pagina(state, const LoginScreen()),
      ),
      GoRoute(
        path: Rutas.registro,
        pageBuilder: (context, state) =>
            _pagina(state, const RegisterScreen()),
      ),
      GoRoute(
        path: Rutas.cliente,
        pageBuilder: (context, state) =>
            _pagina(state, const ClienteHomeScreen()),
        routes: [
          GoRoute(
            path: 'restaurante/:id',
            pageBuilder: (context, state) {
              final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
              return _pagina(state, MenuScreen(restauranteId: id));
            },
          ),
          GoRoute(
            path: 'checkout',
            pageBuilder: (context, state) =>
                _pagina(state, const CheckoutScreen()),
          ),
          GoRoute(
            path: 'pedido/:id',
            pageBuilder: (context, state) {
              final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
              return _pagina(state, PedidoDetalleScreen(pedidoId: id));
            },
          ),
        ],
      ),
      GoRoute(
        path: Rutas.domiciliario,
        pageBuilder: (context, state) =>
            _pagina(state, const DomiciliarioHomeScreen()),
      ),
    ],
  );
});
