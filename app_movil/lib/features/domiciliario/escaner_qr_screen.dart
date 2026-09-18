import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../../core/theme/app_colors.dart';
import '../../providers/services_providers.dart';
import '../shared/widgets/brand_button.dart';

/// Pantalla de escaneo del QR de confirmación de entrega. Recibe el código
/// esperado y, al detectar un QR (o tras escribirlo a mano) que coincida,
/// cierra la entrega y vuelve con `true`. Si no coincide, muestra error y
/// sigue escaneando.
///
/// Tiene un botón "Escribir código" como respaldo: útil para probar todo el
/// flujo en un solo teléfono (ya que la cámara no puede escanear el QR del
/// propio dispositivo).
class EscanerQrScreen extends ConsumerStatefulWidget {
  final int pedidoId;
  final String codigoEsperado;

  const EscanerQrScreen({
    super.key,
    required this.pedidoId,
    required this.codigoEsperado,
  });

  @override
  ConsumerState<EscanerQrScreen> createState() => _EscanerQrScreenState();
}

class _EscanerQrScreenState extends ConsumerState<EscanerQrScreen> {
  final MobileScannerController _scanner = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    facing: CameraFacing.back,
  );

  // Para no procesar el mismo escaneo varias veces mientras se confirma.
  bool _procesando = false;
  String? _ultimoError;

  @override
  void dispose() {
    _scanner.dispose();
    super.dispose();
  }

  Future<void> _verificar(String leido) async {
    if (_procesando) return;
    final esperado = widget.codigoEsperado.trim().toUpperCase();
    final entrada = leido.trim().toUpperCase();

    if (entrada != esperado) {
      // No coincide: marca el error y permite reintentar.
      HapticFeedback.heavyImpact();
      setState(() => _ultimoError = 'El código no coincide con este pedido.');
      return;
    }

    setState(() {
      _procesando = true;
      _ultimoError = null;
    });
    HapticFeedback.mediumImpact();

    try {
      await ref.read(pedidoServiceProvider).confirmarEntrega(widget.pedidoId);
      if (mounted) Navigator.of(context).pop(true);
    } catch (e) {
      if (mounted) {
        setState(() {
          _procesando = false;
          _ultimoError = 'No se pudo confirmar: $e';
        });
      }
    }
  }

  Future<void> _abrirEscribirCodigo() async {
    final controller = TextEditingController();
    final value = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Escribir código'),
        content: TextField(
          controller: controller,
          autofocus: true,
          maxLength: 8,
          textCapitalization: TextCapitalization.characters,
          inputFormatters: [
            FilteringTextInputFormatter.allow(RegExp(r'[A-Za-z0-9]')),
            UpperCaseFormatter(),
          ],
          decoration: const InputDecoration(
            hintText: 'EJ. BURGER',
            counterText: '',
          ),
          style: const TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w800,
            letterSpacing: 4,
            fontFamilyFallback: ['monospace'],
          ),
          onSubmitted: (v) => Navigator.of(ctx).pop(v),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancelar'),
          ),
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(controller.text),
            child: const Text('Confirmar'),
          ),
        ],
      ),
    );
    if (value != null && value.trim().isNotEmpty) {
      await _verificar(value);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        foregroundColor: Colors.white,
        title: const Text('Escanear código'),
        elevation: 0,
      ),
      body: Stack(
        children: [
          // Cámara con detector.
          MobileScanner(
            controller: _scanner,
            onDetect: (capture) {
              for (final b in capture.barcodes) {
                final v = b.rawValue;
                if (v != null && v.isNotEmpty) {
                  _verificar(v);
                  return;
                }
              }
            },
          ),

          // Overlay con marco de mira y mensajes.
          IgnorePointer(
            child: Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black.withValues(alpha: 0.55),
                    Colors.transparent,
                    Colors.transparent,
                    Colors.black.withValues(alpha: 0.75),
                  ],
                  stops: const [0.0, 0.25, 0.6, 1.0],
                ),
              ),
            ),
          ),

          // Marco de mira centrado.
          Center(
            child: Container(
              width: 240,
              height: 240,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.brand, width: 3),
                boxShadow: [
                  BoxShadow(
                    color: AppColors.brand.withValues(alpha: 0.4),
                    blurRadius: 24,
                  ),
                ],
              ),
            ),
          ),

          // Pie con mensaje + botones.
          Positioned(
            left: 20,
            right: 20,
            bottom: 28,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                if (_ultimoError != null)
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 14, vertical: 10),
                    margin: const EdgeInsets.only(bottom: 14),
                    decoration: BoxDecoration(
                      color: AppColors.error.withValues(alpha: 0.18),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                          color: AppColors.error.withValues(alpha: 0.5)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.error_outline_rounded,
                            color: AppColors.error, size: 20),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(_ultimoError!,
                              style: const TextStyle(color: Colors.white)),
                        ),
                      ],
                    ),
                  )
                else
                  const Padding(
                    padding: EdgeInsets.only(bottom: 14),
                    child: Text(
                      'Apunta al QR que te muestra el cliente',
                      style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w600,
                          fontSize: 14),
                    ),
                  ),
                BrandButton(
                  label: 'Escribir código manualmente',
                  icono: Icons.keyboard_alt_outlined,
                  onPressed: _abrirEscribirCodigo,
                ),
              ],
            ),
          ),

          if (_procesando)
            const Positioned.fill(
              child: ColoredBox(
                color: Colors.black54,
                child: Center(child: CircularProgressIndicator()),
              ),
            ),
        ],
      ),
    );
  }
}

/// Pasa todo el texto a mayúsculas mientras el usuario escribe.
class UpperCaseFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
      TextEditingValue oldValue, TextEditingValue newValue) {
    return TextEditingValue(
      text: newValue.text.toUpperCase(),
      selection: newValue.selection,
    );
  }
}
