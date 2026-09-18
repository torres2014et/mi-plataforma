<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// Fase 3 — avisa AL RESTAURANTE (en vivo) que un domiciliario aceptó el pedido
// y va en camino a recogerlo. Va por el canal privado del restaurante (el mismo
// del banner de "nuevo pedido"), no por el del pedido. ShouldBroadcastNow: emite
// en el acto, sin depender de un worker de cola.
class DomiciliarioAcepto implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Pedido $pedido) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("restaurante.{$this->pedido->restaurante_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'domiciliario.acepto';
    }

    public function broadcastWith(): array
    {
        return [
            'pedido_id'    => $this->pedido->id,
            'domiciliario' => optional($this->pedido->domiciliario)->name ?? 'Un domiciliario',
            'medio'        => $this->pedido->medio_transporte ?? 'moto',
        ];
    }
}
