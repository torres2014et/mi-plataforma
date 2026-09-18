import '../core/utils/parse.dart';
import 'user_role.dart';

/// Usuario autenticado. Mapea al modelo User de Laravel.
class User {
  final int id;
  final String nombre;
  final String email;
  final UserRole rol;
  final String? telefono;
  final String? fotoUrl;

  const User({
    required this.id,
    required this.nombre,
    required this.email,
    required this.rol,
    this.telefono,
    this.fotoUrl,
  });

  /// Iniciales para mostrar en avatares cuando no hay foto.
  String get iniciales {
    final partes = nombre.trim().split(RegExp(r'\s+'));
    if (partes.isEmpty || partes.first.isEmpty) return '?';
    if (partes.length == 1) return partes.first[0].toUpperCase();
    return (partes.first[0] + partes.last[0]).toUpperCase();
  }

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: toInt(json['id']),
      // El backend usa "name"; aceptamos también "nombre" por si acaso.
      nombre: (json['name'] ?? json['nombre'] ?? '').toString(),
      email: (json['email'] ?? '').toString(),
      rol: UserRole.fromApi(json['rol']?.toString() ?? json['role']?.toString()),
      telefono: json['telefono']?.toString(),
      fotoUrl: json['foto_url']?.toString(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': nombre,
        'email': email,
        'rol': rol.apiValue,
        'telefono': telefono,
        'foto_url': fotoUrl,
      };

  User copyWith({String? nombre, String? telefono, String? fotoUrl}) {
    return User(
      id: id,
      nombre: nombre ?? this.nombre,
      email: email,
      rol: rol,
      telefono: telefono ?? this.telefono,
      fotoUrl: fotoUrl ?? this.fotoUrl,
    );
  }
}
