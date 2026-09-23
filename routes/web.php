<?php

use App\Http\Controllers\Admin\UsuarioController as AdminUsuarioController;
use App\Http\Controllers\Cliente\CalificacionController;
use App\Http\Controllers\Cliente\CarritoController;
use App\Http\Controllers\Cliente\PedidoController as ClientePedidoController;
use App\Http\Controllers\Cliente\RestauranteController;
use App\Http\Controllers\Domiciliario\DashboardController as DomiciliarioDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Restaurante\ConfiguracionController;
use App\Http\Controllers\Restaurante\DashboardController as RestauranteDashboardController;
use App\Http\Controllers\Restaurante\PedidoController as RestaurantePedidoController;
use App\Http\Controllers\Restaurante\ProductoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::middleware(['auth', 'role.redirect'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Fase 3 (Paso 3) — token FCM web (push del navegador, cualquier rol).
    Route::post('/fcm-token', [\App\Http\Controllers\FcmTokenController::class, 'store'])->name('fcm-token.store');
    Route::delete('/fcm-token', [\App\Http\Controllers\FcmTokenController::class, 'destroy'])->name('fcm-token.destroy');

    // Chatbot IA (Gemini 2.5 Flash) — disponible para cualquier rol autenticado.
    Route::post('/chatbot/mensaje', [\App\Http\Controllers\ChatbotController::class, 'enviarMensaje'])
        ->middleware('throttle:20,1')
        ->name('chatbot.mensaje');

    // ── CLIENTE ──────────────────────────────────────────────
    Route::middleware('role:cliente')->prefix('cliente')->name('cliente.')->group(function () {
        Route::get('/dashboard', function () {
            $totalRestaurantes = \App\Models\Restaurante::where('activo', true)->count();
            $pedidoActivo = \App\Models\Pedido::where('cliente_id', auth()->id())
                ->whereNotIn('estado', ['entregado', 'cancelado'])
                ->with('restaurante')
                ->latest()
                ->first();
            return view('cliente.dashboard', compact('totalRestaurantes', 'pedidoActivo'));
        })->name('dashboard');
        Route::get('/restaurantes', [RestauranteController::class, 'index'])->name('restaurantes.index');
        Route::get('/restaurantes/{restaurante}', [RestauranteController::class, 'show'])->name('restaurantes.show');

        Route::get('/carrito', [CarritoController::class, 'show'])->name('carrito.show');
        Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
        Route::post('/carrito/checkout', [CarritoController::class, 'checkout'])->name('carrito.checkout');
        Route::patch('/carrito/{productoId}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
        Route::delete('/carrito/{productoId}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');

        Route::get('/pedidos', [ClientePedidoController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/{pedido}', [ClientePedidoController::class, 'show'])->name('pedidos.show');
        Route::post('/pedidos/{pedido}/calificar', [CalificacionController::class, 'store'])->name('pedidos.calificar');
        Route::post('/pedidos/{pedido}/confirmar', [ClientePedidoController::class, 'confirmarEntrega'])->name('pedidos.confirmar');
    });

    // ── RESTAURANTE ───────────────────────────────────────────
    Route::middleware('role:restaurante')->prefix('restaurante')->name('restaurante.')->group(function () {
        Route::get('/dashboard', [RestauranteDashboardController::class, 'index'])->name('dashboard');
        Route::resource('productos', ProductoController::class)->except(['show']);
        Route::patch('/productos/{producto}/toggle', [ProductoController::class, 'toggleDisponible'])->name('productos.toggle');
        Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('configuracion');
        Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');

        Route::get('/pedidos', [RestaurantePedidoController::class, 'index'])->name('pedidos.index');
        Route::patch('/pedidos/{pedido}/estado', [RestaurantePedidoController::class, 'updateEstado'])->name('pedidos.updateEstado');
        Route::patch('/pedidos/{pedido}/asignar', [RestaurantePedidoController::class, 'asignarDomiciliario'])->name('pedidos.asignar');
    });

    // ── DOMICILIARIO ──────────────────────────────────────────
    Route::middleware('role:domiciliario')->prefix('domiciliario')->name('domiciliario.')->group(function () {
        Route::get('/dashboard', [DomiciliarioDashboardController::class, 'index'])->name('dashboard');
        Route::patch('/disponibilidad', [DomiciliarioDashboardController::class, 'toggleDisponibilidad'])->name('disponibilidad');
        Route::post('/pedidos/{pedido}/aceptar', [DomiciliarioDashboardController::class, 'aceptar'])->name('pedidos.aceptar');
        // Fase 3 — el domiciliario sale a recoger (avisa al restaurante en vivo).
        Route::post('/pedidos/{pedido}/recoger', [DomiciliarioDashboardController::class, 'recoger'])->name('pedidos.recoger');
        Route::post('/pedidos/{pedido}/confirmar', [DomiciliarioDashboardController::class, 'confirmarEntrega'])->name('pedidos.confirmar');
        // Fase 3 — GPS real: el domiciliario web emite su posición en camino.
        Route::post('/pedidos/{pedido}/ubicacion', [DomiciliarioDashboardController::class, 'ubicacion'])->name('pedidos.ubicacion');
    });

    // ── ADMIN ─────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            $hoy   = today();
            $stats = [
                'usuarios'     => \App\Models\User::count(),
                'restaurantes' => \App\Models\Restaurante::where('activo', true)->count(),
                'pedidosHoy'   => \App\Models\Pedido::whereDate('created_at', $hoy)->count(),
                'ingresosHoy'  => \App\Models\Pedido::whereDate('created_at', $hoy)
                                    ->where('estado', 'entregado')->sum('total'),
            ];
            $actividadReciente = \App\Models\Pedido::with(['cliente', 'restaurante'])
                ->latest()->limit(8)->get();
            return view('admin.dashboard', compact('stats', 'actividadReciente'));
        })->name('dashboard');

        Route::get('/usuarios', [AdminUsuarioController::class, 'index'])->name('usuarios.index');
        Route::patch('/restaurantes/{restaurante}/toggle', [AdminUsuarioController::class, 'toggleRestaurante'])->name('restaurantes.toggle');
    });

    // Perfil (todos los roles)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
