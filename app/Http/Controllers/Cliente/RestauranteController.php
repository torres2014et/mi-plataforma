<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Restaurante;

class RestauranteController extends Controller
{
    public function index()
    {
        $restaurantes = Restaurante::where('activo', true)
            ->withCount(['productosDisponibles as total_productos'])
            ->withAvg('calificaciones as promedio_estrellas', 'estrellas')
            ->withCount('calificaciones as total_calificaciones')
            ->get();

        return view('cliente.restaurantes.index', compact('restaurantes'));
    }

    public function show(Restaurante $restaurante)
    {
        abort_unless($restaurante->activo, 404);

        $productos = $restaurante->productosDisponibles()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');

        $calificaciones = $restaurante->calificaciones()
            ->with('cliente:id,name')
            ->latest()
            ->take(5)
            ->get();

        $promedio = $restaurante->getPromedioEstrellas();
        $totalCal = $restaurante->getTotalCalificaciones();

        $carritoSesion = session('carrito', []);
        $carritoCount  = collect($carritoSesion['items'] ?? [])->sum('cantidad');
        $carritoTotal  = collect($carritoSesion['items'] ?? [])->sum(fn($i) => $i['precio'] * $i['cantidad']);

        return view('cliente.restaurantes.show', compact('restaurante', 'productos', 'carritoCount', 'carritoTotal', 'calificaciones', 'promedio', 'totalCal'));
    }
}
