import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_colors.dart';
import '../../../providers/services_providers.dart';

/// Botón flotante que abre el chat con el asistente IA (mismo backend que el
/// widget de la web, `GeminiService` vía `POST /chatbot/mensaje`). Colocarlo
/// como `floatingActionButton` de un `Scaffold` en las pantallas donde deba
/// estar disponible el asistente.
///
/// [restauranteId] es opcional: pásalo cuando el usuario está viendo el menú
/// de un restaurante concreto, así el bot responde con ese catálogo como
/// contexto en vez del resumen general.
class ChatbotFab extends StatelessWidget {
  final int? restauranteId;
  const ChatbotFab({super.key, this.restauranteId});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Brand.c500, Brand.c600],
        ),
        boxShadow: [
          BoxShadow(
            color: Brand.c500.withValues(alpha: 0.4),
            blurRadius: 18,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: FloatingActionButton(
        heroTag: 'chatbot_fab',
        backgroundColor: Colors.transparent,
        elevation: 0,
        tooltip: 'Asistente Mi Plataforma',
        onPressed: () {
          HapticFeedback.lightImpact();
          _abrirChat(context, restauranteId: restauranteId);
        },
        child: const Icon(Icons.chat_bubble_outline_rounded,
            color: Colors.white),
      ),
    );
  }
}

void _abrirChat(BuildContext context, {int? restauranteId}) {
  showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    backgroundColor: AppColors.surface,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
    ),
    builder: (_) => _ChatbotSheet(restauranteId: restauranteId),
  );
}

class _Mensaje {
  final String texto;
  final bool esUsuario;
  const _Mensaje(this.texto, {required this.esUsuario});
}

class _ChatbotSheet extends ConsumerStatefulWidget {
  final int? restauranteId;
  const _ChatbotSheet({this.restauranteId});

  @override
  ConsumerState<_ChatbotSheet> createState() => _ChatbotSheetState();
}

class _ChatbotSheetState extends ConsumerState<_ChatbotSheet> {
  final _controller = TextEditingController();
  final _scrollController = ScrollController();
  final List<_Mensaje> _mensajes = [
    const _Mensaje(
      '¡Hola! Soy el asistente de Mi Plataforma. '
      'Pregúntame sobre restaurantes, menús, precios, horarios o tus pedidos 🍔',
      esUsuario: false,
    ),
  ];
  bool _cargando = false;

  static const _sugerencias = [
    '¿Qué restaurantes están abiertos ahora?',
    '¿Cuáles tienen domicilio gratis?',
    '¿Cómo va mi pedido?',
  ];

