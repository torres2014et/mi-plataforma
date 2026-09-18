# Mi Plataforma — Ubaté Eats

Plataforma de domicilios de comidas rápidas para **Ubaté, Cundinamarca**. Reemplaza a Rappi/iFood para los negocios locales: sin altas comisiones, con control directo de catálogo, pedidos y logística.

**Stack:** Laravel 12 · MySQL · Tailwind CSS 3 · Alpine.js 3 · Vite 7 · Laravel Reverb (WebSockets) · Leaflet.js (mapas).

Dos frontends comparten este mismo backend:
- **Web** (este repositorio) — cubre los 4 roles: cliente, restaurante, domiciliario y admin.
- **App móvil Flutter** (`app_movil/`) — cubre cliente y domiciliario, conectada a la misma API vía Sanctum.

## Documentación

- 📘 [Manual del Programador](docs/MANUAL_PROGRAMADOR.md) — instalación, arquitectura, comandos y convenciones del proyecto.
- 📗 [Manual de Usuario](docs/MANUAL_USUARIO.md) — cómo usar la plataforma según cada rol (cliente, restaurante, domiciliario, admin).
- 📝 [Historial de cambios](docs/CHANGELOG.md) — registro de actualizaciones subidas al repositorio.

## Inicio rápido

```bash
composer run setup
php artisan migrate:fresh --seed
php artisan storage:link
composer run dev
```

Ver el [Manual del Programador](docs/MANUAL_PROGRAMADOR.md) para el detalle completo de instalación, arquitectura y comandos disponibles.

## Licencia

Proyecto privado de uso académico/comercial. Todos los derechos reservados.
