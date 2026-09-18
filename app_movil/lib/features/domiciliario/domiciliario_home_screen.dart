import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:latlong2/latlong.dart';

import '../../core/theme/app_colors.dart';
import '../../core/utils/formato.dart';
import '../../models/estado_pedido.dart';
import '../../models/medio_transporte.dart';
import '../../models/pedido.dart';
import '../../providers/auth_provider.dart';
import '../../providers/pedido_providers.dart';
import '../../providers/restaurante_providers.dart';
import '../../providers/services_providers.dart';
import '../shared/widgets/aparicion_animada.dart';
import '../shared/widgets/brand_button.dart';
import '../shared/widgets/estado_chip.dart';
import '../shared/widgets/fondo_aurora.dart';
import '../shared/widgets/mapa_pedido.dart';
import '../shared/widgets/perfil_card.dart';
import '../shared/widgets/shimmer_carga.dart';
import 'escaner_qr_screen.dart';

/// Home del domiciliario: toggle de disponibilidad, el pedido activo (con mapa
/// de ruta y botón para avanzar su estado) y el historial de entregas.
class DomiciliarioHomeScreen extends ConsumerWidget {
  const DomiciliarioHomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider);
    final asyncActivos = ref.watch(pedidosActivosProvider);
    final asyncDisponibles = ref.watch(pedidosDisponiblesProvider);
    final text = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(user?.nombre ?? 'Domiciliario',
                style: const TextStyle(
                    fontSize: 17, fontWeight: FontWeight.w700)),
            const Text('Repartidor',
                style:
                    TextStyle(fontSize: 12, color: AppColors.textSecondary)),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Cerrar sesión',
            icon: const Icon(Icons.logout),
            onPressed: () => ref.read(authProvider.notifier).logout(),
          ),
        ],
      ),
      body: FondoAurora(
        child: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(pedidosActivosProvider);
          ref.invalidate(pedidosDisponiblesProvider);
          ref.invalidate(historialDomiciliarioProvider);
        },
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            if (user != null) ...[
              PerfilCard(user: user),
              const SizedBox(height: 20),
            ],
            Text('Tu resumen',
                style: text.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
            const SizedBox(height: 12),
            const _Estadisticas(),
            const SizedBox(height: 24),
            const _DisponibilidadToggle(),
            const SizedBox(height: 16),
            const _SelectorMedio(),
            const SizedBox(height: 24),

            // ---- Mis pedidos activos ----
            Text('Mis pedidos activos',
                style: text.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
            const SizedBox(height: 12),
            asyncActivos.when(
              loading: () => const _CargandoSeccion(),
              error: (e, _) => Text('Error: $e',
                  style: const TextStyle(color: AppColors.error)),
              data: (pedidos) {
                if (pedidos.isEmpty) return const _SinPedido();
                return Column(
                  children: [
                    for (final (i, p) in pedidos.indexed)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 14),
                        child: AparicionAnimada(
                          delay: AparicionAnimada.escalonado(i),
                          child: _PedidoActivoCard(pedido: p),
                        ),
                      ),
                  ],
                );
              },
            ),

            const SizedBox(height: 28),
            // ---- Pedidos disponibles ----
            Row(
              children: [
                Text('Pedidos disponibles',
                    style: text.titleMedium
                        ?.copyWith(fontWeight: FontWeight.w700)),
                const SizedBox(width: 8),
                asyncDisponibles.maybeWhen(
                  data: (p) => p.isEmpty
                      ? const SizedBox.shrink()
                      : _Contador(p.length),
                  orElse: () => const SizedBox.shrink(),
                ),
              ],
            ),
            const SizedBox(height: 12),
            asyncDisponibles.when(
              loading: () => const _CargandoSeccion(),
              error: (e, _) => Text('Error: $e',
                  style: const TextStyle(color: AppColors.error)),
              data: (pedidos) {
                if (pedidos.isEmpty) {
                  return const Padding(
                    padding: EdgeInsets.symmetric(vertical: 16),
                    child: Text('No hay pedidos disponibles ahora mismo.',
                        style: TextStyle(color: AppColors.textSecondary)),
                  );
                }
                return Column(
                  children: [
                    for (final (i, p) in pedidos.indexed)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 14),
                        child: AparicionAnimada(
                          delay: AparicionAnimada.escalonado(i),
                          child: _PedidoDisponibleCard(pedido: p),
                        ),
                      ),
                  ],
                );
              },
            ),

            const SizedBox(height: 28),
            Text('Historial de entregas',
                style: text.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
            const SizedBox(height: 12),
            const _HistorialDomiciliario(),
          ],
        ),
        ),
      ),
    );
  }
}

