/// Roles del sistema. Coinciden con los roles de Spatie en el backend Laravel.
///
/// En la app solo se usan [cliente] y [domiciliario] (los "roles de calle").
/// [restaurante] y [admin] existen para mapear bien la respuesta del backend,
/// pero no tienen pantallas aquí.
enum UserRole {
  cliente,
  restaurante,
  domiciliario,
  admin;

  /// Convierte el string del backend (p. ej. "cliente") al enum.
  static UserRole fromApi(String? value) {
    return UserRole.values.firstWhere(
      (r) => r.name == value,
      orElse: () => UserRole.cliente,
    );
  }

  /// Valor que espera/entrega el backend.
  String get apiValue => name;

  /// Etiqueta legible para mostrar en la UI.
  String get label => switch (this) {
        UserRole.cliente => 'Cliente',
        UserRole.restaurante => 'Restaurante',
        UserRole.domiciliario => 'Domiciliario',
        UserRole.admin => 'Administrador',
      };
}
