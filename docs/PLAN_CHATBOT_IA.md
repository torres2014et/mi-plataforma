# Plan de Trabajo — Chatbot IA para Mi Plataforma (Domicilios Ubaté)

> Adaptación del prompt de la asignatura ("chatbot IA en app estilo Spotify") al proyecto real del estudiante: una plataforma de domicilios en Laravel 12. Se mantiene la estructura de 5 fases solicitada por el docente, sustituyendo canciones/artistas por restaurantes/productos, y el stack "HTML+CSS+JS vanilla" por el stack ya establecido del proyecto (Laravel · Blade · Alpine.js · Tailwind). Cada punto donde la adaptación se aparta del prompt original queda marcado como **Nota de adaptación**.

## Resumen del objetivo

Incorporar un asistente conversacional (chatbot) dentro de `mi-plataforma`, impulsado por **Gemini 2.5 Flash**, que responda preguntas de los clientes sobre **restaurantes, productos del menú y pedidos alojados en la base de datos del sitio**, y que rechace amablemente cualquier consulta fuera de ese contexto.

**Nota de adaptación (stack):** el prompt original pide "HTML5, CSS3 y JS vanilla desde cero" y ejecución en AntiGravity. Este proyecto ya tiene una arquitectura estable (Laravel 12 + Blade + Alpine.js + Tailwind, corriendo con `composer run dev`), documentada en `CLAUDE.md`. En vez de crear una app paralela, el chatbot se integra como un módulo más de esa arquitectura — sigue siendo JS ligero del lado del cliente (Alpine.js) hablando con un backend propio, que es el patrón real y seguro para este caso de uso.

---

## Fase 1: Arquitectura y estructura de archivos

Archivos nuevos a crear dentro de la estructura existente de Laravel:

```
mi-plataforma/
├── app/
│   ├── Http/Controllers/
│   │   └── ChatbotController.php          # recibe el mensaje del usuario, arma el prompt, llama a GeminiService
│   └── Services/
│       └── GeminiService.php              # cliente HTTP hacia Gemini 2.5 Flash, construcción del system prompt
├── config/
│   └── services.php                       # entrada 'gemini' => ['key' => env('GEMINI_API_KEY'), 'model' => ...]
├── resources/
│   ├── views/
│   │   └── partials/
│   │       └── chatbot-widget.blade.php   # widget flotante de chat, incluido en layouts/app.blade.php
│   └── js/
│       └── chatbot.js                     # lógica Alpine.js: estado de mensajes, fetch al backend, loading/error
├── routes/
│   └── web.php                            # POST /chatbot/mensaje (throttle:chat)
├── storage/
│   └── app/
│       └── chatbot/
│           └── system-prompt.md           # texto base de instrucciones del bot (versionable, editable sin tocar código)
└── .env                                   # GEMINI_API_KEY=, GEMINI_MODEL=gemini-flash-latest
```

**Decisiones clave de esta fase:**
- El widget se inyecta una sola vez en `layouts/app.blade.php` (o en `welcome.blade.php` + `app.blade.php` si también se quiere para visitantes no autenticados), disponible en todas las vistas.
- La llamada a Gemini **nunca ocurre desde el navegador**: el frontend solo llama a `POST /chatbot/mensaje` (mismo dominio), y es `GeminiService` quien tiene la API key y habla con Google por detrás. Esto resuelve el problema de seguridad de exponer la key en JS del cliente.
- Sigue el mismo patrón de "fallo silencioso" que ya usa `FcmSender`: si `GEMINI_API_KEY` no está configurada, el endpoint responde con un mensaje tipo "el asistente no está disponible en este momento" en vez de romper la app.

**Nota de adaptación (AntiGravity):** no aplica un entorno de ejecución externo — el chatbot corre dentro del mismo `composer run dev` que ya levanta servidor, cola, Vite y Reverb.

---

## Fase 2: Diseño de la base de conocimiento (JSON)

**Nota de adaptación (fuente de datos):** el prompt pide un catálogo estático en JSON. Aquí los restaurantes/productos ya viven en MySQL (`Restaurante`, `Producto`) y cambian constantemente (nuevos productos, precios, disponibilidad). Mantener un JSON estático duplicaría la fuente de verdad y se desactualizaría. En su lugar, `GeminiService` **genera el JSON de contexto dinámicamente** a partir de una consulta a la base de datos justo antes de llamar a Gemini — mismo formato/esquema que pedía el profe, pero siempre actualizado.

