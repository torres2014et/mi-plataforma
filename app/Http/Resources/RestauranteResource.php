<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestauranteResource extends JsonResource
{
    // Centro de Ubaté: respaldo cuando el restaurante aún no tiene coordenadas.
    private const UBATE_LAT = 5.3085900;
    private const UBATE_LNG = -73.8143000;

    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'nombre'                 => $this->nombre,
            'descripcion'            => $this->descripcion ?? '',
            'categoria'              => $this->categoria ?? 'General',
            'imagen_url'             => $this->fotoUrl(),
            'rating'                 => round((float) ($this->promedio_estrellas ?? $this->getPromedioEstrellas() ?? 0), 1),
            'tiempo_entrega_min'     => (int) ($this->tiempo_entrega_min ?? 30),
            'tiempo_preparacion_min' => (int) ($this->tiempo_preparacion_min ?? 15),
            'costo_domicilio'        => (float) ($this->costo_domicilio ?? 0),
            'abierto'                => (bool) $this->activo,
            'direccion'              => $this->direccion ?? '',
            'lat'                    => $this->lat ?? self::UBATE_LAT,
            'lng'                    => $this->lng ?? self::UBATE_LNG,
            'productos'              => ProductoResource::collection($this->whenLoaded('productos')),
        ];
    }
}