/// Panel con las estadísticas del domiciliario: entregas, ingresos y activos.
class _Estadisticas extends ConsumerWidget {
  const _Estadisticas();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final e = ref.watch(estadisticasDomiciliarioProvider);
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _StatCard(
                icono: Icons.check_circle_outline,
                valor: '${e.entregas}',
                label: 'Entregas',
                color: AppColors.success,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _StatCard(
                icono: Icons.payments_outlined,
                valor: formatoPesos(e.ingresos),
                label: 'Ingresos',
                color: AppColors.brand,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _StatCard(
                icono: Icons.delivery_dining,
                valor: '${e.activos}',
                label: 'En curso',
                color: AppColors.info,
              ),
            ),
          ],
        ),
        if (e.entregas > 0) ...[
          const SizedBox(height: 10),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.border),
            ),
            child: Row(
              children: [
                const Icon(Icons.insights, size: 16, color: AppColors.textSecondary),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Promedio ${formatoPesos(e.promedioPorEntrega)} por entrega · '
                    '${formatoPesos(e.valorEntregado)} en pedidos repartidos',
                    style: const TextStyle(
                        color: AppColors.textSecondary, fontSize: 12.5),
                  ),
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }
}

/// Tarjeta individual de una estadística (ícono + valor + etiqueta).
class _StatCard extends StatelessWidget {
  final IconData icono;
  final String valor;
  final String label;
  final Color color;

  const _StatCard({
    required this.icono,
    required this.valor,
    required this.label,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        children: [
          Icon(icono, color: color, size: 22),
          const SizedBox(height: 8),
          FittedBox(
            fit: BoxFit.scaleDown,
            child: Text(valor,
                style: const TextStyle(
                    fontWeight: FontWeight.w800, fontSize: 18)),
          ),
          const SizedBox(height: 2),
          Text(label,
              style: const TextStyle(
                  color: AppColors.textSecondary, fontSize: 12)),
        ],
      ),
    );
  }
}

/// Selector del medio de transporte del domiciliario. Lo elegido se aplica a
/// los pedidos que acepte (define velocidad e ícono de la entrega).
class _SelectorMedio extends ConsumerWidget {
  const _SelectorMedio();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final medioActual = ref.watch(medioDomiciliarioProvider);
    final text = Theme.of(context).textTheme;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Tu medio de transporte',
              style: text.titleSmall?.copyWith(fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text('Define la velocidad e ícono de tus entregas.',
              style:
                  text.bodySmall?.copyWith(color: AppColors.textSecondary)),
          const SizedBox(height: 12),
          Row(
            children: [
              for (final m in MedioTransporte.values)
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: _ChipMedio(
                      medio: m,
                      activo: m == medioActual,
                      onTap: () => ref
                          .read(medioDomiciliarioProvider.notifier)
                          .seleccionar(m),
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}

class _ChipMedio extends StatelessWidget {
  final MedioTransporte medio;
  final bool activo;
  final VoidCallback onTap;
  const _ChipMedio(
      {required this.medio, required this.activo, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: activo ? AppColors.brand : AppColors.surfaceVariant,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
              color: activo ? AppColors.brand : AppColors.border),
        ),
        child: Column(
          children: [
            Icon(medio.icono,
                size: 22,
                color: activo ? Colors.white : AppColors.textSecondary),
            const SizedBox(height: 4),
            Text(medio.label,
                textAlign: TextAlign.center,
                style: TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    color: activo ? Colors.white : AppColors.textSecondary)),
          ],
        ),
      ),
    );
  }
}

