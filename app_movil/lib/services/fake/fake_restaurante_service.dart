import '../../models/producto.dart';
import '../../models/restaurante.dart';
import '../restaurante_service.dart';
import 'fake_data.dart';

/// Versión fake: devuelve los restaurantes quemados de [FakeData], con un
/// retraso simulado para poder ver los spinners de carga.
class FakeRestauranteService implements RestauranteService {
  @override
  Future<List<Restaurante>> obtenerRestaurantes() async {
    await Future.delayed(const Duration(milliseconds: 1200));
    // En el listado no mandamos el menú completo (como haría una API real).
    return FakeData.restaurantes
        .map((r) => r.copyWith(productos: const []))
        .toList();
  }

  @override
  Future<Restaurante> obtenerRestaurante(int id) async {
    await Future.delayed(const Duration(milliseconds: 700));
    return FakeData.restaurantes.firstWhere(
      (r) => r.id == id,
      orElse: () => throw Exception('Restaurante $id no encontrado'),
    );
  }

  @override
  Future<List<Producto>> obtenerProductos(int restauranteId) async {
    await Future.delayed(const Duration(milliseconds: 700));
    final restaurante = FakeData.restaurantes.firstWhere(
      (r) => r.id == restauranteId,
      orElse: () => throw Exception('Restaurante $restauranteId no encontrado'),
    );
    return restaurante.productos;
  }
}
