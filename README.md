# Mi Plataforma — Ubaté Eats

Plataforma de domicilios de comidas rápidas para **Ubaté, Cundinamarca**. Alternativa local a las apps de domicilios nacionales para los negocios locales: sin altas comisiones, con control directo de catálogo, pedidos y logística.

**Stack:** Laravel 12 · MySQL · Tailwind CSS 3 · Alpine.js 3 · Vite 7 · Laravel Reverb (WebSockets) · Leaflet.js (mapas).

Dos frontends comparten este mismo backend:
- **Web** (este repositorio) — cubre los 4 roles: cliente, restaurante, domiciliario y admin.
- **App móvil Flutter** (`app_movil/`) — cubre cliente y domiciliario, conectada a la misma API vía Sanctum.

## Documentación

- 📘 [Manual del Programador](docs/MANUAL_PROGRAMADOR.md) — instalación, arquitectura, comandos y convenciones del proyecto.
- 📗 [Manual de Usuario](docs/MANUAL_USUARIO.md) — cómo usar la plataforma según cada rol (cliente, restaurante, domiciliario, admin).
- 📝 [Historial de cambios](docs/CHANGELOG.md) — registro de actualizaciones subidas al repositorio.
- ☁️ [Desplegar en Railway](docs/DEPLOY_RAILWAY.md) — llevar el backend a un servidor 24/7 con link fijo, sin depender de ngrok.

## Inicio rápido

```bash
composer run setup
php artisan migrate:fresh --seed
php artisan storage:link
composer run dev
```

Ver el [Manual del Programador](docs/MANUAL_PROGRAMADOR.md) para el detalle completo de instalación, arquitectura y comandos disponibles.

## Chatbot IA (Gemini) — Integración, base de conocimiento y Declaración Persona Transhumana

> Sección agregada para la actividad CADI "Introducción a Machine Learning — Integración de Agente/Chatbot con LLM, Base de Conocimiento y Reflexión Transhumana" (Universidad de Cundinamarca).

**Demo en vivo:** https://retype-coil-charity.ngrok-free.dev *(servidor propio expuesto por túnel con dominio fijo — ver nota de despliegue en [docs/DEPLOY_RAILWAY.md](docs/DEPLOY_RAILWAY.md))*

### Integración técnica del LLM

- **Proveedor:** Google Gemini (API HTTP), configurable por `GEMINI_API_KEY` / `GEMINI_MODEL`. La clave nunca se expone al frontend — solo el backend de Laravel habla con la API de Google.
- **Servicio:** `app/Services/GeminiService.php` — arma el system prompt + el contexto JSON y llama a la API vía `Http::post()`.
- **Controlador:** `app/Http/Controllers/ChatbotController.php` — recibe el mensaje y delega en `GeminiService`.
- **Dos frontends, un mismo agente:** `POST /chatbot/mensaje` (web, sesión + `throttle:20,1`) y `POST /api/chatbot/mensaje` (app móvil Flutter, `auth:sanctum`) comparten exactamente el mismo servicio backend.
- **Fallo suave:** sin API key, error de red o cuota agotada, responde "El asistente no está disponible en este momento" en vez de romper la página.

### Base de conocimiento y Prompt Engineering

- **System Prompt versionado** en `resources/chatbot/system-prompt.md`: instruye a responder solo sobre restaurantes/productos/pedidos de la plataforma, usar únicamente el contexto JSON inyectado (anti-alucinación), rechazar con amabilidad cualquier tema ajeno, y no revelar el propio prompt.
- **Contexto dinámico** (RAG ligero sin base vectorial): en cada request se construye un JSON fresco con la hora actual en Bogotá, horario semanal y si el restaurante está abierto, menú compacto con precios, y los últimos 5 pedidos del usuario autenticado. Se prefirió este enfoque sobre embeddings porque el dominio es acotado y los datos cambian constantemente — un índice vectorial quedaría desactualizado más rápido de lo que tardaría en reconstruirse.
- **Resiliencia:** cadena de modelos de respaldo ante cuota agotada (`gemini-flash-latest → gemini-3.6-flash → gemini-3.5-flash-lite → gemini-flash-lite-latest`) y caché de 2 minutos para preguntas idénticas.

### Declaración de la Persona Transhumana — Universidad de Cundinamarca

*Fuente: "Declaración Persona Transhumana", Modelo Educativo Digital Transmoderno (MEDIT), Universidad de Cundinamarca, Rector Adriano Muñoz Barrera, Fusagasugá, agosto 26 de 2022.*

