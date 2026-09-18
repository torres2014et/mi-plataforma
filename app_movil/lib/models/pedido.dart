import '../core/utils/parse.dart';
import 'estado_pedido.dart';
import 'medio_transporte.dart';
import 'pedido_item.dart';

/// Pedido. Mapea al modelo Pedido de Laravel.
class Pedido {
  final int id;
  final int restauranteId;
  final String restauranteNombre;

  /// Imagen del restaurante (para mostrarla en la lista de pedidos del cliente).
  final String? restauranteImagenUrl;
  final int clienteId;
  final int? domiciliarioId;

  /// Medio en que el domiciliario hace la entrega (define ícono y velocidad
  /// para el estimado y la simulación del movimiento).
  final MedioTransporte medioTransporte;

  /// `true` si el domiciliario ya pulsó "Voy a recoger" (va en camino al
  /// restaurante). El restaurante ya fue avisado en vivo. No cambia el estado.
  final bool recogiendo;
  final EstadoPedido estado;
  final List<PedidoItem> items;
  final double subtotal;
  final double costoDomicilio;
  final double total;
  final String direccionEntrega;

  /// Coordenadas del punto de entrega (para el mapa del domiciliario).
  final double? latEntrega;
  final double? lngEntrega;
  final DateTime? createdAt;

  /// `true` si el cliente ya calificó este pedido.
  final bool calificado;

  /// Código secreto que el cliente le muestra (como QR + texto) al domiciliario
  /// para confirmar la entrega. El domiciliario lo escanea o lo escribe; si
  /// coincide, el pedido pasa a `entregado`. En Fase 2 lo genera el backend.
  final String? codigoConfirmacion;

  const Pedido({
    required this.id,
    required this.restauranteId,
    required this.restauranteNombre,
    this.restauranteImagenUrl,
    required this.clienteId,
    required this.estado,
    required this.items,
    required this.subtotal,
    required this.costoDomicilio,
    required this.total,
    required this.direccionEntrega,
    this.domiciliarioId,
    this.medioTransporte = MedioTransporte.moto,
    this.recogiendo = false,
    this.latEntrega,
    this.lngEntrega,
    this.createdAt,
    this.calificado = false,
    this.codigoConfirmacion,
  });

  int get cantidadItems =>
      items.fold(0, (acc, item) => acc + item.cantidad);

  factory Pedido.fromJson(Map<String, dynamic> json) {
    return Pedido(
      id: toInt(json['id']),
      restauranteId: toInt(json['restaurante_id']),
      restauranteNombre:
          (json['restaurante_nombre'] ?? json['restaurante']?['nombre'] ?? '')
              .toString(),
      restauranteImagenUrl: (json['restaurante_imagen_url'] ??
              json['restaurante']?['imagen_url'])
          ?.toString(),
      clienteId: toInt(json['cliente_id'] ?? json['user_id']),
      domiciliarioId: json['domiciliario_id'] == null
          ? null
          : toInt(json['domiciliario_id']),
      medioTransporte:
          MedioTransporte.fromApi(json['medio_transporte']?.toString()),
      recogiendo: toBool(json['recogiendo']),
      estado: EstadoPedido.fromApi(json['estado']?.toString()),
      items: (json['items'] as List<dynamic>? ?? [])
          .map((e) => PedidoItem.fromJson(e as Map<String, dynamic>))
          .toList(),
      subtotal: toDouble(json['subtotal']),
      costoDomicilio: toDouble(json['costo_domicilio']),
      total: toDouble(json['total']),
      direccionEntrega: (json['direccion_entrega'] ?? '').toString(),
      latEntrega:
          json['lat_entrega'] == null ? null : toDouble(json['lat_entrega']),
      lngEntrega:
          json['lng_entrega'] == null ? null : toDouble(json['lng_entrega']),
      createdAt: toDateOrNull(json['created_at']),
      calificado: toBool(json['calificado']),
      codigoConfirmacion: json['codigo_confirmacion']?.toString(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'restaurante_id': restauranteId,
        'restaurante_nombre': restauranteNombre,
        'restaurante_imagen_url': restauranteImagenUrl,
        'cliente_id': clienteId,
        'domiciliario_id': domiciliarioId,
        'medio_transporte': medioTransporte.apiValue,
        'recogiendo': recogiendo,
        'estado': estado.apiValue,
        'items': items.map((i) => i.toJson()).toList(),
        'subtotal': subtotal,
        'costo_domicilio': costoDomicilio,
        'total': total,
        'direccion_entrega': direccionEntrega,
        'lat_entrega': latEntrega,
        'lng_entrega': lngEntrega,
        'created_at': createdAt?.toIso8601String(),
        'calificado': calificado,
        'codigo_confirmacion': codigoConfirmacion,
      };

  Pedido copyWith({
    EstadoPedido? estado,
    int? domiciliarioId,
    MedioTransporte? medioTransporte,
    bool? recogiendo,
    bool? calificado,
  }) {
    return Pedido(
      id: id,
      restauranteId: restauranteId,
      restauranteNombre: restauranteNombre,
      restauranteImagenUrl: restauranteImagenUrl,
      clienteId: clienteId,
      domiciliarioId: domiciliarioId ?? this.domiciliarioId,
      medioTransporte: medioTransporte ?? this.medioTransporte,
      recogiendo: recogiendo ?? this.recogiendo,
      estado: estado ?? this.estado,
      items: items,
      subtotal: subtotal,
      costoDomicilio: costoDomicilio,
      total: total,
      direccionEntrega: direccionEntrega,
      latEntrega: latEntrega,
      lngEntrega: lngEntrega,
      createdAt: createdAt,
      calificado: calificado ?? this.calificado,
      codigoConfirmacion: codigoConfirmacion,
    );
  }
}
