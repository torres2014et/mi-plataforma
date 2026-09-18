/*
 * Prueba END-TO-END del flujo completo (Fase 3) — Domicilios Ubaté.
 * Recorre, por la API real (a través del proxy :9000), todo el ciclo y verifica
 * que el GPS del domiciliario llega por WEBSOCKET tal como lo recibe el cliente.
 *
 *   cliente crea pedido -> domiciliario acepta -> estados hasta en_camino
 *   -> domiciliario emite GPS -> (WS) el cliente lo recibe -> confirma entrega.
 *
 * Uso:  node test-flujo-completo.cjs
 * Requiere: backend + Reverb + proxy levantados (INICIAR-SERVIDOR.bat).
 */
const WebSocket = require('ws');

const BASE   = process.env.BASE || 'http://localhost:9000';
const WS_URL = (process.env.WS || 'ws://localhost:9000') + '/app/2pqepgpfuvt7rfffgsnm?protocol=7&client=node&version=1.0';
const REST_ID = 1, PROD_ID = 1, PROD_NOMBRE = 'Pollo asado entero', PRECIO = 32000;

const ok = (m) => console.log('  \x1b[32mOK\x1b[0m   ' + m);
const fail = (m) => { console.log('  \x1b[31mFAIL\x1b[0m ' + m); process.exitCode = 1; };
const info = (m) => console.log('\x1b[36m• ' + m + '\x1b[0m');
const sleep = (ms) => new Promise(r => setTimeout(r, ms));

async function api(path, { method = 'GET', token, body } = {}) {
  const res = await fetch(BASE + path, {
    method,
    headers: {
      'Accept': 'application/json',
      'ngrok-skip-browser-warning': 'true',
      ...(body ? { 'Content-Type': 'application/json' } : {}),
      ...(token ? { 'Authorization': 'Bearer ' + token } : {}),
    },
    body: body ? JSON.stringify(body) : undefined,
  });
  let json = null;
  try { json = await res.json(); } catch (_) {}
  return { status: res.status, json };
}

async function registrar(rol) {
  const stamp = Date.now() + Math.floor(Math.random() * 1000);
  const email = `test_${rol}_${stamp}@test.com`;
  const r = await api('/api/register', { method: 'POST', body: {
    name: `Test ${rol}`, email, telefono: '3000000000',
    password: 'password123', password_confirmation: 'password123', rol,
  }});
  if (r.status >= 300 || !r.json?.token) throw new Error(`register ${rol} fallo (${r.status}): ${JSON.stringify(r.json)}`);
  return { token: r.json.token, email, id: r.json.user?.id };
}

async function login(email, password) {
  const r = await api('/api/login', { method: 'POST', body: { email, password } });
  if (r.status >= 300 || !r.json?.token) throw new Error(`login ${email} fallo (${r.status}): ${JSON.stringify(r.json)}`);
  return { token: r.json.token, email, id: r.json.user?.id };
}

// ── Suscriptor websocket genérico (protocolo Pusher/Reverb), como la app/web ──
// `canal` es el nombre privado completo, p. ej. `private-pedido.5` o
// `private-restaurante.1`. Devuelve { ws, recibidos } y va acumulando eventos.
function abrirCanal(canal, token) {
  return new Promise((resolve, reject) => {
    const recibidos = [];
    const ws = new WebSocket(WS_URL);
    let socketId = null;
    const t = setTimeout(() => reject(new Error('timeout suscripcion WS: ' + canal)), 12000);

    ws.on('message', async (buf) => {
      const msg = JSON.parse(buf.toString());
      if (msg.event === 'pusher:ping') { ws.send(JSON.stringify({ event: 'pusher:pong', data: {} })); return; }
      if (msg.event === 'pusher:connection_established') {
        socketId = JSON.parse(msg.data).socket_id;
        // Firmar el canal privado con el token Sanctum del usuario.
        const auth = await api('/api/broadcasting/auth', { method: 'POST', token, body: {
          socket_id: socketId, channel_name: canal,
        }});
        if (!auth.json?.auth) { clearTimeout(t); return reject(new Error('broadcasting/auth sin firma (' + canal + '): ' + JSON.stringify(auth.json))); }
        ws.send(JSON.stringify({ event: 'pusher:subscribe', data: { auth: auth.json.auth, channel: canal } }));
        return;
      }
      if (msg.event === 'pusher_internal:subscription_succeeded') {
        clearTimeout(t);
        return resolve({ ws, recibidos });
      }
      if (msg.event && !msg.event.startsWith('pusher')) {
        recibidos.push({ event: msg.event, data: msg.data ? JSON.parse(msg.data) : null });
      }
    });
    ws.on('error', (e) => { clearTimeout(t); reject(e); });
  });
}

