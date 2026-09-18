import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core/router/app_router.dart';
import 'core/theme/app_theme.dart';
import 'providers/services_providers.dart';
import 'services/api/api_client.dart';
import 'services/fcm_service.dart';
import 'services/local_notificacion_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Cliente HTTP único: carga la URL del servidor guardada y el token de sesión
  // ANTES de arrancar, para apuntar al backend correcto y poder restaurar la
  // sesión en el splash. Se reutiliza esta misma instancia en el provider.
  final apiClient = ApiClient.crear();
  await apiClient.cargarConfig();

  // Inicializa las notificaciones (y pide permiso) antes de arrancar, y
  // reutiliza esa misma instancia en el provider.
  final notificaciones = LocalNotificacionService();
  await notificaciones.init();

  // Push reales (FCM) — Fase 3, Paso 3. Inicializa Firebase y los listeners.
  // Falla suave si todavía no hay google-services.json: queda inactivo y la app
  // sigue con las notificaciones locales. El token se registra tras el login
  // (ver AuthNotifier). Se reutiliza esta instancia en el provider.
  final fcm = FcmService(apiClient, notificaciones);
  await fcm.init();

  runApp(
    ProviderScope(
      overrides: [
        apiClientProvider.overrideWithValue(apiClient),
        notificacionServiceProvider.overrideWithValue(notificaciones),
        fcmServiceProvider.overrideWithValue(fcm),
      ],
      child: const DomiciliosUbateApp(),
    ),
  );
}

class DomiciliosUbateApp extends ConsumerWidget {
  const DomiciliosUbateApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: 'Domicilios Ubaté',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.dark,
      routerConfig: router,
    );
  }
}
