<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// ShouldBroadcastNow: emite en el acto (sin depender de un worker de cola),
// así el tiempo real funciona aunque no esté corriendo `queue:listen`.
class PedidoActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Pedido $pedido) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("pedido.{$this->pedido->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'estado.actualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'estado'      => $this->pedido->estado,
            'estadoLabel' => $this->pedido->estadoLabel(),
            'badgeClass'  => $this->pedido->estadoBadgeClass(),
        ];
    }
}
