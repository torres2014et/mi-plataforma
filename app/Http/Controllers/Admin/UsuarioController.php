<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurante;
use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $rol     = $request->get('rol', 'cliente');
        $rolesValidos = ['cliente', 'restaurante', 'domiciliario', 'admin'];

        if (!in_array($rol, $rolesValidos)) {
            $rol = 'cliente';
        }

        $usuarios = User::role($rol)
            ->with('restaurante')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $conteos = [
            'cliente'      => User::role('cliente')->count(),
            'restaurante'  => User::role('restaurante')->count(),
            'domiciliario' => User::role('domiciliario')->count(),
            'admin'        => User::role('admin')->count(),
        ];

        return view('admin.usuarios.index', compact('usuarios', 'rol', 'conteos'));
    }

    public function toggleRestaurante(Restaurante $restaurante)
    {
        $restaurante->update(['activo' => !$restaurante->activo]);
        $estado = $restaurante->activo ? 'activado' : 'desactivado';

        return back()->with('success', "Restaurante «{$restaurante->nombre}» {$estado}.");
    }
}
