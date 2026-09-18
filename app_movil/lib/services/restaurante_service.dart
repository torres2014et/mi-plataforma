import '../models/producto.dart';
import '../models/restaurante.dart';

/// Contrato de acceso a restaurantes y su menú.
///
/// Implementado hoy por `FakeRestauranteService`. En la Fase 2, un
/// `ApiRestauranteService` hará las llamadas HTTP reales.
abstract interface class RestauranteService {
  /// Lista de restaurantes (sin el menú completo, como en un listado real).
  Future<List<Restaurante>> obtenerRestaurantes();

  /// Restaurante con su menú cargado.
  Future<Restaurante> obtenerRestaurante(int id);

  /// Menú (productos) de un restaurante.
  Future<List<Producto>> obtenerProductos(int restauranteId);
}
