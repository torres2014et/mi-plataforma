<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'restaurante_id' => $this->restaurante_id,
            'nombre'         => $this->nombre,
            'descripcion'    => $this->descripcion,
            'precio'         => (float) $this->precio,
            'categoria'      => $this->categoria ?? 'General',
            'imagen_url'     => $this->fotoUrl(),
            'disponible'     => (bool) $this->disponible,
            // La personalización por opciones aún no existe en el backend web.
            'grupos_opciones' => [],
        ];
    }
}
