import 'package:latlong2/latlong.dart';

/// Recorrido restaurante → entrega: los puntos para dibujar la ruta más la
/// distancia y duración estimadas (las devuelve OSRM en Fase 1; en Fase 2
/// pueden venir del backend o de la ruta real del repartidor).
class Recorrido {
  final List<LatLng> puntos;
  final double distanciaMetros;
  final double duracionSegundos;

  const Recorrido({
    required this.puntos,
    required this.distanciaMetros,
    required this.duracionSegundos,
  });

  /// Recorrido vacío (p. ej. un pedido sin punto de entrega).
  static const Recorrido vacio =
      Recorrido(puntos: [], distanciaMetros: 0, duracionSegundos: 0);

  bool get tieneRuta => puntos.length >= 2;
  bool get tieneEstimado => distanciaMetros > 0;
}
