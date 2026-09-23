# Manual del Programador — Mi Plataforma (Ubaté Eats)

Guía técnica para levantar, entender y extender la plataforma de domicilios de comida rápida para Ubaté, Cundinamarca.

> Para el detalle exhaustivo de arquitectura y decisiones de diseño, la fuente de verdad sigue siendo `CLAUDE.md` en la raíz del repo (no se sube a GitHub por acuerdo del equipo, pero vive en el entorno de desarrollo). Este manual resume lo necesario para que cualquier programador nuevo pueda trabajar sin depender de ese archivo.

## 1. Qué es el proyecto

Alternativa local a las apps de domicilios nacionales para restaurantes locales de Ubaté: sin altas comisiones, con control directo de catálogo, pedidos y logística.

**Roles del sistema:**
- **cliente** — navega restaurantes, hace pedidos, sigue la entrega en un mapa en tiempo real.
- **restaurante** — gestiona perfil, catálogo, horarios, pedidos entrantes y asignación de domiciliarios.
- **domiciliario** — ve y ejecuta entregas asignadas, activa/desactiva disponibilidad, usa mapa GPS.
- **admin** — administra usuarios, restaurantes, pedidos y estadísticas globales.

**Dos frontends, un solo backend:**
1. **Web** (este repositorio) — Laravel 12 + Blade, cubre los 4 roles.
2. **App móvil Flutter** (`app_movil/`) — cubre cliente y domiciliario, consume la misma API vía Sanctum. Tiene su propia documentación dentro de esa carpeta.

## 2. Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 (PHP) |
| Base de datos | MySQL |
| Frontend | Blade + Tailwind CSS 3 + Alpine.js 3 |
| Build | Vite 7 |
| Tiempo real | Laravel Reverb (WebSockets) |
| Mapas | Leaflet.js + OSRM + Nominatim (sin API key) |
| Auth API | Laravel Sanctum (tokens Bearer) |
| Push notifications | Firebase Cloud Messaging (HTTP v1, sin SDK) |
| Roles/permisos | Spatie Laravel Permission 6 |

## 3. Requisitos previos

- PHP 8.2+ con extensiones habituales de Laravel
- Composer
- Node.js + npm
- MySQL (o compatible) corriendo localmente
- (Opcional) ngrok, si se necesita acceso desde otro dispositivo/red

## 4. Instalación desde cero

```bash
# 1. Clonar el repositorio
git clone <url-del-repo>
cd mi-plataforma

# 2. Instalación completa (composer install, key:generate, npm install, etc.)
composer run setup

# 3. Configurar base de datos en .env
#    DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4. Migrar y poblar con datos de prueba
php artisan migrate:fresh --seed

# 5. Symlink de almacenamiento (imágenes de productos/restaurantes)
php artisan storage:link
```

### Usuarios de prueba (creados por el seeder)

| Email | Contraseña | Rol |
|---|---|---|
| `cliente@test.com` | `password` | cliente |
| `vendedor@test.com` | `password` | restaurante |
| `domiciliario@test.com` | `password` | domiciliario |
| `admin@test.com` | `password` | admin |

> **Nunca crear usuarios sin rol asignado** — causa error 403 o bucle de redirección.

`migrate:fresh --seed` también corre `RestaurantesDemoSeeder`: agrega **20 restaurantes** (usuarios `demo-vendedorN@test.com` / `password`) con **20 productos cada uno** (imágenes reales de Unsplash incluidas), pensado para probar búsqueda, filtros y el chatbot con un catálogo grande. Se puede correr solo con `php artisan db:seed --class=RestaurantesDemoSeeder`; es idempotente (no duplica si se corre de nuevo).

## 5. Comandos de desarrollo

```bash
# Levanta servidor PHP + queue worker + Vite + Reverb en una sola terminal
composer run dev

# Build de producción de assets
npm run build

# Tests
php artisan test
php artisan test --filter NombreDelTest

# Formatear código PHP
./vendor/bin/pint

# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Resetear caché de permisos (si aparecen 403 inesperados)
php artisan permission:cache-reset

# Limpiar caché de configuración (tras editar .env)
php artisan config:clear
```

`composer run dev` levanta 4 procesos en paralelo con `concurrently`: `server` (PHP), `queue` (procesamiento de emails), `vite` (assets) y `reverb` (WebSockets). Una sola terminal es suficiente para desarrollar.

## 6. Arquitectura

### 6.1 Roles y middleware

Roles gestionados con Spatie Laravel Permission: `cliente`, `restaurante`, `domiciliario`, `admin`.

