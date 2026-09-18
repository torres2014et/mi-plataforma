<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $path = $request->path();

        // Redirigir /dashboard al dashboard del rol correcto
        if ($path === 'dashboard') {
            if ($user->hasRole('restaurante'))  return redirect()->route('restaurante.dashboard');
            if ($user->hasRole('domiciliario')) return redirect()->route('domiciliario.dashboard');
            if ($user->hasRole('admin'))        return redirect()->route('admin.dashboard');
            if ($user->hasRole('cliente'))      return redirect()->route('cliente.dashboard');
        }

        // Evitar que un rol acceda a la sección de otro rol
        $rolePrefix = [
            'cliente'      => 'cliente/',
            'restaurante'  => 'restaurante/',
            'domiciliario' => 'domiciliario/',
            'admin'        => 'admin/',
        ];

        foreach ($rolePrefix as $rol => $prefix) {
            if (str_starts_with($path, $prefix) && ! $user->hasRole($rol)) {
                // Redirigir a su propio dashboard
                if ($user->hasRole('restaurante'))  return redirect()->route('restaurante.dashboard');
                if ($user->hasRole('domiciliario')) return redirect()->route('domiciliario.dashboard');
                if ($user->hasRole('admin'))        return redirect()->route('admin.dashboard');
                return redirect()->route('cliente.dashboard');
            }
        }

        return $next($request);
    }
}
