<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $calificado = $this->relationLoaded('calificacion')
            ? ! is_null($this->calificacion)
            : $this->calificacion()->exists();

        return [
            'id'                     => $this->id,
            'restaurante_id'         => $this->restaurante_id,
            'restaurante_nombre'     => $this->restaurante?->nombre,
            'restaurante_imagen_url' => $this->restaurante?->imagen,
            'cliente_id'             => $this->cliente_id,
            'domiciliario_id'        => $this->domiciliario_id,
            // Fase 3 — el domiciliario ya salió a recoger (botón "Voy a recoger").
            'recogiendo'             => ! is_null($this->recogiendo_at),
            'medio_transporte'       => $this->medio_transporte ?? 'moto',
            'estado'                 => $this->estado,
            'items'                  => PedidoItemResource::collection($this->whenLoaded('items')),
            'subtotal'               => (float) $this->total - (float) $this->costo_domicilio,
            'costo_domicilio'        => (float) $this->costo_domicilio,
            'total'                  => (float) $this->total,
            'direccion_entrega'      => $this->direccion_entrega,
            'lat_entrega'            => $this->lat_entrega,
            'lng_entrega'            => $this->lng_entrega,
            'created_at'             => $this->created_at?->toIso8601String(),
            'calificado'             => $calificado,
            'codigo_confirmacion'    => $this->codigo_confirmacion,
        ];
    }
}