Alias registrados en `bootstrap/app.php` (Laravel 12 no usa `Kernel.php`):

```php
$middleware->alias([
    'role'               => RoleMiddleware::class,
    'permission'         => PermissionMiddleware::class,
    'role_or_permission' => RoleOrPermissionMiddleware::class,
    'role.redirect'      => \App\Http\Middleware\RoleRedirect::class,
]);
```

`RoleRedirect` (`app/Http/Middleware/RoleRedirect.php`):
1. Redirige `/dashboard` al dashboard correcto según el rol.
2. Redirige silenciosamente si un usuario intenta entrar a la sección de otro rol.

**No aplicar `role.redirect` a rutas de recursos** — solo al grupo raíz autenticado.

Cuando Spatie lanza `UnauthorizedException` (rol incorrecto), `bootstrap/app.php → withExceptions` redirige al dashboard del rol actual en vez de mostrar un 403 genérico.

### 6.2 Rutas principales

```
/                                       → welcome (público)
/dashboard                              → redirige por rol (RoleRedirect)
/cliente/*                              → role:cliente
/cliente/pedidos/{pedido}/calificar     → POST calificación (solo entregados, una vez)
/restaurante/*                          → role:restaurante
/domiciliario/*                         → role:domiciliario
/admin/*                                → role:admin
```

### 6.3 Controladores por rol

| Namespace | Controlador | Responsabilidad |
|---|---|---|
| `Restaurante\` | `ProductoController` | CRUD productos + imagen |
| `Restaurante\` | `ConfiguracionController` | Perfil, horarios (JSON), costo de domicilio |
| `Restaurante\` | `PedidoController` | Gestión de pedidos + asignación domiciliario + emails |
| `Cliente\` | `RestauranteController` | Listado y detalle de restaurantes |
| `Cliente\` | `CarritoController` | Carrito en sesión + checkout |
| `Cliente\` | `PedidoController` | Mis pedidos (index + show con mapa) |
| `Cliente\` | `CalificacionController` | Crear calificación de pedido entregado |
| `Domiciliario\` | `DashboardController` | Dashboard + disponibilidad + mapa GPS |
| `Admin\` | `UsuarioController` | Listado usuarios + toggle restaurantes |

### 6.4 Modelos clave

- **User** — `HasRoles` (Spatie), `Notifiable`. Campos extra: `telefono`, `direccion`, `cedula`, `tipo_vehiculo`, `placa_vehiculo`, `nombre_negocio`, `disponible` (domiciliario).
- **Restaurante** — `user_id` FK, `horarios` (JSON), `costo_domicilio`. Métodos `getPromedioEstrellas()`, `getTotalCalificaciones()`.
- **Producto** — `restaurante_id` FK, `categoria`, `precio`, `disponible`.
- **Pedido** — modelo central. Estados: `pendiente → confirmado → en_preparacion → en_camino → entregado | cancelado`. Métodos `estadoLabel()`, `estadoBadgeClass()`, `estaActivo()`, `transicionesValidas()`.
- **PedidoItem** — snapshot inmutable de nombre/precio al momento del pedido.
- **Calificacion** — una por pedido entregado (`pedido_id` único).
- **DeviceToken** — token FCM por dispositivo (`android`/`ios`/`web`), reemplaza a la vieja columna `users.fcm_token`.

## 7. Flujo de pedido completo

```
Cliente agrega al carrito (sesión)
    ↓
Checkout → DB::transaction() crea Pedido + PedidoItems (con costo_domicilio snapshot)
    ↓
Notificación NuevoPedido → restaurante + broadcast(NuevoPedidoRecibido) en tiempo real
    ↓
Restaurante confirma → email "✅ Pedido confirmado" + broadcast(PedidoActualizado)
    ↓
Restaurante → en_preparacion → asigna domiciliario → en_camino
    ↓ email "🛵 En camino" + broadcast + notificación PedidoAsignado al domiciliario
    ↓
Domiciliario entrega → estado: entregado → email "🎉 Entregado"
    ↓
