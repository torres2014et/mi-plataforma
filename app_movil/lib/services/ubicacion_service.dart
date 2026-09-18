import 'package:latlong2/latlong.dart';

/// Contrato para obtener la ubicación del dispositivo (punto de entrega).
///
/// Implementado por `GeolocatorUbicacionService` (GPS real). Lanza una
/// [UbicacionException] con un mensaje legible si no se puede obtener.
abstract interface class UbicacionService {
  /// Ubicación actual del teléfono. Pide permiso si hace falta.
  Future<LatLng> ubicacionActual();

  /// Flujo continuo de posiciones del dispositivo, para emitir el GPS del
  /// domiciliario mientras lleva un pedido en camino (Fase 3). Pide el permiso
  /// si hace falta, emite una posición inicial de inmediato y luego una nueva
  /// cada vez que se mueve ~[distanciaMetros]. No lanza si el permiso se niega o
  /// el GPS está apagado: simplemente termina sin emitir (quien escucha cae a la
  /// simulación).
  Stream<LatLng> flujoUbicacion({int distanciaMetros});
}

/// Error de ubicación con un mensaje listo para mostrarle al usuario.
class UbicacionException implements Exception {
  final String mensaje;
  const UbicacionException(this.mensaje);

  @override
  String toString() => mensaje;
}