/// Toggle de disponibilidad. Estado local (cosmético en Fase 1): en Fase 2
/// notificará al backend si el repartidor recibe o no nuevos pedidos.
class _DisponibilidadToggle extends StatefulWidget {
  const _DisponibilidadToggle();

  @override
  State<_DisponibilidadToggle> createState() => _DisponibilidadToggleState();
}

class _DisponibilidadToggleState extends State<_DisponibilidadToggle> {
  bool _disponible = true;

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          Icon(
            _disponible ? Icons.bolt : Icons.bedtime_outlined,
            color: _disponible ? AppColors.success : AppColors.textMuted,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(_disponible ? 'Disponible' : 'No disponible',
                    style: text.titleMedium
                        ?.copyWith(fontWeight: FontWeight.w700)),
                Text(
                  _disponible
                      ? 'Estás recibiendo pedidos'
                      : 'No recibirás nuevos pedidos',
                  style: text.bodySmall
                      ?.copyWith(color: AppColors.textSecondary),
                ),
              ],
            ),
          ),
          Switch(
            value: _disponible,
            activeThumbColor: AppColors.brand,
            onChanged: (v) => setState(() => _disponible = v),
          ),
        ],
      ),
    );
  }
}

class _SinPedido extends StatelessWidget {
  const _SinPedido();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
      ),
      child: const Column(
        children: [
          Icon(Icons.inbox_outlined, size: 48, color: AppColors.textMuted),
          SizedBox(height: 12),
          Text('Sin pedidos por ahora',
              style: TextStyle(color: AppColors.textSecondary)),
        ],
      ),
    );
  }
}

/// Skeleton con shimmer mientras carga una sección (estilo Rappi).
class _CargandoSeccion extends StatelessWidget {
  const _CargandoSeccion();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: List.generate(
        2,
        (_) => Container(
          margin: const EdgeInsets.only(bottom: 14),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.border),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: const [
              Row(
                children: [
                  ShimmerCaja(height: 22, width: 80),
                  Spacer(),
                  ShimmerCaja(height: 18, width: 70),
                ],
              ),
              SizedBox(height: 14),
              ShimmerCaja(height: 16, width: 160),
              SizedBox(height: 10),
              ShimmerCaja(height: 12, width: 220),
              SizedBox(height: 16),
              ShimmerCaja(height: 48, radio: null),
            ],
          ),
        ),
      ),
    );
  }
}

/// Pequeña píldora con un número (cantidad de pedidos disponibles).
class _Contador extends StatelessWidget {
  final int n;
  const _Contador(this.n);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: AppColors.brand,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text('$n',
          style: const TextStyle(
              color: Colors.white, fontSize: 12, fontWeight: FontWeight.w800)),
    );
  }
}

/// Tarjeta de un pedido disponible (sin asignar) con botón para aceptarlo.
class _PedidoDisponibleCard extends ConsumerStatefulWidget {
  final Pedido pedido;
  const _PedidoDisponibleCard({required this.pedido});

  @override
  ConsumerState<_PedidoDisponibleCard> createState() =>
      _PedidoDisponibleCardState();
}

