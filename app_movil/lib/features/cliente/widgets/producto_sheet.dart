import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/utils/formato.dart';
import '../../../models/grupo_opciones.dart';
import '../../../models/opcion_producto.dart';
import '../../../models/producto.dart';
import '../../../models/restaurante.dart';
import '../../../providers/carrito_provider.dart';
import '../../shared/widgets/brand_button.dart';
import '../../shared/widgets/imagen_placeholder.dart';
import 'cantidad_stepper.dart';

/// Abre el personalizador de un [producto] como bottom sheet: permite elegir
/// ingredientes/adiciones/tamaño, la cantidad y agregarlo al carrito.
Future<void> mostrarProductoSheet(
  BuildContext context,
  WidgetRef ref, {
  required Producto producto,
  required Restaurante restaurante,
}) {
  return showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (_) =>
        _ProductoSheet(producto: producto, restaurante: restaurante),
  );
}

/// Pide confirmación si el carrito ya tiene productos de OTRO restaurante.
/// Devuelve `true` si se puede continuar (mismo restaurante, carrito vacío, o
/// el usuario aceptó vaciarlo).
Future<bool> confirmarCarritoOtroRestaurante(
  BuildContext context,
  WidgetRef ref,
  Restaurante restaurante,
) async {
  final carrito = ref.read(carritoProvider);
  if (carrito.isEmpty || carrito.restauranteId == restaurante.id) return true;

  final ok = await showDialog<bool>(
    context: context,
    builder: (ctx) => AlertDialog(
      backgroundColor: AppColors.surface,
      title: const Text('Empezar un carrito nuevo'),
      content: Text(
        'Tu carrito tiene productos de ${carrito.restauranteNombre}. '
        '¿Quieres vaciarlo y pedir de ${restaurante.nombre}?',
      ),
      actions: [
        TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancelar')),
        TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Vaciar y agregar')),
      ],
    ),
  );
  return ok == true;
}

class _ProductoSheet extends ConsumerStatefulWidget {
  final Producto producto;
  final Restaurante restaurante;

  const _ProductoSheet({required this.producto, required this.restaurante});

  @override
  ConsumerState<_ProductoSheet> createState() => _ProductoSheetState();
}

class _ProductoSheetState extends ConsumerState<_ProductoSheet> {
  /// grupoId → ids de opciones elegidas.
  late final Map<int, Set<int>> _seleccion;
  int _cantidad = 1;

  @override
  void initState() {
    super.initState();
    _seleccion = {
      for (final g in widget.producto.gruposOpciones)
        g.id: {for (final o in g.seleccionInicial) o.id},
    };
  }

  List<OpcionProducto> get _opcionesElegidas {
    final res = <OpcionProducto>[];
    for (final g in widget.producto.gruposOpciones) {
      final ids = _seleccion[g.id] ?? const <int>{};
      res.addAll(g.opciones.where((o) => ids.contains(o.id)));
    }
    return res;
  }

  double get _precioUnitario =>
      widget.producto.precio +
      _opcionesElegidas.fold(0.0, (a, o) => a + o.precioExtra);

  bool _grupoCompleto(GrupoOpciones g) {
    final n = _seleccion[g.id]?.length ?? 0;
    return n >= g.seleccionMin && n <= g.seleccionMax;
  }

  bool get _valido => widget.producto.gruposOpciones.every(_grupoCompleto);

  void _toggle(GrupoOpciones g, OpcionProducto o) {
    setState(() {
      final set = _seleccion.putIfAbsent(g.id, () => <int>{});
      if (g.esUnica) {
        if (set.contains(o.id)) {
          if (!g.obligatorio) set.clear(); // opcional → permite deseleccionar
        } else {
          set
            ..clear()
            ..add(o.id);
        }
      } else {
        if (set.contains(o.id)) {
          set.remove(o.id);
        } else if (set.length < g.seleccionMax) {
          set.add(o.id);
        }
      }
    });
  }

  Future<void> _agregar() async {
    final messenger = ScaffoldMessenger.of(context);
    final ok = await confirmarCarritoOtroRestaurante(
        context, ref, widget.restaurante);
    if (!ok || !mounted) return;

    ref.read(carritoProvider.notifier).agregar(
          widget.producto,
          widget.restaurante,
          opciones: _opcionesElegidas,
          cantidad: _cantidad,
        );

    Navigator.pop(context);
    messenger.showSnackBar(
      SnackBar(
        content: Text('$_cantidad× ${widget.producto.nombre} en el carrito'),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final p = widget.producto;
    final maxH = MediaQuery.of(context).size.height * 0.9;

    return Container(
      constraints: BoxConstraints(maxHeight: maxH),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          _CabeceraProducto(producto: p),
          // Contenido desplazable: descripción + grupos de opciones.
          Flexible(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(20, 4, 20, 16),
              children: [
                if (p.descripcion.isNotEmpty)
                  Text(
                    p.descripcion,
                    style: const TextStyle(
                        color: AppColors.textSecondary, height: 1.4),
                  ),
                for (final g in p.gruposOpciones)
                  _GrupoSection(
                    grupo: g,
                    seleccionadas: _seleccion[g.id] ?? const <int>{},
                    completo: _grupoCompleto(g),
                    onToggle: (o) => _toggle(g, o),
                  ),
              ],
            ),
          ),
          _BarraInferior(
            cantidad: _cantidad,
            total: _precioUnitario * _cantidad,
            habilitado: _valido,
            onMas: () => setState(() => _cantidad++),
            onMenos: () =>
                setState(() => _cantidad = _cantidad > 1 ? _cantidad - 1 : 1),
            onAgregar: _agregar,
          ),
        ],
      ),
    );
  }
}

