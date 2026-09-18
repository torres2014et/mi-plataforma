# Historial de cambios

Registro de actualizaciones subidas a este repositorio. Formato: fecha, resumen del cambio, archivos/áreas afectadas.

## 2026-09-18 — Subida inicial a GitHub

- Se inicializó el repositorio Git y se subió el proyecto completo a GitHub como repositorio público: [`torres2014et/mi-plataforma`](https://github.com/torres2014et/mi-plataforma).
- Se creó el [Manual del Programador](MANUAL_PROGRAMADOR.md) — instalación, arquitectura, comandos, API móvil, base de datos y convenciones del proyecto.
- Se creó el [Manual de Usuario](MANUAL_USUARIO.md) — guía de uso por rol (cliente, restaurante, domiciliario, administrador) y preguntas frecuentes.
- Se reemplazó el `README.md` genérico de Laravel por uno propio del proyecto, con enlaces a ambos manuales.
- Se ajustó `.gitignore` para excluir del repositorio: `.env`, `CLAUDE.md`, la carpeta `.claude/`, `documentacion-pgc/` (trabajo de grado académico), los `FASE2.md`/`FASE3.md`/`FASE4.md`/`CLAUDE.md` internos de `app_movil/`, y capturas de pantalla o notas sueltas de la raíz (`Laravel`, `Reverb`, `apunta`, `poster.html`, `server.log`, `mockasts/`).
- Se verificó que ningún secreto o credencial real quedó incluido en el commit.
- Se agregó `docs/CHANGELOG.md` (este archivo) para llevar el historial de cambios versionado.

## 2026-09-18 — Preparación para desplegar en Railway

- Se agregó un `Dockerfile` en la raíz, pensado para reutilizarse en 3 servicios de Railway (web, cola de trabajos, Reverb/WebSockets) cambiando solo el comando de inicio de cada uno.
- Se agregó `.dockerignore` para no incluir en la imagen archivos que no debe (`.env`, documentación interna, `node_modules`, `vendor`, capturas de pantalla, etc.).
- Se creó [`docs/DEPLOY_RAILWAY.md`](DEPLOY_RAILWAY.md) — guía completa: arquitectura de los 3 servicios, variables de entorno necesarias y pasos exactos en el dashboard de Railway, incluyendo la advertencia sobre el almacenamiento de imágenes (no persiste entre despliegues sin un Volume).

<!--
Cómo agregar una entrada nueva:

## AAAA-MM-DD — Título breve del cambio

- Qué se hizo, en una o dos líneas por punto.
-->