(async () => {
  console.log('\n=== PRUEBA E2E DEL FLUJO COMPLETO ===\n');

  // 0) Health
  const ping = await api('/api/ping');
  if (ping.json?.ok) ok('API responde por el proxy'); else return fail('API no responde: ' + JSON.stringify(ping));

  // 1) Cuentas
  info('1) Registrando cliente y domiciliario');
  const cliente = await registrar('cliente');
  const domi = await registrar('domiciliario');
  ok(`cliente ${cliente.email} (#${cliente.id}) y domiciliario ${domi.email} (#${domi.id})`);

  // 2) Cliente crea el pedido CON coordenadas
  info('2) Cliente crea pedido (con lat/lng)');
  const latEntrega = 5.3050, lngEntrega = -73.8200;
  const crear = await api('/api/pedidos', { method: 'POST', token: cliente.token, body: {
    restaurante_id: REST_ID,
    items: [{ producto_id: PROD_ID, nombre_producto: PROD_NOMBRE, precio_unitario: PRECIO, cantidad: 1 }],
    costo_domicilio: 0,
    direccion_entrega: 'Calle 5 #10-20, Ubaté (prueba)',
    lat_entrega: latEntrega, lng_entrega: lngEntrega,
  }});
  const pedido = crear.json?.data;
  if (!pedido?.id) return fail('no se creó el pedido: ' + JSON.stringify(crear));
  const codigo = pedido.codigo_confirmacion;
  ok(`pedido #${pedido.id} creado, estado=${pedido.estado}, codigo QR=${codigo}`);
  if (pedido.lat_entrega && pedido.lng_entrega) ok('el pedido guardó las coordenadas de entrega'); else fail('el pedido NO guardó lat/lng (falla del checkout)');

  // 3) El RESTAURANTE (dueño) se suscribe a su canal ANTES de que acepten, para
  //    comprobar que se entera en vivo de que el domiciliario va a recoger.
  info('3) Restaurante se suscribe a su canal (avisos en vivo)');
  let canalRest = null;
  try {
    const vendedor = await login('vendedor@test.com', 'password');
    canalRest = await abrirCanal(`private-restaurante.${pedido.restaurante_id}`, vendedor.token);
    ok(`restaurante #${pedido.restaurante_id} suscrito a su canal`);
  } catch (e) {
    info('   (aviso: no se pudo suscribir el restaurante: ' + e.message + ')');
  }

  // 4) Domiciliario lo ve disponible y lo acepta
  info('4) Domiciliario ve disponibles y acepta');
  const disp = await api('/api/domiciliario/pedidos/disponibles', { token: domi.token });
  const visible = (disp.json?.data || []).some(p => p.id === pedido.id);
  if (visible) ok('el pedido aparece en "disponibles"'); else fail('el pedido NO aparece en disponibles');
  const acc = await api(`/api/pedidos/${pedido.id}/aceptar`, { method: 'POST', token: domi.token, body: { medio_transporte: 'moto' } });
  if (acc.json?.data?.domiciliario_id === domi.id) ok('domiciliario asignado'); else fail('no se asignó el domiciliario: ' + JSON.stringify(acc.json));

  // 4.bis) El domiciliario pulsa "Voy a recoger": AHÍ el restaurante se entera
  //        en vivo de que va en camino a recoger (separado de la aceptación).
  info('4.bis) Domiciliario "Voy a recoger" -> avisa al restaurante');
  const rec = await api(`/api/pedidos/${pedido.id}/recoger`, { method: 'POST', token: domi.token });
  if (rec.json?.data?.recogiendo === true) ok('el pedido quedó marcado como "recogiendo"'); else fail('no se marcó recogiendo: ' + JSON.stringify(rec.json));
  if (canalRest) {
    await sleep(800);
    const aviso = canalRest.recibidos.find(e => e.event === 'domiciliario.acepto');
    if (aviso) ok(`el restaurante recibió en vivo: "${aviso.data?.domiciliario}" va a recoger (medio=${aviso.data?.medio})`);
    else fail('el restaurante NO recibió el aviso de que el domiciliario va en camino a recoger');
  }

  // 5) Cliente abre el canal en vivo (como la app/web)
  info('5) Cliente se suscribe al canal privado del pedido (websocket)');
  let canal;
  try { canal = await abrirCanal(`private-pedido.${pedido.id}`, cliente.token); ok('suscripción al canal privado confirmada'); }
  catch (e) { return fail('no se pudo suscribir al WS: ' + e.message); }

  // 6) Avanzar estados hasta en_camino (cada cambio debe emitir por WS)
  info('6) Avanzando estados: confirmado -> en_preparacion -> en_camino');
  for (const estado of ['confirmado', 'en_preparacion', 'en_camino']) {
    const r = await api(`/api/pedidos/${pedido.id}/estado`, { method: 'PATCH', token: domi.token, body: { estado } });
    if (r.json?.data?.estado === estado) ok(`-> ${estado}`); else fail(`no avanzó a ${estado}: ${JSON.stringify(r.json)}`);
    await sleep(400);
  }

  // 7) Domiciliario emite GPS (la pieza nueva de Fase 3)
  info('7) Domiciliario emite 3 posiciones GPS');
  const posiciones = [[5.3100, -73.8150], [5.3080, -73.8175], [5.3060, -73.8195]];
  for (const [lat, lng] of posiciones) {
    const r = await api(`/api/pedidos/${pedido.id}/ubicacion`, { method: 'POST', token: domi.token, body: { lat, lng } });
    if (r.json?.ok) ok(`emitida (${lat}, ${lng})`); else fail(`POST /ubicacion falló: ${r.status} ${JSON.stringify(r.json)}`);
    await sleep(500);
  }

  // 8) Verificar que el cliente RECIBIÓ el GPS por websocket
  info('8) Verificando recepción por websocket (lo critico)');
  await sleep(1200);
  const ubic = canal.recibidos.filter(e => e.event === 'ubicacion');
  const estados = canal.recibidos.filter(e => e.event === 'estado.actualizado');
  if (estados.length >= 1) ok(`recibió ${estados.length} cambios de estado por WS`); else fail('NO llegaron cambios de estado por WS');
  if (ubic.length >= 1) {
    ok(`recibió ${ubic.length} posiciones GPS por WS`);
    const ult = ubic[ubic.length - 1].data;
    const esperado = posiciones[posiciones.length - 1];
    if (Math.abs(ult.lat - esperado[0]) < 1e-6 && Math.abs(ult.lng - esperado[1]) < 1e-6)
      ok(`la última posición coincide (${ult.lat}, ${ult.lng})`);
    else fail(`la posición recibida no coincide: ${JSON.stringify(ult)} vs ${esperado}`);
  } else {
    fail('EL CLIENTE NO RECIBIÓ NINGUNA POSICIÓN GPS POR WS (el tiempo real no llega)');
  }

  // 9) Confirmar entrega con el QR
  info('9) Domiciliario confirma entrega con el código QR');
  const ent = await api(`/api/pedidos/${pedido.id}/confirmar-entrega`, { method: 'POST', token: domi.token, body: { codigo } });
  if (ent.json?.data?.estado === 'entregado') ok('pedido entregado'); else fail('no se confirmó la entrega: ' + JSON.stringify(ent.json));

  // 10) Cliente ve el pedido entregado
  info('10) Cliente verifica el estado final');
  const fin = await api(`/api/pedidos/${pedido.id}`, { token: cliente.token });
  if (fin.json?.data?.estado === 'entregado') ok('el cliente ve "entregado"'); else fail('el cliente no ve entregado: ' + JSON.stringify(fin.json));

  canal.ws.close();
  if (canalRest) canalRest.ws.close();
  console.log('\n=== ' + (process.exitCode ? '\x1b[31mHUBO FALLOS (ver arriba)\x1b[0m' : '\x1b[32mTODO EL FLUJO PASÓ\x1b[0m') + ' ===\n');
  setTimeout(() => process.exit(), 300);
})().catch(e => { fail('excepción no controlada: ' + (e.stack || e.message)); setTimeout(() => process.exit(1), 300); });
