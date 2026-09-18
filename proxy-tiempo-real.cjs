/*
 * Proxy de tiempo real — Fase 3 (Domicilios Ubaté)
 * ------------------------------------------------------------------
 * Une, en UN solo puerto público, el backend Laravel y el servidor de
 * websockets Reverb, para poder exponer AMBOS por el único túnel de ngrok
 * (plan free = 1 túnel) y que el GPS en vivo funcione DESDE CUALQUIER SITIO.
 *
 *   ngrok (https) ──► este proxy (:9000) ──┬─ /app/*  ──► Reverb  (127.0.0.1:8080)
 *                                          └─ resto   ──► Laravel (127.0.0.1:8000)
 *
 * - HTTP normal (API + web) → Laravel.
 * - WebSocket (wss://host/app/{key}) → Reverb (vía el evento 'upgrade').
 * Sin dependencias: solo los módulos http/net de Node. Arráncalo con:
 *     node proxy-tiempo-real.cjs
 */
const http = require('http');
const net = require('net');

const LISTEN  = Number(process.env.PROXY_PORT || 9000);
const LARAVEL = { host: '127.0.0.1', port: Number(process.env.LARAVEL_PORT || 8000) };
const REVERB  = { host: '127.0.0.1', port: Number(process.env.REVERB_PORT  || 8080) };

// Todo lo que empiece por /app va a Reverb (el canal websocket de Pusher/Reverb
// es wss://host/app/{appKey}); el resto, al backend Laravel.
const destino = (url) => (url.startsWith('/app') ? REVERB : LARAVEL);

// ── Peticiones HTTP normales ──────────────────────────────────────
const server = http.createServer((req, res) => {
  const t = destino(req.url);
  const proxyReq = http.request(
    { host: t.host, port: t.port, method: req.method, path: req.url, headers: req.headers },
    (proxyRes) => {
      res.writeHead(proxyRes.statusCode, proxyRes.headers);
      proxyRes.pipe(res);
    },
  );
  proxyReq.on('error', () => {
    if (!res.headersSent) res.writeHead(502);
    res.end('Proxy: backend no disponible');
  });
  req.pipe(proxyReq);
});

// ── Upgrades de WebSocket (Reverb) ────────────────────────────────
server.on('upgrade', (req, socket, head) => {
  const t = destino(req.url);
  const upstream = net.connect(t.port, t.host, () => {
    // Reenviar la línea de petición + cabeceras tal cual y luego hacer de tubo.
    let raw = `${req.method} ${req.url} HTTP/1.1\r\n`;
    for (let i = 0; i < req.rawHeaders.length; i += 2) {
      raw += `${req.rawHeaders[i]}: ${req.rawHeaders[i + 1]}\r\n`;
    }
    raw += '\r\n';
    upstream.write(raw);
    if (head && head.length) upstream.write(head);
    upstream.pipe(socket);
    socket.pipe(upstream);
  });
  upstream.on('error', () => socket.destroy());
  socket.on('error', () => upstream.destroy());
});

server.listen(LISTEN, '0.0.0.0', () => {
  console.log(`[proxy-tiempo-real] escuchando en :${LISTEN}`);
  console.log(`   /app/*  -> Reverb  127.0.0.1:${REVERB.port}`);
  console.log(`   resto   -> Laravel 127.0.0.1:${LARAVEL.port}`);
});
