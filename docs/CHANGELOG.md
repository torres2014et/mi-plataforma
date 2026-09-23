# Historial de cambios

Registro de actualizaciones subidas a este repositorio. Formato: fecha, resumen del cambio, archivos/áreas afectadas.

## 2026-09-23 — Chatbot más resistente a la saturación de Gemini

- `GeminiService` ahora reintenta ante 429/5xx o timeout y, si el modelo configurado sigue fallando, prueba un modelo de respaldo (`gemini-flash-lite-latest`) antes de mostrar "no disponible". Antes una sola respuesta 503 "high demand" de Google dejaba el asistente inutilizable.
- La API key de Gemini se envía en el header `x-goog-api-key` en lugar de la URL, para que no quede escrita en `storage/logs/laravel.log` cuando hay errores.

## 2026-09-18 — Subida inicial a GitHub

- Se inicializó el repositorio Git y se subió el proyecto completo a GitHub como repositorio público: [`torres2014et/mi-plataforma`](https://github.com/torres2014et/mi-plataforma).
- Se creó el [Manual del Programador](MANUAL_PROGRAMADOR.md) — instalación, arquitectura, comandos, API móvil, base de datos y convenciones del proyecto.
- Se creó el [Manual de Usuario](MANUAL_USUARIO.md) — guía de uso por rol (cliente, restaurante, domiciliario, administrador) y preguntas frecuentes.
- Se reemplazó el `README.md` genérico de Laravel por uno propio del proyecto, con enlaces a ambos manuales.
- Se ajustó `.gitignore` para excluir del repositorio: `.env`, `CLAUDE.md`, la carpeta `.claude/`, `documentacion-pgc/` (trabajo de grado académico), los `FASE2.md`/`FASE3.md`/`FASE4.md`/`CLAUDE.md` internos de `app_movil/`, y capturas de pantalla o notas sueltas de la raíz (`Laravel`, `Reverb`, `apunta`, `poster.html`, `server.log`, `mockasts/`).
- Se verificó que ningún secreto o credencial real quedó incluido en el commit.
- Se agregó `docs/CHANGELOG.md` (este archivo) para llevar el historial de cambios versionado.

## 2026-09-22 — Chatbot IA, datos de demo más grandes y rediseño de interacciones

- **Chatbot IA (Gemini)** — asistente conversacional en web (`resources/views/partials/chatbot-widget.blade.php`) y app móvil (`ChatbotFab` en Flutter), disponible para los 4 roles. Responde sobre restaurantes/productos/pedidos usando el catálogo real de la base de datos, rechaza temas ajenos a la plataforma y falla en silencio si no hay `GEMINI_API_KEY` configurada. Mismo `ChatbotController`/`GeminiService` sirve `POST /chatbot/mensaje` (web, sesión) y `POST /api/chatbot/mensaje` (app, Sanctum). Sin persistencia del historial.
- **`RestaurantesDemoSeeder`** — 20 restaurantes nuevos (usuarios `demo-vendedorN@test.com`) con 20 productos cada uno (408 productos en total con los 2 restaurantes originales), pensado para probar el chatbot, la búsqueda y el filtro por categoría con un catálogo más grande. Se corre automáticamente con `php artisan migrate:fresh --seed`.
- **Imágenes reales** en los 22 restaurantes y sus 408 productos (Unsplash, verificadas visualmente una por una antes de usarse), con variedad dentro de cada menú para evitar que un mismo restaurante muestre la misma foto en casi todos sus platos.
- **Splash de bienvenida** en el login web (scooter acelerando + insignia de marca), una sola vez por sesión del navegador — mismo espíritu que el splash de la app Flutter.
- **Rediseño de interacciones ("gran escala")** — nuevas primitivas compartidas del design system: fondo ambiental animado detrás de toda la app autenticada, tarjetas con spotlight/tilt 3D que siguen el mouse (`.card-interactive`), scroll-reveal reutilizable, shimmer continuo en los botones principales y navegación reactiva al scroll. Aplicadas globalmente vía el layout compartido y explícitamente en el grid de restaurantes, el menú de productos y los 4 dashboards por rol.
- Actualizados `docs/MANUAL_PROGRAMADOR.md` (nueva sección del chatbot, configuración de Gemini, seeder de demo, capa de interacciones) y `docs/MANUAL_USUARIO.md` (nueva sección del asistente virtual).

## 2026-09-18 — Preparación para desplegar en Railway

- Se agregó un `Dockerfile` en la raíz, pensado para reutilizarse en 3 servicios de Railway (web, cola de trabajos, Reverb/WebSockets) cambiando solo el comando de inicio de cada uno.
- Se agregó `.dockerignore` para no incluir en la imagen archivos que no debe (`.env`, documentación interna, `node_modules`, `vendor`, capturas de pantalla, etc.).
- Se creó [`docs/DEPLOY_RAILWAY.md`](DEPLOY_RAILWAY.md) — guía completa: arquitectura de los 3 servicios, variables de entorno necesarias y pasos exactos en el dashboard de Railway, incluyendo la advertencia sobre el almacenamiento de imágenes (no persiste entre despliegues sin un Volume).

<!--
Cómo agregar una entrada nueva:

## AAAA-MM-DD — Título breve del cambio

- Qué se hizo, en una o dos líneas por punto.
-->
