import '../../models/producto.dart';
import '../../models/restaurante.dart';
import '../restaurante_service.dart';
import 'api_client.dart';

/// Implementación real de [RestauranteService] contra el backend Laravel.
///
/// Las API Resources de Laravel envuelven las respuestas en `{ "data": ... }`;
/// aquí se desempaqueta ese `data` antes de mapear a los modelos.
class ApiRestauranteService implements RestauranteService {
  final ApiClient _api;

  ApiRestauranteService(this._api);

  @override
  Future<List<Restaurante>> obtenerRestaurantes() async {
    try {
      final res = await _api.dio.get('/restaurantes');
      final lista = (res.data['data'] as List)
          .map((e) => Restaurante.fromJson(e as Map<String, dynamic>))
          .toList();
      return lista;
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<Restaurante> obtenerRestaurante(int id) async {
    try {
      final res = await _api.dio.get('/restaurantes/$id');
      return Restaurante.fromJson(res.data['data'] as Map<String, dynamic>);
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }

  @override
  Future<List<Producto>> obtenerProductos(int restauranteId) async {
    try {
      final res = await _api.dio.get('/restaurantes/$restauranteId/productos');
      return (res.data['data'] as List)
          .map((e) => Producto.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (e) {
      throw ApiClient.comoError(e);
    }
  }
}