  @override
  void dispose() {
    _controller.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _scrollAbajo() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!_scrollController.hasClients) return;
      _scrollController.animateTo(
        _scrollController.position.maxScrollExtent,
        duration: const Duration(milliseconds: 200),
        curve: Curves.easeOut,
      );
    });
  }

  Future<void> _enviar([String? textoRapido]) async {
    final texto = (textoRapido ?? _controller.text).trim();
    if (texto.isEmpty || _cargando) return;

    // Turnos previos (sin el saludo inicial ni los errores locales) para que
    // el bot entienda preguntas de seguimiento. El backend usa los últimos 8.
    final previos = _mensajes.skip(1).toList();
    final recientes =
        previos.length > 8 ? previos.sublist(previos.length - 8) : previos;
    final historial = [
      for (final m in recientes)
        (rol: m.esUsuario ? 'user' : 'bot', texto: m.texto),
    ];

    setState(() {
      _mensajes.add(_Mensaje(texto, esUsuario: true));
      _controller.clear();
      _cargando = true;
    });
    _scrollAbajo();

    try {
      final respuesta = await ref
          .read(chatbotServiceProvider)
          .enviarMensaje(texto,
              restauranteId: widget.restauranteId, historial: historial);
      if (!mounted) return;
      setState(() => _mensajes.add(_Mensaje(respuesta, esUsuario: false)));
    } catch (_) {
      if (!mounted) return;
      setState(() => _mensajes.add(const _Mensaje(
          'Tuve un problema para responder. Intenta de nuevo en un momento.',
          esUsuario: false)));
    } finally {
      if (mounted) setState(() => _cargando = false);
      _scrollAbajo();
    }
  }

  @override
  Widget build(BuildContext context) {
    final alturaMax = MediaQuery.of(context).size.height * 0.75;
    final teclado = MediaQuery.of(context).viewInsets.bottom;

    return Padding(
      padding: EdgeInsets.only(bottom: teclado),
      child: ConstrainedBox(
        constraints: BoxConstraints(maxHeight: alturaMax),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Header
            Container(
              padding: const EdgeInsets.fromLTRB(20, 16, 12, 16),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Brand.c600, Brand.c500],
                ),
                borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.smart_toy_outlined,
                      color: Colors.white, size: 20),
                  const SizedBox(width: 8),
                  const Expanded(
                    child: Text(
                      'Asistente Mi Plataforma',
                      style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                          fontSize: 15),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.white),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                ],
              ),
            ),
            // Mensajes
            Flexible(
              child: ListView.builder(
                controller: _scrollController,
                padding: const EdgeInsets.all(14),
                itemCount: _mensajes.length + 1,
                itemBuilder: (context, i) {
                  if (i < _mensajes.length) {
                    return _Burbuja(mensaje: _mensajes[i]);
                  }
                  // Último elemento: sugerencias (solo sin conversación) o "escribiendo".
                  if (_cargando) return const _BurbujaEscribiendo();
                  if (_mensajes.length == 1) {
                    return Padding(
                      padding: const EdgeInsets.only(top: 6),
                      child: Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          for (final s in _sugerencias)
                            ActionChip(
                              label: Text(s),
                              onPressed: () => _enviar(s),
                              backgroundColor:
                                  Brand.c500.withValues(alpha: 0.12),
                              side: BorderSide(
                                  color: Brand.c500.withValues(alpha: 0.35)),
                              labelStyle: const TextStyle(
                                  color: Brand.c400,
                                  fontSize: 12.5,
                                  fontWeight: FontWeight.w600),
                            ),
                        ],
                      ),
                    );
                  }
                  return const SizedBox.shrink();
                },
              ),
            ),
            // Input
            Container(
              padding: const EdgeInsets.fromLTRB(12, 10, 12, 14),
              decoration: BoxDecoration(
                border: Border(top: BorderSide(color: AppColors.border)),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _controller,
                      enabled: !_cargando,
                      maxLength: 500,
                      textInputAction: TextInputAction.send,
                      onSubmitted: (_) => _enviar(),
                      buildCounter: (context,
                              {required currentLength,
                              required isFocused,
                              maxLength}) =>
                          null,
                      style: const TextStyle(color: AppColors.textPrimary),
                      decoration: InputDecoration(
                        hintText: 'Escribe tu pregunta...',
                        hintStyle:
                            const TextStyle(color: AppColors.textMuted),
                        filled: true,
                        fillColor: AppColors.inputFill,
                        contentPadding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 12),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(24),
                          borderSide: BorderSide.none,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  ValueListenableBuilder<TextEditingValue>(
                    valueListenable: _controller,
                    builder: (context, value, _) {
                      final habilitado =
                          !_cargando && value.text.trim().isNotEmpty;
                      return Container(
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          gradient: LinearGradient(
                            colors: habilitado
                                ? const [Brand.c500, Brand.c600]
                                : [
                                    AppColors.surfaceVariant,
                                    AppColors.surfaceVariant
                                  ],
                          ),
                        ),
                        child: IconButton(
                          icon: const Icon(Icons.send_rounded,
                              color: Colors.white, size: 20),
                          onPressed: habilitado ? _enviar : null,
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Burbuja extends StatelessWidget {
  final _Mensaje mensaje;
  const _Burbuja({required this.mensaje});

  @override
  Widget build(BuildContext context) {
    final esUsuario = mensaje.esUsuario;
    return Align(
      alignment: esUsuario ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 4),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        constraints: BoxConstraints(
            maxWidth: MediaQuery.of(context).size.width * 0.75),
        decoration: BoxDecoration(
          gradient: esUsuario
              ? const LinearGradient(colors: [Brand.c500, Brand.c600])
              : null,
          color: esUsuario ? null : AppColors.surfaceVariant,
          borderRadius: BorderRadius.circular(16),
          border: esUsuario ? null : Border.all(color: AppColors.border),
        ),
        child: Text(
          mensaje.texto,
          style: TextStyle(
            color: esUsuario ? Colors.white : AppColors.textPrimary,
            fontSize: 13.5,
            height: 1.35,
          ),
        ),
      ),
    );
  }
}

class _BurbujaEscribiendo extends StatelessWidget {
  const _BurbujaEscribiendo();

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 4),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: AppColors.surfaceVariant,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border),
        ),
        child: const Text('Escribiendo…',
            style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
      ),
    );
  }
}
