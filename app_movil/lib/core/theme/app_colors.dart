import 'package:flutter/material.dart';

/// Escala de marca (naranja), idéntica al `tailwind.config.js` de la web.
/// La principal es [c500]; el gradiente de los botones va de [c500] a [c600].
class Brand {
  Brand._();

  static const Color c50 = Color(0xFFFFF4EE);
  static const Color c100 = Color(0xFFFFE8D6);
  static const Color c200 = Color(0xFFFFD0B0);
  static const Color c300 = Color(0xFFFFA97A);
  static const Color c400 = Color(0xFFFD7A42);
  static const Color c500 = Color(0xFFF25C2E); // principal
  static const Color c600 = Color(0xFFE33F14);
  static const Color c700 = Color(0xFFBC2F0F);
  static const Color c800 = Color(0xFF952913);
  static const Color c900 = Color(0xFF782514);
  static const Color c950 = Color(0xFF410F07);
}

/// Paleta central de la app, igualada a la web (tema oscuro premium estilo
/// Rappi/iFood). Tener los colores en un solo lugar evita "colores mágicos"
/// repartidos por las pantallas. **Tomar los colores de aquí, nunca hardcodear.**
class AppColors {
  AppColors._();

  // ---- Marca ----
  /// Naranja de marca (Tailwind brand-500).
  static const Color brand = Brand.c500;

  /// Fin del gradiente / glow de los botones (brand-600).
  static const Color brandDark = Brand.c600;

  /// Tono claro para anillo de foco y color secundario (brand-400).
  static const Color brandLight = Brand.c400;

  // ---- Fondos (escala zinc, tema oscuro) ----
  /// Fondo casi negro de la app (zinc-950+).
  static const Color background = Color(0xFF0D0D0F);

  /// Superficie / cards (zinc-900).
  static const Color surface = Color(0xFF18181B);

  /// Cards (= [surface], se mantiene el nombre por compatibilidad).
  static const Color card = Color(0xFF18181B);

  /// Superficie elevada / chips / disabled (zinc-800).
  static const Color surfaceVariant = Color(0xFF27272A);

  /// Relleno de los campos de formulario (zinc-800).
  static const Color inputFill = Color(0xFF27272A);

  // ---- Texto ----
  /// Texto principal (zinc-100).
  static const Color textPrimary = Color(0xFFF4F4F5);

  /// Alias de [textPrimary] (el spec de la web lo llama "textMain").
  static const Color textMain = Color(0xFFF4F4F5);

  /// Texto secundario (zinc-400).
  static const Color textSecondary = Color(0xFFA1A1AA);

  /// Texto muy atenuado / placeholder (zinc-600).
  static const Color textMuted = Color(0xFF52525B);

  // ---- Bordes / divisores ----
  /// Borde tenue translúcido (blanco al 8%), como en la web: se adapta a la
  /// superficie sobre la que se dibuja. No es `const` a propósito.
  static final Color border = Colors.white.withValues(alpha: 0.08);

  // ---- Semánticos / estados de pedido (tonos -400, legibles sobre oscuro) ----
  /// Verde → activo / entregado.
  static const Color success = Color(0xFF34D399);

  /// Naranja → pendiente / preparando.
  static const Color warning = Brand.c400;

  /// Rojo → cancelado.
  static const Color error = Color(0xFFF87171);

  /// Azul → confirmado / en camino.
  static const Color info = Color(0xFF60A5FA);

  /// Color de las estrellas de calificación.
  static const Color star = Color(0xFFFFC107);
}
