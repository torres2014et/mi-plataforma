<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductoResource;
use App\Http\Resources\RestauranteResource;
use App\Models\Restaurante;

class RestauranteController extends Controller
{
    /**
     * Lista de restaurantes activos (sin el menú completo), con rating.
     */
    public function index()
    {
        $restaurantes = Restaurante::where('activo', true)
            ->withAvg('calificaciones as promedio_estrellas', 'estrellas')
            ->get();

        return RestauranteResource::collection($restaurantes);
    }

    /**
     * Restaurante con su menú (productos disponibles).
     */
    public function show(Restaurante $restaurante)
    {
        abort_unless($restaurante->activo, 404);

        $restaurante->loadAvg('calificaciones as promedio_estrellas', 'estrellas');
        $restaurante->setRelation(
            'productos',
            $restaurante->productosDisponibles()->orderBy('categoria')->orderBy('nombre')->get()
        );

        return new RestauranteResource($restaurante);
    }

    /**
     * Solo el menú (productos) de un restaurante.
     */
    public function productos(Restaurante $restaurante)
    {
        $productos = $restaurante->productosDisponibles()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        return ProductoResource::collection($productos);
    }
}
