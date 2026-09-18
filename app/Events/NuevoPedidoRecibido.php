<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoPedidoRecibido implements ShouldBroadcast
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
        return 'pedido.nuevo';
    }

    public function broadcastWith(): array
    {
        return [
            'pedido_id' => $this->pedido->id,
            'cliente'   => $this->pedido->cliente->name,
            'total'     => $this->pedido->total,
        ];
    }
}