class _PedidoDisponibleCardState
    extends ConsumerState<_PedidoDisponibleCard> {
  bool _aceptando = false;

  Future<void> _aceptar() async {
    HapticFeedback.lightImpact();
    setState(() => _aceptando = true);
    try {
      final medio = ref.read(medioDomiciliarioProvider);
      await ref
          .read(pedidoServiceProvider)
          .aceptarPedido(widget.pedido.id, medio: medio);
      // Pasa de "disponibles" a "mis activos". Saldrá "en camino" solo en cuanto
      // el restaurante termine de prepararlo (el cliente recibe el aviso ahí).
      ref.invalidate(pedidosDisponiblesProvider);
      ref.invalidate(pedidosActivosProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(_snackSuccess(
            '¡Pedido aceptado! Saldrás en cuanto esté listo.'));
      }
    } catch (e) {
      if (mounted) {
        setState(() => _aceptando = false);
        ScaffoldMessenger.of(context)
            .showSnackBar(_snackError('No se pudo aceptar: $e'));
      }
    }
  }

  /// Oculta el pedido localmente: deja de aparecer en la lista de disponibles
  /// para este domiciliario. El snackbar incluye "Deshacer" por si fue por
  /// accidente. En Fase 2 esta decisión la guarda el backend.
  void _rechazar() {
    HapticFeedback.mediumImpact();
    final id = widget.pedido.id;
    ref.read(pedidosRechazadosProvider.notifier).rechazar(id);
    ScaffoldMessenger.of(context).showSnackBar(
      _snackInfo(
        'Pedido rechazado',
        accion: SnackBarAction(
          label: 'Deshacer',
          textColor: AppColors.brand,
          onPressed: () =>
              ref.read(pedidosRechazadosProvider.notifier).desRechazar(id),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final pedido = widget.pedido;
    final text = Theme.of(context).textTheme;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              EstadoChip(estado: pedido.estado),
              const Spacer(),
              Text(formatoPesos(pedido.total),
                  style:
                      text.titleMedium?.copyWith(fontWeight: FontWeight.w800)),
            ],
          ),
          const SizedBox(height: 12),
          Text(pedido.restauranteNombre,
              style: text.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text('${pedido.cantidadItems} artículo(s) · Pedido #${pedido.id}',
              style: text.bodySmall?.copyWith(color: AppColors.textSecondary)),
          const SizedBox(height: 10),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(Icons.location_on_outlined,
                  size: 18, color: AppColors.textSecondary),
              const SizedBox(width: 8),
              Expanded(
                child: Text('Entregar en: ${pedido.direccionEntrega}',
                    style: text.bodyMedium),
              ),
            ],
          ),
          const SizedBox(height: 16),
          // El tema fuerza a los botones a ocupar todo el ancho disponible
          // (minimumSize de width infinity), así que ambos botones DEBEN ir
          // dentro de Expanded para tener constraints finitos.
          Row(
            children: [
              Expanded(
                flex: 4,
                child: OutlinedButton(
                  onPressed: _aceptando ? null : _rechazar,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.textSecondary,
                    side: BorderSide(color: AppColors.border, width: 1),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text(
                    'Rechazar',
                    maxLines: 1,
                    overflow: TextOverflow.visible,
                    style: TextStyle(fontWeight: FontWeight.w700),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                flex: 6,
                child: BrandButton(
                  label: _aceptando ? 'Aceptando…' : 'Aceptar pedido',
                  icono: Icons.check,
                  cargando: _aceptando,
                  onPressed: _aceptar,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

/// Tarjeta del pedido activo: estado en vivo, mapa de ruta, dirección y una
/// línea que dice qué está pasando. El pedido avanza solo (cocina → en camino →
/// entregado); cuando se entrega, pasa al historial automáticamente.
class _PedidoActivoCard extends ConsumerWidget {
  final Pedido pedido;
  const _PedidoActivoCard({required this.pedido});

  /// Qué está pasando con el pedido, según su estado.
  ({String texto, IconData icono, Color color}) _estadoInfo(
      EstadoPedido estado) {
    return switch (estado) {
      EstadoPedido.enCamino => (
          texto: 'En camino al cliente.',
          icono: Icons.delivery_dining,
          color: AppColors.success,
        ),
      EstadoPedido.entregado => (
          texto: 'Entregado.',
          icono: Icons.done_all,
          color: AppColors.success,
        ),
      EstadoPedido.enPreparacion => (
          texto: 'En preparación en el restaurante.',
          icono: Icons.restaurant,
          color: AppColors.warning,
        ),
      _ => (
          texto: 'En cocina — saldrás en cuanto el restaurante lo prepare.',
          icono: Icons.restaurant,
          color: AppColors.warning,
        ),
    };
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    // Estado en vivo: el pedido avanza solo. Si la lista trae un snapshot viejo,
    // el stream lo pone al día.
    final pedido =
        ref.watch(pedidoEnVivoProvider(this.pedido.id)).asData?.value ??
            this.pedido;
    final text = Theme.of(context).textTheme;
    final hayMapa = pedido.latEntrega != null && pedido.lngEntrega != null;
    final info = _estadoInfo(pedido.estado);

    // Cuando el pedido se entrega, sacarlo de "activos" y mandarlo al historial.
    ref.listen(pedidoEnVivoProvider(this.pedido.id), (prev, next) {
      if (next.asData?.value.estado.esFinal ?? false) {
        ref.invalidate(pedidosActivosProvider);
        ref.invalidate(historialDomiciliarioProvider);
      }
    });

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              EstadoChip(estado: pedido.estado),
              const Spacer(),
              Text(formatoPesos(pedido.total),
                  style: text.titleMedium?.copyWith(fontWeight: FontWeight.w800)),
            ],
          ),
          const SizedBox(height: 14),
          Text(pedido.restauranteNombre,
              style: text.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text('${pedido.cantidadItems} artículo(s) · Pedido #${pedido.id}',
              style: text.bodySmall?.copyWith(color: AppColors.textSecondary)),
          const SizedBox(height: 8),
          Row(
            children: [
              Icon(pedido.medioTransporte.icono,
                  size: 16, color: AppColors.brand),
              const SizedBox(width: 6),
              Text('Repartes ${pedido.medioTransporte.frase.toLowerCase()}',
                  style: text.bodySmall?.copyWith(
                      color: AppColors.textPrimary,
                      fontWeight: FontWeight.w600)),
            ],
          ),

          if (hayMapa) ...[
            const SizedBox(height: 16),
            _RutaMapa(pedido: pedido),
          ],

          const Divider(height: 28),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(Icons.location_on_outlined,
                  size: 18, color: AppColors.textSecondary),
              const SizedBox(width: 8),
              Expanded(
                child: Text('Entregar en: ${pedido.direccionEntrega}',
                    style: text.bodyMedium),
              ),
            ],
          ),

          const SizedBox(height: 16),
          Row(
            children: [
              Icon(info.icono, size: 18, color: info.color),
              const SizedBox(width: 8),
              Expanded(
                child: Text(info.texto,
                    style: TextStyle(
                        color: info.color, fontWeight: FontWeight.w600)),
              ),
            ],
          ),

          // Flujo de recogida:
          // 1) "Voy a recoger" → avisa EN VIVO al restaurante que vas en camino
          //    a recogerlo (no cambia el estado del pedido).
          if (pedido.estado != EstadoPedido.enCamino && !pedido.recogiendo) ...[
            const SizedBox(height: 16),
            _BotonRecoger(pedido.id),
          ],
          // 2) Ya yendo a recoger y la comida en preparación → "Salí a entregar"
          //    (en_preparacion → en_camino), que dispara el GPS hacia el cliente.
          if (pedido.recogiendo &&
              pedido.estado == EstadoPedido.enPreparacion) ...[
            const SizedBox(height: 16),
            _BotonSalida(pedido.id),
          ],
          // Yendo a recoger pero la cocina aún no termina: solo un aviso.
          if (pedido.recogiendo &&
              pedido.estado != EstadoPedido.enPreparacion &&
              pedido.estado != EstadoPedido.enCamino) ...[
            const SizedBox(height: 12),
            const _AvisoRecogiendo(),
          ],

          // Fase 3 — mientras este pedido va en camino, el domiciliario emite su
          // GPS real para que el cliente lo vea moverse en su mapa. Invisible.
          if (pedido.estado == EstadoPedido.enCamino) _EmisorGps(pedido.id),

          // Botón para escanear el QR del cliente y cerrar la entrega. Solo
          // cuando el pedido ya está enCamino (en camino → entregado).
          if (pedido.estado == EstadoPedido.enCamino &&
              pedido.codigoConfirmacion != null) ...[
            const SizedBox(height: 16),
            BrandButton(
              label: 'Confirmar entrega',
              icono: Icons.qr_code_scanner_rounded,
              onPressed: () async {
                HapticFeedback.lightImpact();
                final ok = await Navigator.of(context).push<bool>(
                  MaterialPageRoute(
                    builder: (_) => EscanerQrScreen(
                      pedidoId: pedido.id,
                      codigoEsperado: pedido.codigoConfirmacion!,
                    ),
                  ),
                );
                if (ok == true && context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                      _snackSuccess('¡Entrega confirmada!'));
                  ref.invalidate(pedidosActivosProvider);
                  ref.invalidate(historialDomiciliarioProvider);
                }
              },
            ),
          ],
        ],
      ),
    );
  }
}

/// Fase 3 — emisor de GPS (invisible). Mientras está montado (el pedido va en
/// camino y la pantalla del domiciliario está abierta), escucha el GPS del
/// teléfono y manda cada posición al backend, que la difunde por websocket al
/// cliente. Si falta el permiso de ubicación, el flujo simplemente no emite y
/// el cliente sigue viendo la simulación. Al desmontarse, corta el stream.
class _EmisorGps extends ConsumerStatefulWidget {
  final int pedidoId;
  const _EmisorGps(this.pedidoId);

  @override
  ConsumerState<_EmisorGps> createState() => _EmisorGpsState();
}

class _EmisorGpsState extends ConsumerState<_EmisorGps> {
  StreamSubscription<LatLng>? _sub;
  LatLng? _ultima;
  int _enviadas = 0;
  bool _bloqueado = false; // permiso negado o GPS apagado: no se puede emitir

  @override
  void initState() {
    super.initState();
    _sub = ref.read(ubicacionServiceProvider).flujoUbicacion().listen(
      (pos) {
        ref.read(pedidoServiceProvider).enviarUbicacion(widget.pedidoId, pos);
        if (!mounted) return;
        setState(() {
          _ultima = pos;
          _enviadas++;
        });
      },
      onError: (_) {}, // sin permiso/servicio: no emite (el cliente ve la simulación)
      onDone: () {
        // El flujo terminó sin emitir nunca → permiso negado o GPS apagado.
        // (Si ya emitió, fue un cierre normal del stream: no avisamos.)
        if (mounted && _ultima == null) setState(() => _bloqueado = true);
      },
    );
  }

  @override
  void dispose() {
    _sub?.cancel();
    super.dispose();
  }

  /// Reabre la app/ajustes para que el repartidor conceda la ubicación.
  Future<void> _abrirAjustes() async {
    HapticFeedback.selectionClick();
    if (!await Geolocator.isLocationServiceEnabled()) {
      await Geolocator.openLocationSettings();
    } else {
      await Geolocator.openAppSettings();
    }
  }

  @override
  Widget build(BuildContext context) {
    final activo = _ultima != null;
    final color = _bloqueado
        ? AppColors.error
        : (activo ? AppColors.success : AppColors.warning);
    final icono = _bloqueado
        ? Icons.location_off
        : (activo ? Icons.podcasts : Icons.gps_not_fixed);
    final texto = _bloqueado
        ? 'Activá la ubicación para que el cliente te vea. Tocá aquí.'
        : (activo
            ? 'Compartiendo tu ubicación en vivo · $_enviadas envíos'
            : 'Buscando tu GPS… mantené la pantalla encendida');
    return GestureDetector(
      onTap: _bloqueado ? _abrirAjustes : null,
      child: Container(
        margin: const EdgeInsets.only(top: 12),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.12),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.4)),
        ),
        child: Row(
          children: [
            Icon(icono, size: 16, color: color),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                texto,
                style: TextStyle(
                    color: color, fontWeight: FontWeight.w600, fontSize: 12.5),
              ),
            ),
            if (_bloqueado)
              Icon(Icons.chevron_right, size: 18, color: color),
          ],
        ),
      ),
    );
  }
}