Cliente puede calificar (1–5 estrellas + comentario opcional)
```

Emails vía `Mail::queue(PedidoEstadoActualizado)` — **requiere `QUEUE_CONNECTION=database` y el queue worker corriendo** (incluido en `composer run dev`).

## 8. Tiempo real — Laravel Reverb

**Canales privados:**

| Canal | Autorizado para | Eventos |
|---|---|---|
| `pedido.{id}` | cliente + restaurante + domiciliario del pedido | `estado.actualizado`, `ubicacion` |
| `restaurante.{id}` | dueño del restaurante | `pedido.nuevo`, `domiciliario.acepto` |

**Eventos:** `PedidoActualizado`, `NuevoPedidoRecibido`, `DomiciliarioAcepto`, `DomiciliarioUbicacion` (los dos últimos con `ShouldBroadcastNow`, sin pasar por cola).

Autorización de canales: `routes/channels.php`. Frontend Echo: `resources/js/bootstrap.js`. La app móvil se autentica en `POST /api/broadcasting/auth` con token Sanctum en vez de sesión.

## 9. API para la app móvil (Sanctum)

Toda expuesta en `routes/api.php`, con token Bearer (Sanctum). Endpoints principales:

```
GET    /api/ping                          → descubrimiento de red (sin auth)
POST   /api/login | /api/register         → { token, user }
GET    /api/me · POST /api/logout
POST   /api/broadcasting/auth             → auth de canales privados
POST/DELETE /api/me/fcm-token             → registrar/borrar token push

GET    /api/restaurantes · /api/restaurantes/{id} · /api/restaurantes/{id}/productos

GET/POST /api/pedidos · GET /api/pedidos/{id}
POST   /api/pedidos/{id}/calificar

GET    /api/domiciliario/pedidos/activos | disponibles | historial
POST   /api/pedidos/{id}/aceptar
POST   /api/pedidos/{id}/recoger
PATCH  /api/pedidos/{id}/estado
POST   /api/pedidos/{id}/confirmar-entrega   → valida codigo_confirmacion si se envía
POST   /api/pedidos/{id}/ubicacion           → GPS real, retransmitido por socket

POST   /api/chatbot/mensaje                  → asistente IA (mismo endpoint que la web, ver sección 11)
```

Controladores: `Api\AuthController`, `Api\PedidoController`, `Api\RestauranteController`, `ChatbotController` (compartido con la web).

**Confirmación de entrega por código (tipo QR):** cada pedido genera un `codigo_confirmacion` de 6 caracteres al crearse (alfabeto sin `0/O/1/I/L`).

## 10. Notificaciones y push

- **Database (badge en nav):** `NuevoPedido` → restaurante; `PedidoAsignado` → domiciliario.
- **Email (Mailtrap en dev):** `PedidoEstadoActualizado` → cliente, en cada cambio de estado.
- **Push (Firebase FCM, `App\Services\FcmSender`):** construye el JWT y pide el access token OAuth2 a mano con `openssl` (sin SDK). Envía a todos los `DeviceToken` del usuario (app + web). Avisa cuando el pedido sale (`en_camino`) y cuando el domiciliario está a ≤250 m del destino. **Falla en silencio** si no existe `storage/app/firebase/service-account.json` — el resto del sistema sigue funcionando igual.

## 11. Chatbot IA (Gemini)

Widget flotante disponible en las 4 vistas autenticadas de la web y en la app móvil (cliente y domiciliario).

- **Backend:** `POST /chatbot/mensaje` (web, sesión) y `POST /api/chatbot/mensaje` (app, Sanctum) apuntan al mismo `ChatbotController` → `App\Services\GeminiService`. Construye el system prompt (`storage/app/chatbot/system-prompt.md`) + un contexto JSON con restaurantes/productos reales de la BD, y llama a la API de Gemini.
- **Falla suave:** sin `GEMINI_API_KEY` en `.env`, o si la llamada falla, responde "El asistente no está disponible en este momento" — el resto de la app sigue funcionando igual (mismo patrón que `FcmSender`).
- **Modelo:** usar `GEMINI_MODEL=gemini-flash-latest` (alias estable de Google). Modelos preview fijos como `gemini-3.6-flash` han devuelto 503 "high demand" en pruebas.
- **Sin persistencia:** el historial de la conversación vive solo en memoria del navegador/app; no se guarda en base de datos.
- **Web:** `resources/views/partials/chatbot-widget.blade.php` + `resources/js/chatbot.js` (Alpine.js).
- **App móvil:** `ChatbotFab` en `app_movil/lib/features/shared/widgets/chatbot_flotante.dart`, agregado en los shells de cliente y domiciliario.

## 12. Base de datos

Tablas principales: `users`, `restaurantes`, `productos`, `pedidos`, `pedido_items`, `calificaciones`, `device_tokens`, `personal_access_tokens` (Sanctum), `notifications`.

```bash
php artisan migrate           # aplicar migraciones nuevas
php artisan migrate:fresh --seed   # reiniciar BD completa con datos de prueba
```

## 13. Convenciones de vistas

Todas las vistas autenticadas usan `<x-app-layout>`. **Nunca** `@extends`/`@yield`:

```blade
<x-app-layout>
    <x-slot name="header">Título</x-slot>
    ...contenido...