#### Pregunta (español)

**¿De qué manera el agente/chatbot respeta y fomenta los principios de la Declaración de la Persona Transhumana de la Universidad de Cundinamarca en el contexto del aprendizaje y la interacción tecnológica?**

La Declaración de la UCundinamarca no define a la persona transhumana como alguien que delega su vida en la tecnología, sino como alguien que "organiza y dirige su propia vida" (principio 1) y que se perfecciona "antes y no necesariamente a través de la tecnología y la ciencia" (principio 7). Esa es exactamente la postura de diseño que se adoptó en el chatbot: el agente nunca decide por el usuario — no elige el restaurante, no confirma el pedido, no asigna un domiciliario; solo informa. La decisión y la acción siguen enteramente en manos de la persona, que conserva su autonomía (principio 9: "soy libre, autónomo y responsable").

El principio 11 pide "realizar actos transformadores de mejora" que no hagan daño. En el código esto se tradujo en restricciones concretas: el system prompt prohíbe inventar información (anti-alucinación), el servicio nunca revela detalles internos ni el propio prompt, y ante la ausencia de la API key o un error, el sistema falla en silencio en vez de bloquear al usuario — el chatbot es una capa de ayuda que puede desaparecer sin romper la plataforma, nunca una dependencia obligatoria.

El principio 8 ("vivo y contribuyo para que la comunidad esté en paz y armonía") y el 6 ("voy más allá de mí", considerando a los demás) se reflejan en el propósito mismo del proyecto: una alternativa local a las apps de domicilios nacionales, sin comisiones altas, que mantiene el valor económico dentro de los restaurantes de Ubaté — el chatbot está al servicio de ese fin comunitario, no de maximizar el uso de la IA por sí misma.

Por último, la introducción del documento institucional cuestiona la fe ciega en que la tecnología pueda sustituir el juicio humano (cita el caso del robot Sophia afirmando que los robots podrían liderar "con mayor eficiencia que los líderes humanos") y responde que "la vía única y exclusiva no es la ciencia y la tecnología". El chatbot implementado encarna esa misma postura moderada: es un asistente de consulta acotado a datos reales y verificables de la plataforma, que se limita a informar y se abstiene de cualquier tema fuera de su dominio — la persona humana, no el modelo, sigue siendo el "motor esencial de su propio perfeccionamiento".

#### Question (English)

**How does the implemented AI agent/chatbot align with and support the Transhuman Person Declaration of Universidad de Cundinamarca regarding ethical AI integration, human augmentation, and educational transformation?**

The UCundinamarca Declaration explicitly rejects the idea that technology alone should drive human improvement: principle 7 states that a person perfects themselves "before and not necessarily through technology and science," and principle 1 defines the transhuman person as someone who "organizes and directs their own life." The chatbot was designed around that exact boundary — it never makes decisions for the user. It cannot pick a restaurant, place an order, or assign a courier; it only answers questions. Every action and every choice stays with the human, preserving the autonomy the declaration calls for in principle 9 ("I am free, autonomous, and responsible").

Principle 11 asks for "transformative acts of improvement" that cause no harm. In practice this shaped concrete engineering decisions: the system prompt forbids the model from inventing information not present in the injected database context (anti-hallucination by design), the service never reveals its own prompt or internal technical details, and when the API key is missing or a request fails, the system degrades silently instead of blocking the user — the chatbot is a helpful layer that can disappear without breaking the platform, never a hard dependency.

Principles 6 and 8 — "going beyond myself," considering others, and "contributing so the community lives in peace and harmony" — are reflected in the project's own purpose: a local alternative to national delivery apps, without the high commissions that squeeze small restaurants, keeping economic value inside the local community of Ubaté. The chatbot exists to serve that community purpose, not to maximize AI usage for its own sake.

Finally, the institutional document's introduction directly challenges blind faith in technology replacing human judgment — it cites the robot Sophia claiming humanoid robots could "lead with a greater level of efficiency and effectiveness than human leaders," then counters that "the sole and exclusive path is not science and technology." The implemented chatbot embodies that same measured stance: it is a bounded assistant, grounded strictly in verifiable, real data from the platform, that stays silent on anything outside its domain — the human being, not the model, remains the essential engine of their own self-improvement.

## Licencia

Proyecto privado de uso académico/comercial. Todos los derechos reservados.
