import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/restaurante.dart';
import 'services_providers.dart';

/// Lista de restaurantes (asíncrona). La UI la consume como AsyncValue, así
/// obtiene gratis los estados de carga/error/datos para el spinner.
final restaurantesProvider = FutureProvider<List<Restaurante>>((ref) async {
  return ref.watch(restauranteServiceProvider).obtenerRestaurantes();
});

/// Texto de búsqueda escrito por el cliente.
///
/// En Riverpod 3 se usa un Notifier en lugar del antiguo StateProvider.
class BusquedaRestauranteNotifier extends Notifier<String> {
  @override
  String build() => '';

  void actualizar(String valor) => state = valor;
}

final busquedaRestauranteProvider =
    NotifierProvider<BusquedaRestauranteNotifier, String>(
  BusquedaRestauranteNotifier.new,
);

/// Categoría seleccionada en los chips de filtro (`''` = todas).
class CategoriaRestauranteNotifier extends Notifier<String> {
  @override
  String build() => '';

  void seleccionar(String categoria) =>
      state = state == categoria ? '' : categoria;
}

final categoriaRestauranteProvider =
    NotifierProvider<CategoriaRestauranteNotifier, String>(
  CategoriaRestauranteNotifier.new,
);

/// Lista de categorías disponibles (derivadas de los restaurantes), para los
/// chips de filtro.
final categoriasRestauranteProvider = Provider<List<String>>((ref) {
  final lista = ref.watch(restaurantesProvider).asData?.value ?? const [];
  final set = <String>{for (final r in lista) r.categoria};
  return set.toList()..sort();
});

/// Restaurantes filtrados por la búsqueda (nombre/categoría) y por la categoría
/// seleccionada en los chips.
///
/// Devuelve un AsyncValue para preservar los estados de carga/error de
/// [restaurantesProvider].
final restaurantesFiltradosProvider =
    Provider<AsyncValue<List<Restaurante>>>((ref) {
  final asyncRestaurantes = ref.watch(restaurantesProvider);
  final query = ref.watch(busquedaRestauranteProvider).trim().toLowerCase();
  final categoria = ref.watch(categoriaRestauranteProvider);

  return asyncRestaurantes.whenData((restaurantes) {
    return restaurantes.where((r) {
      final coincideBusqueda = query.isEmpty ||
          r.nombre.toLowerCase().contains(query) ||
          r.categoria.toLowerCase().contains(query);
      final coincideCategoria = categoria.isEmpty || r.categoria == categoria;
      return coincideBusqueda && coincideCategoria;
    }).toList();
  });
});

/// Detalle de un restaurante (con su menú), por id.
final restauranteDetalleProvider =
    FutureProvider.family<Restaurante, int>((ref, id) async {
  return ref.watch(restauranteServiceProvider).obtenerRestaurante(id);
});