/// Botón "Voy a recoger": el domiciliario marca que sale hacia el restaurante a
/// recoger el pedido. No cambia el estado; **avisa EN VIVO al restaurante** que
/// va en camino a recoger. El stream del pedido actualiza la tarjeta sola (la
/// `recogiendo` pasa a true y aparece el siguiente paso, "Salí a entregar").
class _BotonRecoger extends ConsumerStatefulWidget {
  final int pedidoId;
  const _BotonRecoger(this.pedidoId);

  @override
  ConsumerState<_BotonRecoger> createState() => _BotonRecogerState();
}

class _BotonRecogerState extends ConsumerState<_BotonRecoger> {
  bool _enviando = false;

  Future<void> _recoger() async {
    HapticFeedback.mediumImpact();
    setState(() => _enviando = true);
    try {
      await ref.read(pedidoServiceProvider).marcarVoyARecoger(widget.pedidoId);
      ref.invalidate(pedidosActivosProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(_snackSuccess(
            'El restaurante ya sabe que vas en camino a recoger.'));
      }
    } catch (e) {
      if (mounted) {
        setState(() => _enviando = false);
        ScaffoldMessenger.of(context)
            .showSnackBar(_snackError('No se pudo avisar: $e'));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return BrandButton(
      label: _enviando ? 'Avisando…' : 'Voy a recoger',
      icono: Icons.storefront_outlined,
      cargando: _enviando,
      onPressed: _recoger,
    );
  }
}

/// Aviso pequeño: el domiciliario ya va a recoger pero la cocina aún no termina.
class _AvisoRecogiendo extends StatelessWidget {
  const _AvisoRecogiendo();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.brand.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.brand.withValues(alpha: 0.4)),
      ),
      child: const Row(
        children: [
          Icon(Icons.directions_run, size: 16, color: AppColors.brand),
          SizedBox(width: 8),
          Expanded(
            child: Text(
              'Vas en camino a recoger · espera a que el restaurante termine.',
              style: TextStyle(
                  color: AppColors.brand,
                  fontWeight: FontWeight.w600,
                  fontSize: 12.5),
            ),
          ),
        ],
      ),
    );
  }
}

