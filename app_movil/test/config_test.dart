import 'package:flutter_test/flutter_test.dart';

import 'package:domicilios_ubate/core/config.dart';

void main() {
  group('ApiConfig.normalizarUrl', () {
    test('agrega esquema http:// y sufijo /api', () {
      expect(
        ApiConfig.normalizarUrl('192.168.1.10:8000'),
        'http://192.168.1.10:8000/api',
      );
    });

    test('respeta https y agrega /api', () {
      expect(
        ApiConfig.normalizarUrl('https://algo.ngrok-free.app'),
        'https://algo.ngrok-free.app/api',
      );
    });

    test('quita barras finales antes de agregar /api', () {
      expect(
        ApiConfig.normalizarUrl('https://algo.ngrok-free.app/'),
        'https://algo.ngrok-free.app/api',
      );
    });

    test('no duplica /api si ya viene', () {
      expect(
        ApiConfig.normalizarUrl('https://algo.ngrok-free.app/api'),
        'https://algo.ngrok-free.app/api',
      );
    });

    test('no duplica /api aunque venga en mayúsculas y con barra', () {
      expect(
        ApiConfig.normalizarUrl('https://algo.ngrok-free.app/API/'),
        'https://algo.ngrok-free.app/API',
      );
    });

    test('entrada vacía cae al valor por defecto', () {
      expect(ApiConfig.normalizarUrl('   '), ApiConfig.baseUrl);
    });

    test('recorta espacios', () {
      expect(
        ApiConfig.normalizarUrl('  http://10.0.2.2:8000  '),
        'http://10.0.2.2:8000/api',
      );
    });
  });
}
