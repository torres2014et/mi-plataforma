<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Detrás de un túnel/proxy (ngrok) el visitante entra por HTTPS pero la
        // petición llega a Laravel como HTTP. Confiar en el proxy hace que
        // Laravel respete X-Forwarded-Proto y genere los assets en https (si no,
        // el navegador bloquea el CSS/JS por "mixed content" y la web se ve sin
        // estilos). Por IP local no hay esa cabecera, así que sigue en http.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'role.redirect'      => \App\Http\Middleware\RoleRedirect::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirigir al dashboard correcto cuando Spatie rechaza por rol
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No tienes permisos para esta acción.'], 403);
            }

            if (auth()->check()) {
                $user = auth()->user();
                if ($user->hasRole('restaurante'))  return redirect()->route('restaurante.dashboard');
                if ($user->hasRole('domiciliario')) return redirect()->route('domiciliario.dashboard');
                if ($user->hasRole('admin'))        return redirect()->route('admin.dashboard');
                return redirect()->route('cliente.dashboard');
            }

            return redirect()->route('login');
        });

        // La sesión/token de seguridad expiró (ej. la pestaña quedó abierta
        // desde antes de reiniciar el servidor). En vez de la pantalla técnica
        // "419 Page Expired", volver al login con un aviso claro.
        // La sesión/token de seguridad expiró (ej. la pestaña quedó abierta
        // desde antes de reiniciar el servidor). Laravel convierte
        // TokenMismatchException en un HttpException(419) genérico antes de
        // llegar aquí (ver Handler::prepareException()), así que hay que
        // interceptar por código de estado, no por el tipo original.
        // En vez de la pantalla técnica "419 Page Expired", volver al login
        // con un aviso claro.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tu sesión expiró, recarga la página e intenta de nuevo.'], 419);
            }

            return redirect()->route('login')
                ->with('status', 'Tu sesión había expirado — recargamos la página, intenta de nuevo.');
        });
    })->create();
