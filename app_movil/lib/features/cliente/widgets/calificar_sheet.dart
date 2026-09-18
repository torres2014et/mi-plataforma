import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_colors.dart';
import '../../../providers/pedido_providers.dart';
import '../../../providers/services_providers.dart';
import '../../shared/widgets/brand_button.dart';

/// Abre la hoja para calificar un pedido. Devuelve `true` si se calificó.
Future<bool?> mostrarCalificarSheet(
  BuildContext context, {
  required int pedidoId,
}) {
  return showModalBottomSheet<bool>(
    context: context,
    isScrollControlled: true,
    backgroundColor: AppColors.surface,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
    ),
    builder: (_) => _CalificarSheet(pedidoId: pedidoId),
  );
}

class _CalificarSheet extends ConsumerStatefulWidget {
  final int pedidoId;
  const _CalificarSheet({required this.pedidoId});

  @override
  ConsumerState<_CalificarSheet> createState() => _CalificarSheetState();
}

class _CalificarSheetState extends ConsumerState<_CalificarSheet> {
  int _estrellas = 5;
  final _comentarioCtrl = TextEditingController();
  bool _enviando = false;

  @override
  void dispose() {
    _comentarioCtrl.dispose();
    super.dispose();
  }

  Future<void> _enviar() async {
    setState(() => _enviando = true);
    try {
      await ref.read(pedidoServiceProvider).calificarPedido(
            pedidoId: widget.pedidoId,
            estrellas: _estrellas,
            comentario: _comentarioCtrl.text.trim(),
          );
      // El detalle se actualiza solo por el stream en vivo; refrescar la lista.
      ref.invalidate(misPedidosProvider);
      if (mounted) Navigator.pop(context, true);
    } catch (e) {
      if (mounted) {
        setState(() => _enviando = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('No se pudo calificar: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    // Padding para que el teclado no tape el contenido.
    final bottom = MediaQuery.of(context).viewInsets.bottom;

    return Padding(
      padding: EdgeInsets.fromLTRB(24, 20, 24, 20 + bottom),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: AppColors.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 20),
          Text('¿Cómo estuvo tu pedido?',
              style: text.titleLarge?.copyWith(fontWeight: FontWeight.w800)),
          const SizedBox(height: 16),

          // Selector de estrellas
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(5, (i) {
              final valor = i + 1;
              final activa = valor <= _estrellas;
              return IconButton(
                onPressed: () => setState(() => _estrellas = valor),
                icon: Icon(
                  activa ? Icons.star_rounded : Icons.star_outline_rounded,
                  color: AppColors.star,
                  size: 40,
                ),
              );
            }),
          ),
          const SizedBox(height: 12),

          TextField(
            controller: _comentarioCtrl,
            maxLines: 3,
            decoration: const InputDecoration(
              hintText: 'Cuéntanos algo (opcional)',
            ),
          ),
          const SizedBox(height: 20),

          BrandButton(
            label: 'Enviar calificación',
            cargando: _enviando,
            onPressed: _enviar,
          ),
        ],
      ),
    );
  }
}
