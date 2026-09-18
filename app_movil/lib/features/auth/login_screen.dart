import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/router/app_router.dart';
import '../../core/theme/app_colors.dart';
import '../../models/user_role.dart';
import '../../providers/auth_provider.dart';
import '../../providers/services_providers.dart';
import '../shared/widgets/brand_button.dart';
import '../shared/widgets/logo_marca.dart';
import 'widgets/fondo_animado.dart';
import 'widgets/selector_rol.dart';
import 'widgets/tarjeta_vidrio.dart';

/// Login de PRUEBA (Fase 1): cualquier correo/contraseña entra. El selector
/// decide si entras como cliente o como domiciliario, para probar ambas vistas
/// sin backend.
class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _emailCtrl = TextEditingController(text: 'cliente@test.com');
  final _passCtrl = TextEditingController(text: 'password');
  UserRole _rol = UserRole.cliente;
  bool _cargando = false;
  String? _error;

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  /// Abre un diálogo para cambiar la URL del servidor (backend). Permite
  /// repartir el APK una vez y luego apuntarlo al túnel público (ngrok) sin
  /// recompilar. La URL se guarda en el teléfono. También trae un botón
  /// **"Buscar en la red"** que autodescubre el backend en la Wi-Fi actual
  /// (útil cuando cambias de red y la IP del PC cambió).
  Future<void> _configurarServidor() async {
    final api = ref.read(apiClientProvider);
    final ctrl = TextEditingController(text: api.urlActual);
    var buscando = false;

    final guardar = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialog) => AlertDialog(
          backgroundColor: AppColors.surface,
          title: const Text('Servidor'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Pega la dirección del backend (la URL del túnel ngrok o la IP '
                'del PC en la red). Se le agrega /api automáticamente.',
                style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
              ),
              const SizedBox(height: 14),
              TextField(
                controller: ctrl,
                keyboardType: TextInputType.url,
                autocorrect: false,
                decoration: const InputDecoration(
                  labelText: 'URL del servidor',
                  hintText: 'https://algo.ngrok-free.app',
                  prefixIcon: Icon(Icons.dns_outlined),
                ),
              ),
              const SizedBox(height: 10),
              Align(
                alignment: Alignment.centerLeft,
                child: TextButton.icon(
                  onPressed: buscando
                      ? null
                      : () async {
                          setDialog(() => buscando = true);
                          final url = await api.autodescubrir();
                          setDialog(() => buscando = false);
                          if (url != null) {
                            ctrl.text = url;
                          } else if (ctx.mounted) {
                            ScaffoldMessenger.of(ctx).showSnackBar(
                              const SnackBar(
                                content: Text(
                                    'No encontré el servidor en esta red. '
                                    'Revisa que el backend esté corriendo.'),
                              ),
                            );
                          }
                        },
                  icon: buscando
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.wifi_find_outlined, size: 18),
                  label: Text(buscando
                      ? 'Buscando en la red…'
                      : 'Buscar en la red'),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Cancelar'),
            ),
            TextButton(
              onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Guardar'),
            ),
          ],
        ),
      ),
    );

    if (guardar == true) {
      final url = await api.cambiarUrl(ctrl.text);
      if (mounted) {
        setState(() {}); // refresca la URL mostrada abajo
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Servidor: $url')),
        );
      }
    }
    ctrl.dispose();
  }

  Future<void> _entrar() async {
    setState(() {
      _cargando = true;
      _error = null;
    });
    try {
      // Si cambiaste de Wi-Fi y la IP del PC cambió, busca el servidor en la
      // red actual antes de intentar entrar (transparente para el usuario).
      final hayServidor = await ref.read(apiClientProvider).asegurarConexion();
      if (!hayServidor) {
        if (mounted) {
          setState(() => _error =
              'No encuentro el servidor en esta red. Revisa que el backend '
              'esté corriendo, o ponlo a mano en "Servidor".');
        }
        return;
      }
      await ref.read(authProvider.notifier).login(
            email: _emailCtrl.text.trim(),
            password: _passCtrl.text,
            rolDeseado: _rol,
          );
      // No navegamos a mano: el router redirige solo al detectar la sesión.
    } catch (e) {
      // Mensaje limpio (sin el prefijo "Exception:").
      final msg = e.toString().replaceFirst('Exception: ', '');
      if (mounted) setState(() => _error = msg);
    } finally {
      if (mounted) setState(() => _cargando = false);
    }
  }

  /// Correo de prueba sugerido para cada rol.
  static String _emailDePrueba(UserRole rol) =>
      rol == UserRole.domiciliario ? 'domiciliario@test.com' : 'cliente@test.com';

  /// Al cambiar el selector de rol, si el correo está vacío o aún es el de
  /// prueba del otro rol, lo cambia al del rol elegido (comodidad al probar).
  void _onRolChanged(UserRole r) {
    setState(() {
      final actual = _emailCtrl.text.trim();
      if (actual.isEmpty || actual == _emailDePrueba(_rol)) {
        _emailCtrl.text = _emailDePrueba(r);
      }
      _rol = r;
    });
  }

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;

    return Scaffold(
      body: FondoAnimado(
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: TarjetaVidrio(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Logo / marca
                      const Center(child: LogoMarca()),
                      const SizedBox(height: 20),
                      Text('Domicilios Ubaté',
                          textAlign: TextAlign.center,
                          style: text.headlineSmall
                              ?.copyWith(fontWeight: FontWeight.w800)),
                      const SizedBox(height: 4),
                      Text('Pide tu comida favorita en Ubaté',
                          textAlign: TextAlign.center,
                          style: text.bodyMedium
                              ?.copyWith(color: AppColors.textSecondary)),
                      const SizedBox(height: 28),

                      // Selector de rol (cliente / domiciliario)
                      Text('Entrar como',
                          style: text.labelLarge
                              ?.copyWith(color: AppColors.textSecondary)),
                      const SizedBox(height: 8),
                      SelectorRol(
                        rol: _rol,
                        onChanged: _onRolChanged,
                      ),
                      const SizedBox(height: 20),

                      TextField(
                        controller: _emailCtrl,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(
                          labelText: 'Correo',
                          prefixIcon: Icon(Icons.email_outlined),
                        ),
                      ),
                      const SizedBox(height: 14),
                      TextField(
                        controller: _passCtrl,
                        obscureText: true,
                        decoration: const InputDecoration(
                          labelText: 'Contraseña',
                          prefixIcon: Icon(Icons.lock_outline),
                        ),
                      ),

                      if (_error != null) ...[
                        const SizedBox(height: 14),
                        Text(_error!,
                            style: const TextStyle(color: AppColors.error)),
                      ],

                      const SizedBox(height: 24),
                      BrandButton(
                        label: 'Entrar',
                        cargando: _cargando,
                        onPressed: _entrar,
                      ),
                      const SizedBox(height: 18),

                      // Ir a registro
                      Wrap(
                        alignment: WrapAlignment.center,
                        crossAxisAlignment: WrapCrossAlignment.center,
                        children: [
                          Text('¿No tienes cuenta?',
                              style: text.bodyMedium
                                  ?.copyWith(color: AppColors.textSecondary)),
                          TextButton(
                            onPressed: _cargando
                                ? null
                                : () => context.push(Rutas.registro),
                            child: const Text('Regístrate'),
                          ),
                        ],
                      ),
                      // Configuración del servidor (backend). Discreta, abajo.
                      Center(
                        child: TextButton.icon(
                          onPressed: _cargando ? null : _configurarServidor,
                          icon: const Icon(Icons.dns_outlined, size: 16),
                          label: Text(
                            'Servidor: ${ref.watch(apiClientProvider).urlActual}',
                            style: text.bodySmall
                                ?.copyWith(color: AppColors.textMuted),
                          ),
                          style: TextButton.styleFrom(
                            foregroundColor: AppColors.textMuted,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
