import 'dart:async';
import 'dart:io' show Platform;

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/router/app_router.dart';
import '../../core/theme/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/services_providers.dart';
import '../shared/widgets/fondo_aurora.dart';

/// Splash inicial: el scooter de la marca entra acelerando desde la izquierda
/// con líneas de velocidad detrás; en el centro frena, aparece la insignia con
/// rebote y luego el nombre de la app. Al terminar navega a [Rutas.login] —
/// el redirect del router se encarga de mandarlo al destino real según sesión.
class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  Timer? _navTimer;
  // Preparación disparada al montar (asegura servidor + restaura sesión); se
  // espera antes de navegar para que el router ya tenga el estado (y no
  // parpadee el login si hay sesión).
  Future<void>? _preparar;

  // Fases de la animación, todas sobre el mismo controller con Intervals.
  late final Animation<double> _entrada;   // scooter entra
  late final Animation<double> _insignia;  // insignia rebota detrás
  late final Animation<double> _textos;    // título + tagline aparecen

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 2200),
    );

    _entrada = CurvedAnimation(
      parent: _ctrl,
      curve: const Interval(0.0, 0.45, curve: Curves.easeOutBack),
    );
    _insignia = CurvedAnimation(
      parent: _ctrl,
      curve: const Interval(0.40, 0.72, curve: Curves.elasticOut),
    );
    _textos = CurvedAnimation(
      parent: _ctrl,
      curve: const Interval(0.60, 0.95, curve: Curves.easeOut),
    );

    _ctrl.forward();

    // Mientras corre la animación: asegura que haya servidor (si cambiaste de
    // Wi-Fi, lo busca en la red) y luego restaura la sesión guardada.
    _preparar = _prepararSesion();

    _navTimer = Timer(const Duration(milliseconds: 2400), _continuar);
  }

  Future<void> _prepararSesion() async {
    // En tests no tocamos la red (evita timers de red pendientes al desmontar).
    if (!Platform.environment.containsKey('FLUTTER_TEST')) {
      // Si la URL guardada no responde (p. ej. cambiaste de red), autodescubre
      // el backend en la Wi-Fi actual antes de intentar restaurar la sesión.
      await ref.read(apiClientProvider).asegurarConexion();
    }
    await ref.read(authProvider.notifier).restaurarSesion();
  }

  /// Al terminar el splash, espera a que la preparación acabe (suele estar
  /// lista) y navega. El redirect del router decide el destino real según haya
  /// o no sesión.
  Future<void> _continuar() async {
    await _preparar;
    if (mounted) context.go(Rutas.login);
  }

  @override
  void dispose() {
    _navTimer?.cancel();
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: FondoAurora(
        child: Center(
          child: AnimatedBuilder(
            animation: _ctrl,
            builder: (context, _) {
              // dx: -260 (afuera, izquierda) → 0 (centro)
              final dx = -260 * (1 - _entrada.value);
              // Las líneas se desvanecen a medida que el scooter llega al centro.
              final opLineas = (1 - _entrada.value).clamp(0.0, 1.0);

              return SizedBox(
                width: 300,
                height: 320,
                child: Stack(
                  alignment: Alignment.center,
                  children: [
                    // Insignia detrás del scooter (aparece con bounce).
                    Transform.scale(
                      scale: _insignia.value.clamp(0.0, 1.0),
                      child: Opacity(
                        opacity: _insignia.value.clamp(0.0, 1.0),
                        child: _Insignia(size: 130),
                      ),
                    ),

                    // Scooter + líneas se mueven juntos en el Transform.
                    Transform.translate(
                      offset: Offset(dx, 0),
                      child: SizedBox(
                        width: 300,
                        height: 120,
                        child: CustomPaint(
                          painter: _LineasVelocidad(
                            t: _ctrl.value,
                            opacidad: opLineas,
                          ),
                          child: const Center(
                            child: Icon(
                              Icons.delivery_dining,
                              color: Colors.white,
                              size: 76,
                            ),
                          ),
                        ),
                      ),
                    ),

                    // Título y tagline abajo del logo.
                    Positioned(
                      bottom: 0,
                      left: 0,
                      right: 0,
                      child: Opacity(
                        opacity: _textos.value.clamp(0.0, 1.0),
                        child: Transform.translate(
                          offset: Offset(0, (1 - _textos.value) * 14),
                          child: const Column(
                            children: [
                              Text(
                                'Domicilios Ubaté',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w800,
                                  fontSize: 22,
                                  letterSpacing: -0.4,
                                ),
                              ),
                              SizedBox(height: 4),
                              Text(
                                'Comida que llega rápido',
                                style: TextStyle(
                                  color: AppColors.textMuted,
                                  fontSize: 13,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ),
    );
  }
}

/// Insignia con el degradado de marca (misma estética que [LogoMarca] pero sin
/// el ícono interno — el scooter llega por encima).
class _Insignia extends StatelessWidget {
  final double size;
  const _Insignia({required this.size});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Brand.c400, Brand.c600],
        ),
        borderRadius: BorderRadius.circular(size * 0.3),
        boxShadow: [
          BoxShadow(
            color: Brand.c500.withValues(alpha: 0.55),
            blurRadius: size * 0.4,
            offset: Offset(0, size * 0.12),
          ),
        ],
      ),
      foregroundDecoration: BoxDecoration(
        borderRadius: BorderRadius.circular(size * 0.3),
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [
            Colors.white.withValues(alpha: 0.22),
            Colors.transparent,
          ],
        ),
      ),
    );
  }
}

/// Dibuja 5 líneas blancas a la izquierda del centro que sugieren velocidad.
/// Se "mueven" con [t] (avance del controller) para dar sensación de rapidez,
/// y se atenúan con [opacidad] cuando el scooter frena en el centro.
class _LineasVelocidad extends CustomPainter {
  final double t;
  final double opacidad;
  _LineasVelocidad({required this.t, required this.opacidad});

  @override
  void paint(Canvas canvas, Size size) {
    if (opacidad <= 0) return;

    final cx = size.width / 2;
    final cy = size.height / 2;

    // (offset Y desde el centro, largo, alpha relativo)
    const lineas = <({double dy, double largo, double alpha})>[
      (dy: -28, largo: 60, alpha: 0.35),
      (dy: -14, largo: 90, alpha: 0.55),
      (dy: 0,   largo: 120, alpha: 0.80),
      (dy: 14,  largo: 85, alpha: 0.50),
      (dy: 28,  largo: 55, alpha: 0.30),
    ];

    final desplazamiento = t * 70;
    final paint = Paint()
      ..strokeWidth = 2.5
      ..strokeCap = StrokeCap.round;

    for (final l in lineas) {
      paint.color = Colors.white.withValues(alpha: l.alpha * opacidad);
      // Las líneas terminan justo antes del ícono (-40 del centro) y se
      // extienden hacia la izquierda.
      final endX = cx - 44 + desplazamiento * 0.15;
      final startX = endX - l.largo;
      canvas.drawLine(
        Offset(startX, cy + l.dy),
        Offset(endX, cy + l.dy),
        paint,
      );
    }
  }

  @override
  bool shouldRepaint(covariant _LineasVelocidad old) =>
      old.t != t || old.opacidad != opacidad;
}
