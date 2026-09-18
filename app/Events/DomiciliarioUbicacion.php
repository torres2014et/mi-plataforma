<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// Fase 3 — GPS real del domiciliario. El repartidor emite su posición y el
// backend la difunde en vivo por el canal privado del pedido a quien lo sigue
// (cliente). ShouldBroadcastNow: emite en el acto, sin worker de cola.
class DomiciliarioUbicacion implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Pedido $pedido,
        public float $lat,
        public float $lng,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("pedido.{$this->pedido->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ubicacion';
    }

    public function broadcastWith(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }
}
