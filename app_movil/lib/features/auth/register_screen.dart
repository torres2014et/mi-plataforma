import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/theme/app_colors.dart';
import '../../models/user_role.dart';
import '../../providers/auth_provider.dart';
import '../shared/widgets/brand_button.dart';
import '../shared/widgets/logo_marca.dart';
import 'widgets/fondo_animado.dart';
import 'widgets/selector_rol.dart';
import 'widgets/tarjeta_vidrio.dart';

/// Registro de PRUEBA (Fase 1): crea la cuenta y entra directo. No persiste
/// nada; en la Fase 2 el `ApiAuthService` golpeará el endpoint de registro.
class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nombreCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _telefonoCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _pass2Ctrl = TextEditingController();
  UserRole _rol = UserRole.cliente;
  bool _cargando = false;
  String? _error;

  @override
  void dispose() {
    _nombreCtrl.dispose();
    _emailCtrl.dispose();
    _telefonoCtrl.dispose();
    _passCtrl.dispose();
    _pass2Ctrl.dispose();
    super.dispose();
  }

  Future<void> _registrar() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _cargando = true;
      _error = null;
    });
    try {
      await ref.read(authProvider.notifier).registrar(
            nombre: _nombreCtrl.text,
            email: _emailCtrl.text,
            telefono: _telefonoCtrl.text,
            password: _passCtrl.text,
            rol: _rol,
          );
      // El router redirige solo al detectar la sesión nueva.
    } catch (e) {
      if (mounted) setState(() => _error = 'No se pudo registrar: $e');
    } finally {
      if (mounted) setState(() => _cargando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;

    return Scaffold(
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        title: const Text('Crear cuenta'),
      ),
      body: FondoAnimado(
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(24, 8, 24, 24),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: TarjetaVidrio(
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        const Center(child: LogoMarca(size: 60)),
                        const SizedBox(height: 16),
                        Text('Únete a Domicilios Ubaté',
                            textAlign: TextAlign.center,
                            style: text.titleLarge
                                ?.copyWith(fontWeight: FontWeight.w800)),
                        const SizedBox(height: 4),
                        Text('Crea tu cuenta en segundos',
                            textAlign: TextAlign.center,
                            style: text.bodyMedium
                                ?.copyWith(color: AppColors.textSecondary)),
                        const SizedBox(height: 24),

                        Text('Quiero registrarme como',
                            style: text.labelLarge
                                ?.copyWith(color: AppColors.textSecondary)),
                        const SizedBox(height: 8),
                        SelectorRol(
                          rol: _rol,
                          onChanged: (r) => setState(() => _rol = r),
                        ),
                        const SizedBox(height: 18),

                        TextFormField(
                          controller: _nombreCtrl,
                          textCapitalization: TextCapitalization.words,
                          decoration: const InputDecoration(
                            labelText: 'Nombre completo',
                            prefixIcon: Icon(Icons.person_outline),
                          ),
                          validator: (v) => (v == null || v.trim().length < 3)
                              ? 'Escribe tu nombre'
                              : null,
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _emailCtrl,
                          keyboardType: TextInputType.emailAddress,
                          decoration: const InputDecoration(
                            labelText: 'Correo',
                            prefixIcon: Icon(Icons.email_outlined),
                          ),
                          validator: (v) {
                            final s = (v ?? '').trim();
                            if (s.isEmpty) return 'Escribe tu correo';
                            if (!s.contains('@') || !s.contains('.')) {
                              return 'Correo no válido';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _telefonoCtrl,
                          keyboardType: TextInputType.phone,
                          decoration: const InputDecoration(
                            labelText: 'Teléfono',
                            prefixIcon: Icon(Icons.phone_outlined),
                          ),
                          validator: (v) => (v == null || v.trim().length < 7)
                              ? 'Teléfono no válido'
                              : null,
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _passCtrl,
                          obscureText: true,
                          decoration: const InputDecoration(
                            labelText: 'Contraseña',
                            prefixIcon: Icon(Icons.lock_outline),
                          ),
                          validator: (v) => (v == null || v.length < 6)
                              ? 'Mínimo 6 caracteres'
                              : null,
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _pass2Ctrl,
                          obscureText: true,
                          decoration: const InputDecoration(
                            labelText: 'Confirmar contraseña',
                            prefixIcon: Icon(Icons.lock_reset_outlined),
                          ),
                          validator: (v) => (v != _passCtrl.text)
                              ? 'Las contraseñas no coinciden'
                              : null,
                        ),

                        if (_error != null) ...[
                          const SizedBox(height: 14),
                          Text(_error!,
                              style: const TextStyle(color: AppColors.error)),
                        ],

                        const SizedBox(height: 24),
                        BrandButton(
                          label: 'Crear cuenta',
                          cargando: _cargando,
                          onPressed: _registrar,
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'Modo prueba: la cuenta no se guarda todavía.',
                          textAlign: TextAlign.center,
                          style: text.bodySmall
                              ?.copyWith(color: AppColors.textMuted),
                        ),
                      ],
                    ),
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
