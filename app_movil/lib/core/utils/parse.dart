/// Helpers de parseo tolerantes para los `fromJson`.
///
/// Laravel/MySQL pueden devolver decimales como String ("12000.00") o enteros
/// como num. Estas funciones evitan que la app explote por un cast en la
/// Fase 2 (conexión real); con los datos fake nunca fallan.
library;

double toDouble(dynamic value) {
  if (value == null) return 0;
  if (value is num) return value.toDouble();
  return double.tryParse(value.toString()) ?? 0;
}

int toInt(dynamic value) {
  if (value == null) return 0;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString()) ?? 0;
}

bool toBool(dynamic value) {
  if (value is bool) return value;
  if (value is num) return value != 0;
  final s = value?.toString().toLowerCase();
  return s == 'true' || s == '1';
}

DateTime? toDateOrNull(dynamic value) {
  if (value == null) return null;
  return DateTime.tryParse(value.toString());
}