/// Cabecera con imagen, nombre, precio base y botón de cerrar.
class _CabeceraProducto extends StatelessWidget {
  final Producto producto;
  const _CabeceraProducto({required this.producto});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        ClipRRect(
          borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
          child: Stack(
            children: [
              SizedBox(
                height: 150,
                width: double.infinity,
                child: ImagenPlaceholder(
                  texto: producto.nombre,
                  categoria: producto.categoria,
                  url: producto.imagenUrl,
                  iconSize: 52,
                ),
              ),
              Positioned.fill(
                child: DecoratedBox(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        Colors.black.withValues(alpha: 0.1),
                        Colors.black.withValues(alpha: 0.55),
                      ],
                    ),
                  ),
                ),
              ),
              Positioned(
                top: 10,
                right: 10,
                child: Material(
                  color: Colors.black.withValues(alpha: 0.4),
                  shape: const CircleBorder(),
                  child: InkWell(
                    customBorder: const CircleBorder(),
                    onTap: () => Navigator.pop(context),
                    child: const Padding(
                      padding: EdgeInsets.all(6),
                      child: Icon(Icons.close, color: Colors.white, size: 20),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 14, 20, 6),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Text(
                  producto.nombre,
                  style: const TextStyle(
                      fontWeight: FontWeight.w800, fontSize: 20),
                ),
              ),
              const SizedBox(width: 12),
              Text(
                formatoPesos(producto.precio),
                style: const TextStyle(
                    color: AppColors.brand,
                    fontWeight: FontWeight.w800,
                    fontSize: 17),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

/// Una sección de grupo: título + pista (obligatorio/opcional) + opciones.
class _GrupoSection extends StatelessWidget {
  final GrupoOpciones grupo;
  final Set<int> seleccionadas;
  final bool completo;
  final ValueChanged<OpcionProducto> onToggle;

  const _GrupoSection({
    required this.grupo,
    required this.seleccionadas,
    required this.completo,
    required this.onToggle,
  });

  String get _pista {
    if (grupo.esUnica) return grupo.obligatorio ? 'Elige 1' : 'Opcional';
    if (grupo.obligatorio) {
      return 'Elige de ${grupo.seleccionMin} a ${grupo.seleccionMax}';
    }
    return 'Opcional · hasta ${grupo.seleccionMax}';
  }

  @override
  Widget build(BuildContext context) {
    // Resaltar la pista en rojo si el grupo es obligatorio y aún falta elegir.
    final pendiente = grupo.obligatorio && !completo;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const SizedBox(height: 22),
        Row(
          children: [
            Expanded(
              child: Text(
                grupo.nombre,
                style: const TextStyle(
                    fontWeight: FontWeight.w800, fontSize: 16),
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
              decoration: BoxDecoration(
                color: pendiente
                    ? AppColors.error.withValues(alpha: 0.15)
                    : AppColors.surfaceVariant,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                _pista,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: pendiente ? AppColors.error : AppColors.textSecondary,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        for (final o in grupo.opciones)
          _OpcionTile(
            opcion: o,
            seleccionada: seleccionadas.contains(o.id),
            esUnica: grupo.esUnica,
            onTap: () => onToggle(o),
          ),
      ],
    );
  }
}

/// Fila de una opción: ícono radio/checkbox + nombre + precio extra.
class _OpcionTile extends StatelessWidget {
  final OpcionProducto opcion;
  final bool seleccionada;
  final bool esUnica;
  final VoidCallback onTap;

  const _OpcionTile({
    required this.opcion,
    required this.seleccionada,
    required this.esUnica,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final IconData icono = esUnica
        ? (seleccionada
            ? Icons.radio_button_checked
            : Icons.radio_button_unchecked)
        : (seleccionada ? Icons.check_box : Icons.check_box_outline_blank);

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 120),
        margin: const EdgeInsets.symmetric(vertical: 4),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        decoration: BoxDecoration(
          color: seleccionada
              ? AppColors.brand.withValues(alpha: 0.10)
              : AppColors.surfaceVariant,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: seleccionada ? AppColors.brand : AppColors.border,
            width: seleccionada ? 1.4 : 1,
          ),
        ),
        child: Row(
          children: [
            Icon(icono,
                size: 20,
                color: seleccionada ? AppColors.brand : AppColors.textMuted),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                opcion.nombre,
                style: TextStyle(
                  fontWeight: seleccionada ? FontWeight.w700 : FontWeight.w500,
                  color: AppColors.textPrimary,
                ),
              ),
            ),
            if (opcion.precioExtra > 0)
              Text(
                '+${formatoPesos(opcion.precioExtra)}',
                style: const TextStyle(
                    color: AppColors.textSecondary,
                    fontWeight: FontWeight.w600,
                    fontSize: 13),
              ),
          ],
        ),
      ),
    );
  }
}

/// Barra inferior: stepper de cantidad + CTA "Agregar · total".
class _BarraInferior extends StatelessWidget {
  final int cantidad;
  final double total;
  final bool habilitado;
  final VoidCallback onMas;
  final VoidCallback onMenos;
  final VoidCallback onAgregar;

  const _BarraInferior({
    required this.cantidad,
    required this.total,
    required this.habilitado,
    required this.onMas,
    required this.onMenos,
    required this.onAgregar,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
          child: Row(
            children: [
              CantidadStepper(
                cantidad: cantidad,
                onMas: onMas,
                onMenos: onMenos,
              ),
              const SizedBox(width: 14),
              Expanded(
                child: BrandButton(
                  label: 'Agregar · ${formatoPesos(total)}',
                  onPressed: habilitado ? onAgregar : null,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