**Esquema del JSON de contexto (generado en runtime):**

```json
{
  "restaurantes": [
    {
      "id": 3,
      "nombre": "Asadero El Fogón",
      "descripcion": "Comida típica a la parrilla",
      "activo": true,
      "costo_domicilio": 3000,
      "promedio_estrellas": 4.6,
      "productos": [
        {
          "id": 15,
          "nombre": "Bandeja paisa",
          "categoria": "Platos fuertes",
          "precio": 22000,
          "disponible": true,
          "descripcion": "Frijoles, arroz, carne, chicharrón, huevo, plátano y arepa"
        }
      ]
    }
  ]
}
```

**Estrategia de inyección de contexto (para no saturar el prompt):**
- Chat abierto en la página de un restaurante (`cliente/restaurantes/show`) → se inyecta **solo ese restaurante** con su menú completo.
- Chat abierto en cualquier otra vista (home, dashboard) → se inyecta un **resumen** (nombre + categoría + rango de precios) de los restaurantes activos, no el menú completo de todos.
- Si el catálogo crece mucho a futuro, esta fase deja la puerta abierta a una Fase 2.1 con búsqueda/embeddings real en vez de mandar todo el contexto — no necesario para el volumen actual del piloto en Ubaté.

**Esquema del "site JSON" (navegación):** no se requiere — Laravel ya gestiona rutas, vistas y permisos vía `routes/web.php` + Spatie roles (ver `CLAUDE.md`). Duplicarlo en JSON sería redundante con la arquitectura existente.

---

## Fase 3: Desarrollo del frontend del widget

1. **Botón flotante** (esquina inferior derecha), visible en todas las vistas autenticadas, usando las clases ya existentes del design system (`.btn-primary`, glow naranja `brand-500`) para mantener coherencia visual — no se introduce paleta verde ni tema claro nuevo (ver nota abajo).
2. **Panel de chat** (`chatbot-widget.blade.php`): historial de mensajes, input de texto, indicador de "escribiendo…" mientras se espera la respuesta de Gemini. Estilo `.card` (zinc-900, borde white/8) igual que el resto del sitio.
3. **Estado con Alpine.js** (`chatbot.js`): array `mensajes[]`, método `enviar()` que hace `fetch('/chatbot/mensaje', {method:'POST', body: {mensaje}})`, maneja loading y errores de red.
4. **Accesibilidad básica:** `x-cloak` mientras Alpine inicializa (patrón ya usado en el resto del proyecto), scroll automático al último mensaje, botón de cerrar/minimizar.

**Nota de adaptación (tema claro/oscuro verde):** el prompt pide paleta verde con fondo claro por defecto y toggle claro/oscuro. `mi-plataforma` ya tiene un sistema de diseño propio, estable y documentado (`#0D0D0F` de fondo, acento naranja `brand-500`, dark-first) que `CLAUDE.md` pide explícitamente **no romper** para mantener paridad con la app Flutter. Por eso el widget de chat reutiliza esa identidad en vez de introducir un tema verde/claro nuevo. Si el profe evalúa específicamente el requisito literal de "paleta verde + toggle claro/oscuro", esto debe conversarse aparte — no se implementa por defecto en este plan.

---

## Fase 4: Implementación del módulo de chatbot (integración con Gemini)

1. **Configuración (`.env` + `config/services.php`):**
   ```env
   GEMINI_API_KEY=tu_api_key_de_google_ai_studio
   GEMINI_MODEL=gemini-flash-latest
   ```
   ```php
   // config/services.php
   'gemini' => [
       'key'   => env('GEMINI_API_KEY'),
       'model' => env('GEMINI_MODEL', 'gemini-flash-latest'),
   ],
   ```

   **Nota de la Fase 5 (elección de modelo):** el modelo `gemini-2.5-flash` fue descontinuado por Google para API keys nuevas (404 "no longer available to new users"). El reemplazo directo `gemini-3.6-flash` (preview) devolvió **503 "high demand"** de forma consistente en las pruebas (confirmado con `curl` directo a la API, fuera de Laravel, con timeouts de hasta 90s). Se optó por `gemini-flash-latest`, el alias estable que Google mantiene apuntando siempre al modelo flash vigente (resolvió a `gemini-3.8-flash` en las pruebas) — responde en ~1-2s.