</x-app-layout>
```

Scripts por vista con `@push('scripts')` / `@stack('scripts')`. `[x-cloak]` está definido globalmente para ocultar elementos Alpine antes de inicializar.

### Design system

- Fondo `#0D0D0F`, nav `gray-950`, acento naranja `brand-500 #F25C2E` (escala 50–950 en `tailwind.config.js`).
- Fuente: Plus Jakarta Sans.
- Clases utilitarias en `resources/css/app.css`: `.btn-primary/.btn-secondary/.btn-danger/.btn-ghost`, `.card/.card-interactive/.stat-card`, `.badge-green/.badge-gray/.badge-orange/.badge-blue/.badge-red`, `.input/.form-section`.
- Capa de interacciones compartida (fondo ambiental, `.card-interactive` con spotlight/tilt al mouse, scroll-reveal `[data-reveal]`, shimmer en loop de `.btn-primary`, nav reactiva al scroll) incluida una vez en `layouts/app.blade.php` y `resources/js/interactions.js` — llega a todas las páginas autenticadas sin tocarlas una por una. Ver `.fondo-ambiente`/`.card-interactive`/`[data-reveal]` en `app.css`.
- Si se cambia algo de marca/colores, replicarlo también en el tema de Flutter (`app_movil/lib/core/theme/`) para mantener paridad visual web/app.

## 14. Patrón de autorización (controladores de restaurante)

```php
private function restaurante(): Restaurante
{
    return Restaurante::firstOrCreate(
        ['user_id' => auth()->id()],
        ['nombre' => auth()->user()->name, 'activo' => true]
    );
}
```

Propiedad de un recurso verificada con `abort_unless($model->restaurante_id === $this->restaurante()->id, 403)`.

## 15. Configuración de servicios externos

### Mailtrap (emails en desarrollo)

1. Crear cuenta en mailtrap.io.
2. Email Testing → Inboxes → Mi inbox → SMTP Settings → integración "Laravel".
3. Copiar credenciales a `.env` (`MAIL_MAILER=smtp`, `MAIL_HOST=sandbox.smtp.mailtrap.io`, etc.).
4. `php artisan config:clear`.

Requiere `QUEUE_CONNECTION=database` y `composer run dev` corriendo.

### ngrok (acceso desde otro dispositivo/red)

Se necesitan **dos túneles**: uno para el puerto 8000 (HTTP) y otro para el 8080 (Reverb/WebSockets). Luego actualizar `.env` con las URLs públicas y correr `php artisan config:clear && npm run build`.

### Firebase (push notifications)

Generar la cuenta de servicio en la consola de Firebase (Configuración del proyecto → Cuentas de servicio → Generar nueva clave privada) y colocarla en `storage/app/firebase/service-account.json`. Mientras no exista, el resto del backend funciona normal (falla en silencio).

### Gemini (chatbot IA)

1. Generar una API key gratuita en [aistudio.google.com/apikey](https://aistudio.google.com/apikey).
2. Agregar a `.env`: `GEMINI_API_KEY=...` y `GEMINI_MODEL=gemini-flash-latest`.
3. `php artisan config:clear`.

Sin key, el chatbot responde "no disponible" pero el resto de la app funciona igual. Ver sección 11.

## 16. Subida de imágenes

- Productos: `storage/app/public/productos/`. Restaurantes: `storage/app/public/restaurantes/`.
- Symlink requerido: `php artisan storage:link`.
- Al actualizar, se borra la imagen anterior con `Storage::disk('public')->delete(...)`.
- Sin imagen: placeholder SVG o emoji — **no usar URLs externas** para datos reales (solo permitido en la landing pública).

## 17. App móvil Flutter

Vive en `app_movil/` dentro de este mismo repositorio. Cubre cliente y domiciliario, ya conectada a esta API: login por Sanctum, pedidos, GPS real por WebSocket (Reverb), push notifications (Firebase), confirmación de entrega por QR, chatbot IA. Tiene su propia guía técnica en `app_movil/README.md` — consultarla antes de tocar código Flutter.

## 18. Estado del proyecto y roadmap

Ver la sección "Pendiente / Próximos pasos" que el equipo mantiene internamente para las siguientes prioridades (pagos en línea, cupones, PWA, tests automatizados, reportes CSV, etc.). Antes de iniciar una feature grande, confirmar con el equipo si sigue vigente.
