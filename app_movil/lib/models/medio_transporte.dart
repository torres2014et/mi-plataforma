import 'package:flutter/material.dart';

/// Medio de transporte del domiciliario. Define su ícono, etiqueta y la
/// **velocidad típica en ciudad** (km/h), con la que se aproxima el tiempo de
/// viaje según la distancia de la ruta. Mapea a un valor del backend en Fase 2
/// (`fromApi`/`apiValue`).
enum MedioTransporte {
  moto('moto', 'Moto', Icons.two_wheeler, 28),
  bici('bici', 'Bicicleta', Icons.pedal_bike, 14),
  auto('auto', 'Carro', Icons.directions_car, 22),
  aPie('a_pie', 'A pie', Icons.directions_walk, 5);

  final String api;
  final String label;
  final IconData icono;

  /// Velocidad promedio en ciudad (km/h) para estimar el tiempo de viaje.
  final double velocidadKmh;

  const MedioTransporte(this.api, this.label, this.icono, this.velocidadKmh);

  /// Segundos reales aproximados para recorrer [metros] a esta velocidad.
  double tiempoSegundos(double metros) {
    final ms = velocidadKmh * 1000 / 3600; // m/s
    return ms <= 0 ? 0 : metros / ms;
  }

  String get apiValue => api;

  /// "En moto" / "En bicicleta" / "A pie" (para mostrar al cliente).
  String get frase =>
      this == MedioTransporte.aPie ? 'A pie' : 'En ${label.toLowerCase()}';

  static MedioTransporte fromApi(String? value) =>
      MedioTransporte.values.firstWhere(
        (m) => m.api == value,
        orElse: () => MedioTransporte.moto,
      );
}
