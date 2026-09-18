import 'package:geolocator/geolocator.dart';
import 'package:latlong2/latlong.dart';

import 'ubicacion_service.dart';

/// Implementación real de [UbicacionService] con el GPS del dispositivo.
///
/// No es "fake": usa el hardware. Por eso vive aquí y no en `services/fake/`.
class GeolocatorUbicacionService implements UbicacionService {
  @override
  Future<LatLng> ubicacionActual() async {
    final activado = await Geolocator.isLocationServiceEnabled();
    if (!activado) {
      throw const UbicacionException(
          'La ubicación del teléfono está apagada. Actívala e inténtalo de nuevo.');
    }

    var permiso = await Geolocator.checkPermission();
    if (permiso == LocationPermission.denied) {
      permiso = await Geolocator.requestPermission();
    }
    if (permiso == LocationPermission.denied) {
      throw const UbicacionException(
          'Necesitamos permiso de ubicación para fijar el punto de entrega.');
    }
    if (permiso == LocationPermission.deniedForever) {
      throw const UbicacionException(
          'El permiso de ubicación está bloqueado. Actívalo en los ajustes de la app.');
    }

    final pos = await Geolocator.getCurrentPosition(
      locationSettings: const LocationSettings(accuracy: LocationAccuracy.high),
    );
    return LatLng(pos.latitude, pos.longitude);
  }

  @override
  Stream<LatLng> flujoUbicacion({int distanciaMetros = 15}) async* {
    // El domiciliario no pasa por el checkout (donde el cliente concede el
    // permiso), así que aquí SÍ hay que pedirlo: si solo lo verificábamos, su
    // GPS real nunca emitía y el cliente se quedaba en la simulación.
    if (!await Geolocator.isLocationServiceEnabled()) return;
    var permiso = await Geolocator.checkPermission();
    if (permiso == LocationPermission.denied) {
      permiso = await Geolocator.requestPermission();
    }
    final ok = permiso == LocationPermission.always ||
        permiso == LocationPermission.whileInUse;
    if (!ok) return;

    // Emitir una posición inicial de inmediato. `getPositionStream` solo emite
    // al moverse ~[distanciaMetros]; si el repartidor está quieto, el cliente se
    // quedaría en la simulación. Con este primer fijo, el GPS real toma el
    // control apenas arranca el reparto.
    try {
      final inicial = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(accuracy: LocationAccuracy.high),
      );
      yield LatLng(inicial.latitude, inicial.longitude);
    } catch (_) {
      // Si el primer fijo falla, el stream de abajo igual cubrirá el viaje.
    }

    final stream = Geolocator.getPositionStream(
      locationSettings: LocationSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: distanciaMetros,
      ),
    );
    await for (final pos in stream) {
      yield LatLng(pos.latitude, pos.longitude);
    }
  }
}