/// Botón "Salí a entregar": el domiciliario marca que ya recogió el pedido y
/// arranca el viaje (`en_preparacion → en_camino`). Ese cambio dispara el GPS
/// en vivo: el cliente empieza a verlo moverse. El estado lo avanza el backend
/// y el stream en vivo del pedido actualiza la tarjeta solo (montando el emisor
/// de GPS y el botón de QR).
class _BotonSalida extends ConsumerStatefulWidget {
  final int pedidoId;
  const _BotonSalida(this.pedidoId);

  @override
  ConsumerState<_BotonSalida> createState() => _BotonSalidaState();
}

class _BotonSalidaState extends ConsumerState<_BotonSalida> {
  bool _enviando = false;

  Future<void> _salir() async {
    HapticFeedback.mediumImpact();
    setState(() => _enviando = true);
    try {
      await ref
          .read(pedidoServiceProvider)
          .actualizarEstado(widget.pedidoId, EstadoPedido.enCamino.apiValue);
      ref.invalidate(pedidosActivosProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
            _snackSuccess('¡En camino! El cliente ya te ve en el mapa.'));
      }
    } catch (e) {
      if (mounted) {
        setState(() => _enviando = false);
        ScaffoldMessenger.of(context)
            .showSnackBar(_snackError('No se pudo salir: $e'));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return BrandButton(
      label: _enviando ? 'Saliendo…' : 'Salí a entregar',
      icono: Icons.navigation_rounded,
      cargando: _enviando,
      onPressed: _salir,
    );
  }
}

/// Mapa de la ruta restaurante → entrega. Necesita las coordenadas del
/// restaurante, que se piden a su detalle.
class _RutaMapa extends ConsumerWidget {
  final Pedido pedido;
  const _RutaMapa({required this.pedido});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final asyncRestaurante =
        ref.watch(restauranteDetalleProvider(pedido.restauranteId));
    final recorrido = ref.watch(rutaPedidoProvider(pedido.id)).asData?.value;
    final domiciliario =
        ref.watch(seguimientoDomiciliarioProvider(pedido.id)).asData?.value;

