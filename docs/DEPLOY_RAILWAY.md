# Desplegar en Railway

Guía para llevar esta plataforma de "corre en mi PC + ngrok" a un servidor real en la nube, disponible 24/7 con un link fijo.

## 1. ¿Qué es Railway y por qué lo necesitamos?

Railway es un servicio de hosting en la nube: le conectas tu repositorio de GitHub y él construye y corre la aplicación en sus servidores. A diferencia de ngrok, el link **no cambia** y **no depende de que tu PC esté encendido**.

**Importante sobre el costo:** Railway no es gratis de forma permanente. Da un crédito de prueba inicial y luego cobra según el uso (RAM/CPU/tráfico) de los servicios que tengas corriendo. Para un proyecto pequeño como este (piloto en un solo pueblo) el costo mensual suele ser bajo, pero **vas a necesitar registrar una tarjeta** en algún momento para que los servicios sigan corriendo después del crédito gratuito.

## 2. Arquitectura que vamos a desplegar

Esta plataforma no es solo "una web" — tiene 3 procesos que deben correr **al mismo tiempo y por separado**:

| Servicio en Railway | Qué hace | Comando de inicio |
|---|---|---|
| **web** | Sirve las páginas y la API | `php artisan migrate --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=$PORT` |
| **queue** | Procesa la cola de emails (Mailtrap) y notificaciones push | `php artisan queue:work --tries=3 --timeout=60` |
| **reverb** | WebSockets — el mapa en tiempo real del pedido | `php artisan reverb:start --host=0.0.0.0 --port=$PORT` |
| **MySQL** (plugin de Railway) | Base de datos | — (administrado por Railway) |

Los 3 primeros se crean **desde el mismo repositorio de GitHub**, solo cambiando el "Start Command" de cada uno. Todos usan el mismo `Dockerfile` que ya está en la raíz del repo.

## 3. Antes de empezar — limitación importante sobre imágenes

El código actual guarda las imágenes de productos/restaurantes en el disco local (`storage/app/public/...`). **En Railway, el disco de un contenedor no es permanente**: si se reinicia o se vuelve a desplegar el servicio, las imágenes subidas se pierden, a menos que:

- **Opción rápida:** activar un **Volume** (disco persistente) de Railway en el servicio `web`, montado en `/app/storage/app/public`. Esto lo resuelve sin tocar código.
- **Opción robusta (a futuro, no necesaria para empezar):** mover el almacenamiento a un servicio tipo S3 (por ejemplo Cloudflare R2, con capa gratuita). Requiere cambios de código — no lo hacemos en este primer despliegue, pero quedará anotado como pendiente.

Para el piloto, usa la opción del Volume — es suficiente y no requiere cambios de código.

## 4. Variables de entorno necesarias

Estas son las variables que hay que configurar en Railway (en "Variables" del proyecto, compartidas entre los 3 servicios). Cópialas y complétalas:

Antes de pegar las variables, genera una clave nueva **solo para producción** (no reutilices la de tu `.env` local) corriendo esto en tu máquina y copiando el resultado:

```bash
php artisan key:generate --show
```

Esto imprime algo como `base64:xxxxxxxx...=`. Cópialo y pégalo directo en la variable `APP_KEY` de Railway — **nunca lo escribas en un archivo que se vaya a subir al repositorio**, ni lo compartas en texto plano.

```env
APP_NAME="Ubaté Eats"
APP_ENV=production
APP_KEY=<pega-aqui-el-valor-que-generaste-arriba>
APP_DEBUG=false
APP_URL=https://<el-dominio-que-te-da-railway-para-el-servicio-web>

APP_LOCALE=es
APP_FALLBACK_LOCALE=es

# Base de datos — usar las variables de referencia del plugin MySQL de Railway
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=ubate-eats
REVERB_APP_KEY=<genera-un-valor-aleatorio-cualquiera>
REVERB_APP_SECRET=<genera-otro-valor-aleatorio>
REVERB_HOST=<el-dominio-que-te-da-railway-para-el-servicio-reverb>
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

# Mailtrap (o el proveedor de correo que estés usando)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<tu-usuario-de-mailtrap>
MAIL_PASSWORD=<tu-password-de-mailtrap>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ubateeats.com
MAIL_FROM_NAME="${APP_NAME}"
```

> El `APP_KEY` de arriba ya fue generado especialmente para producción (no es el mismo que usas en tu `.env` local). No lo reutilices para nada más y no lo publiques en ningún lugar público — solo pégalo directo en las variables de Railway.

`REVERB_HOST`/`VITE_REVERB_HOST` los sabrás **después** de crear el servicio `reverb` (Railway te da el dominio en ese momento) — puedes dejarlos vacíos al principio y completarlos cuando lo tengas.

## 5. Pasos en el dashboard de Railway

1. Entra a [railway.app](https://railway.app) y crea una cuenta (puedes usar tu cuenta de GitHub para entrar directo).
2. **New Project → Deploy from GitHub repo** → elige `torres2014et/mi-plataforma`. Railway detectará el `Dockerfile` automáticamente.
3. Ese primer servicio será tu **web**:
   - En **Settings → Deploy**, en "Start Command" pon:
     `php artisan migrate --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=$PORT`
   - En **Settings → Networking**, activa "Generate Domain" para obtener tu URL pública (`https://algo.up.railway.app`).
   - En **Settings → Volumes**, agrega un volumen montado en `/app/storage/app/public`.
4. **New → Database → Add MySQL** dentro del mismo proyecto — Railway crea la base de datos y sus variables automáticamente.
5. **New → Empty Service** (dos veces más) para `queue` y `reverb`:
   - Para cada uno, en **Settings → Source**, conecta el mismo repo de GitHub (`torres2014et/mi-plataforma`).
   - En `queue`, Start Command: `php artisan queue:work --tries=3 --timeout=60`
   - En `reverb`, Start Command: `php artisan reverb:start --host=0.0.0.0 --port=$PORT`, y en **Networking** activa también "Generate Domain" (ese dominio es tu `REVERB_HOST`).
6. En el proyecto, ve a **Variables** a nivel de proyecto (no de un solo servicio) y pega las variables de la sección 4 — así las ven los 3 servicios a la vez. Ajusta `REVERB_HOST` con el dominio real que te dio el paso anterior.
7. Cada servicio se redesplegará solo al guardar variables o cambiar el start command. Revisa los logs de cada uno (pestaña **Deployments**) para confirmar que arrancó sin errores.
8. Cuando `web` esté arriba, entra a la URL que te dio Railway — deberías ver la página de bienvenida de la plataforma.

## 6. Después del primer despliegue

- Corre el seeder de usuarios de prueba una sola vez, desde la terminal de Railway del servicio `web` (**Settings → conectar por CLI** o el botón de terminal en el dashboard):
  ```bash
  php artisan db:seed --class=RolesAndTestUsersSeeder
  ```
- A partir de ahora, cada vez que hagas `git push` a `master`, Railway vuelve a desplegar automáticamente los 3 servicios con el código nuevo.
- Actualiza `APP_URL` en `.env` local o en la documentación con el nuevo dominio fijo, y ya puedes dejar de depender de ngrok.

## 7. Pendientes que quedan fuera de este primer despliegue

- Migrar imágenes de `storage/` a un servicio tipo S3/R2 para no depender del Volume (más robusto a largo plazo).
- Configurar un dominio propio (ej. `ubateeats.com`) en vez del subdominio `*.up.railway.app`.
- Activar push notifications reales subiendo `storage/app/firebase/service-account.json` como variable/secret en Railway en vez de archivo (Railway no persiste archivos sueltos fuera de un Volume).
