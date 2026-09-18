import 'package:flutter/material.dart';

/// Imagen de restaurante/producto con respaldo visual.
///
/// Si [url] trae una imagen, la carga de la red ([Image.network]) y usa el
/// degradado generado como *placeholder* mientras carga o si falla (sin red,
/// 404, etc.). Si no hay [url], muestra solo el degradado: un color estable
/// derivado del texto + un ícono según la categoría.
///
/// En Fase 2 el backend manda `imagen_url` y este mismo widget la pinta; las
/// pantallas no cambian.
class ImagenPlaceholder extends StatelessWidget {
  final String texto;
  final String? categoria;
  final String? url;
  final double? height;
  final double iconSize;
  final BorderRadius? borderRadius;

  const ImagenPlaceholder({
    super.key,
    required this.texto,
    this.categoria,
    this.url,
    this.height,
    this.iconSize = 40,
    this.borderRadius,
  });

  @override
  Widget build(BuildContext context) {
    if (url == null || url!.isEmpty) return _placeholder();

    final imagen = Image.network(
      url!,
      height: height,
      width: double.infinity,
      fit: BoxFit.cover,
      // Mientras descarga, mostrar el degradado en vez de un cuadro vacío.
      loadingBuilder: (context, child, progress) =>
          progress == null ? child : _placeholder(),
      // Sin internet / URL caída: degradar elegante al placeholder.
      errorBuilder: (context, _, __) => _placeholder(),
    );

    return borderRadius == null
        ? imagen
        : ClipRRect(borderRadius: borderRadius!, child: imagen);
  }

  /// Degradado + ícono: respaldo y estado de carga.
  Widget _placeholder() {
    final base = _colorDesde(texto);
    return Container(
      height: height,
      width: double.infinity,
      decoration: BoxDecoration(
        borderRadius: borderRadius,
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [base, Color.lerp(base, Colors.black, 0.35)!],
        ),
      ),
      child: Center(
        child: Icon(
          _iconoCategoria(categoria),
          size: iconSize,
          color: Colors.white.withValues(alpha: 0.9),
        ),
      ),
    );
  }

  /// Color HSL estable derivado del hash del texto.
  Color _colorDesde(String s) {
    final hash = s.codeUnits.fold<int>(0, (acc, c) => acc + c);
    final hue = (hash * 47) % 360;
    return HSLColor.fromAHSL(1, hue.toDouble(), 0.45, 0.42).toColor();
  }

  IconData _iconoCategoria(String? cat) {
    switch (cat?.toLowerCase()) {
      case 'hamburguesas':
        return Icons.lunch_dining;
      case 'pizza':
      case 'pizzas':
        return Icons.local_pizza;
      case 'asados':
      case 'pollo':
        return Icons.outdoor_grill;
      case 'japonesa':
      case 'rollos':
        return Icons.set_meal;
      case 'típica':
      case 'platos':
        return Icons.rice_bowl;
      case 'bebidas':
        return Icons.local_drink;
      case 'postres':
        return Icons.icecream;
      default:
        return Icons.restaurant_menu;
    }
  }
}
