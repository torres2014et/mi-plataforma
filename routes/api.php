<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\RestauranteController;
use App\Http\Controllers\ChatbotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API de la app móvil (Flutter) — Fase 2
|--------------------------------------------------------------------------
| Autenticación por token con Laravel Sanctum. Todas las rutas devuelven JSON
| (API Resources). Las rutas protegidas requieren el header:
|   Authorization: Bearer <token>
*/

// ── Públicas ──────────────────────────────────────────────

// Señal de descubrimiento: la app escanea la red Wi-Fi y reconoce a SU backend
// por esta respuesta. Liviano, sin auth, para poder probarlo en cada host.
Route::get('/ping', fn () => response()->json([
    'app' => 'domicilios-ubate',
    'ok'  => true,
]));

Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// ── Protegidas (token Sanctum) ────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Fase 3, Paso 3 — token FCM del dispositivo (push reales).
    Route::post('/me/fcm-token',   [AuthController::class, 'guardarFcmToken']);
    Route::delete('/me/fcm-token', [AuthController::class, 'eliminarFcmToken']);

    // Auth de canales privados para la app (Fase 3): el cliente de sockets pide
    // aquí la firma del canal `pedido.{id}` usando su token Sanctum. Las reglas
    // de autorización viven en routes/channels.php.
    Route::post('/broadcasting/auth', fn (Request $request) => Broadcast::auth($request));

    // Chatbot IA (Gemini) — mismo controller que la web (routes/web.php); aquí
    // protegido con el token Sanctum de la app en vez de sesión.
    Route::post('/chatbot/mensaje', [ChatbotController::class, 'enviarMensaje'])
        ->middleware('throttle:20,1');

    // Restaurantes y menú
    Route::get('/restaurantes',                 [RestauranteController::class, 'index']);
    Route::get('/restaurantes/{restaurante}',   [RestauranteController::class, 'show']);
    Route::get('/restaurantes/{restaurante}/productos', [RestauranteController::class, 'productos']);

    // Pedidos — cliente
    Route::get('/pedidos',                   [PedidoController::class, 'index']);
    Route::post('/pedidos',                  [PedidoController::class, 'store']);
    Route::get('/pedidos/{pedido}',          [PedidoController::class, 'show']);
    Route::post('/pedidos/{pedido}/calificar', [PedidoController::class, 'calificar']);

    // Pedidos — domiciliario
    Route::get('/domiciliario/pedidos/activos',     [PedidoController::class, 'activos']);
    Route::get('/domiciliario/pedidos/disponibles', [PedidoController::class, 'disponibles']);
    Route::get('/domiciliario/pedidos/historial',   [PedidoController::class, 'historial']);
    Route::post('/pedidos/{pedido}/aceptar',        [PedidoController::class, 'aceptar']);
    // Fase 3 — el domiciliario sale a recoger (avisa al restaurante en vivo).
    Route::post('/pedidos/{pedido}/recoger',        [PedidoController::class, 'recoger']);
    Route::patch('/pedidos/{pedido}/estado',        [PedidoController::class, 'actualizarEstado']);
    Route::post('/pedidos/{pedido}/confirmar-entrega', [PedidoController::class, 'confirmarEntrega']);
    // Fase 3 — GPS real: el domiciliario asignado emite su posición en camino.
    Route::post('/pedidos/{pedido}/ubicacion',      [PedidoController::class, 'ubicacion']);
});
