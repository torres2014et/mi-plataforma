/// Formatea un valor en pesos colombianos sin decimales y con separador de
/// miles con punto. Ej: 55500 -> "$55.500".
///
/// Implementación manual para no depender de intl en la Fase 1.
String formatoPesos(num valor) {
  final entero = valor.round().toString();
  final buffer = StringBuffer();
  for (int i = 0; i < entero.length; i++) {
    if (i > 0 && (entero.length - i) % 3 == 0) buffer.write('.');
    buffer.write(entero[i]);
  }
  return '\$$buffer';
}

/// Formatea una distancia en metros. Ej: 850 -> "850 m"; 1240 -> "1,2 km".
String formatoDistancia(double metros) {
  if (metros < 1000) return '${metros.round()} m';
  final km = metros / 1000;
  // Un decimal, con coma como separador (es-CO).
  return '${km.toStringAsFixed(1).replaceAll('.', ',')} km';
}

/// Formatea una duración en segundos como minutos (mínimo 1). Ej: 240 -> "4 min".
String formatoDuracion(double segundos) {
  final min = (segundos / 60).round();
  return '${min < 1 ? 1 : min} min';
}