    return asyncRestaurante.when(
      loading: () => const SizedBox(
        height: 180,
        child: Center(child: CircularProgressIndicator()),
      ),
      error: (_, __) => const SizedBox.shrink(),
      data: (restaurante) => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          MapaPedido(
            restaurante: LatLng(restaurante.lat, restaurante.lng),
            entrega: LatLng(pedido.latEntrega!, pedido.lngEntrega!),
            ruta: recorrido?.puntos ?? const [],
            domiciliario: domiciliario,
            iconoDomiciliario: pedido.medioTransporte.icono,
            height: 180,
          ),
          if (recorrido != null && recorrido.tieneEstimado) ...[
            const SizedBox(height: 10),
            Row(
              children: [
                Icon(pedido.medioTransporte.icono,
                    size: 16, color: AppColors.textSecondary),
                const SizedBox(width: 6),
                Text(
                  '${formatoDistancia(recorrido.distanciaMetros)} · '
                  '${formatoDuracion(pedido.medioTransporte.tiempoSegundos(recorrido.distanciaMetros))} '
                  'hasta la entrega',
                  style: const TextStyle(
                      color: AppColors.textSecondary,
                      fontWeight: FontWeight.w600,
                      fontSize: 13),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}

/// Lista de pedidos ya entregados por el domiciliario.
class _HistorialDomiciliario extends ConsumerWidget {
  const _HistorialDomiciliario();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final asyncHistorial = ref.watch(historialDomiciliarioProvider);

    return asyncHistorial.when(
      loading: () => const Padding(
        padding: EdgeInsets.symmetric(vertical: 24),
        child: Center(child: CircularProgressIndicator()),
      ),
      error: (e, _) =>
          Text('Error: $e', style: const TextStyle(color: AppColors.error)),
      data: (pedidos) {
        if (pedidos.isEmpty) {
          return const Padding(
            padding: EdgeInsets.symmetric(vertical: 16),
            child: Text('Aún no tienes entregas registradas.',
                style: TextStyle(color: AppColors.textSecondary)),
          );
        }
        return Column(
          children: pedidos.map((p) => _HistorialTile(pedido: p)).toList(),
        );
      },
    );
  }
}

class _HistorialTile extends StatelessWidget {
  final Pedido pedido;
  const _HistorialTile({required this.pedido});

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(pedido.restauranteNombre,
                    style: text.bodyLarge?.copyWith(fontWeight: FontWeight.w700)),
                const SizedBox(height: 2),
                Text(pedido.direccionEntrega,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: text.bodySmall
                        ?.copyWith(color: AppColors.textSecondary)),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              EstadoChip(estado: pedido.estado),
              const SizedBox(height: 4),
              Text(formatoPesos(pedido.total),
                  style: text.bodyMedium
                      ?.copyWith(fontWeight: FontWeight.w700)),
            ],
          ),
        ],
      ),
    );
  }
}