2. **`GeminiService`** — responsabilidades:
   - Construir el **system prompt** (desde `storage/app/chatbot/system-prompt.md`) con la regla estricta: *"Solo respondes sobre restaurantes, productos, precios, horarios y pedidos de esta plataforma. Si la pregunta es de otro tema, recházala con amabilidad y redirige al usuario a lo que sí puedes ayudar."*
   - Serializar el contexto de la Fase 2 (restaurante actual o resumen general) y anexarlo al prompt.
   - Llamar a la API de Gemini vía `Http::post()` (equivalente Laravel al `curl` del prompt original):
     ```
     POST https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={key}
     ```
   - Devolver solo el texto de la respuesta al controller (nunca la key ni la respuesta cruda de Google al frontend).
   - Igual que `FcmSender`: si falla la llamada (sin key, error de red, cuota excedida) **no debe romper la página** — responde con un mensaje de fallback y loguea el error.

3. **`ChatbotController@enviarMensaje`:**
   - Valida `mensaje` (string, requerido, límite de longitud).
   - Recupera contexto según la vista/restaurante actual (parámetro opcional `restaurante_id`).
   - Llama a `GeminiService`, retorna JSON `{ respuesta: "..." }`.
   - Ruta protegida con `throttle` para evitar abuso de la cuota de Gemini.

4. **Manejo de "contexto cerrado":** el bot no debe usar conocimiento general de Gemini fuera del JSON inyectado. Esto se logra 100% vía instrucciones en el system prompt (no hay forma de "bloquear" el modelo técnicamente, se restringe por prompt engineering) — la Fase 5 valida que esto se cumpla en la práctica.

---

## Fase 5: Pruebas, validación y ejecución local

**Checklist de pruebas manuales:**

| Prueba | Resultado esperado | Estado |
|---|---|---|
| Preguntar por un producto real del catálogo | Responde con datos correctos (nombre, precio, descripción) tomados del contexto inyectado | ✅ Probado (2026-09-22, rol cliente vía Chrome) — preguntó por restaurantes con domicilio gratis y respondió con "El Pollo Don Luis" y "plapizza", ambos $0 de domicilio, datos reales de la BD |
| Preguntar por un restaurante inactivo/inexistente | Responde que no lo encuentra en la plataforma | Pendiente de probar |
| Preguntar algo fuera de contexto (ej. "cuál es la capital de Francia") | Rechaza amablemente y redirige al tema de la plataforma | Pendiente de probar |
| Quitar `GEMINI_API_KEY` del `.env` | El widget sigue funcionando, responde con mensaje de "asistente no disponible" (no rompe la app) | ✅ Comportamiento verificado indirectamente — se disparó este mismo mensaje cuando la llamada a Gemini falló (503 del modelo preview), confirma que el fallback funciona |
| Revisar pestaña Network del navegador | La API key de Google **no aparece** en ninguna petición hecha desde el cliente — solo se ve la llamada a `/chatbot/mensaje` del propio dominio | Pendiente de probar |
| Probar en las 4 vistas por rol (cliente, restaurante, domiciliario, admin) | El widget aparece y responde de forma consistente donde esté habilitado | Probado en cliente; pendiente restaurante/domiciliario/admin |

**Ejecución local:**
```bash
composer run dev   # levanta servidor + queue + Vite + Reverb, incluido el endpoint del chatbot
```

**Nota de adaptación (AntiGravity):** se sustituye por el flujo de desarrollo local ya documentado en `CLAUDE.md` (`composer run dev`), sin necesidad de un entorno externo adicional.

---

## Resumen de adaptaciones respecto al prompt original

| Requisito del prompt | Adaptación aplicada |
|---|---|
| HTML/CSS/JS vanilla desde cero | Se usa el stack real ya construido (Laravel + Blade + Alpine.js) |
| AntiGravity | Se usa `composer run dev`, flujo local ya establecido |
| Paleta verde, claro por defecto, toggle | Se mantiene el design system existente (dark + naranja) por estabilidad visual con la app Flutter |
| Catálogo y navegación en JSON estático | Catálogo real en MySQL; el JSON de contexto para el bot se genera dinámicamente en cada consulta |
| `.env` leído desde JS vanilla | `.env` se lee del lado del servidor (Laravel); el frontend nunca ve la API key |
| Base de conocimiento del bot | JSON generado en runtime desde `Restaurante`/`Producto`, mismo esquema conceptual que pedía el profe |