/// SnackBar premium: tarjeta flotante con borde de color, ícono y acción
/// opcional. Mismo lenguaje que el helper del checkout.
SnackBar _snack(String texto,
    {required IconData icono,
    required Color color,
    SnackBarAction? accion}) {
  return SnackBar(
    behavior: SnackBarBehavior.floating,
    backgroundColor: AppColors.surface,
    elevation: 8,
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(12),
      side: BorderSide(color: color.withValues(alpha: 0.4)),
    ),
    content: Row(
      children: [
        Icon(icono, color: color, size: 20),
        const SizedBox(width: 10),
        Expanded(
          child: Text(texto,
              style: const TextStyle(color: AppColors.textPrimary)),
        ),
      ],
    ),
    action: accion,
  );
}

SnackBar _snackSuccess(String texto, {SnackBarAction? accion}) => _snack(texto,
    icono: Icons.check_circle_rounded,
    color: AppColors.success,
    accion: accion);
SnackBar _snackError(String texto, {SnackBarAction? accion}) => _snack(texto,
    icono: Icons.error_outline_rounded,
    color: AppColors.error,
    accion: accion);
SnackBar _snackInfo(String texto, {SnackBarAction? accion}) => _snack(texto,
    icono: Icons.info_outline_rounded,
    color: AppColors.brand,
    accion: accion);
