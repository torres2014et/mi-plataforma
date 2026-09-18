<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Notifications\Notification;

class NuevoPedido extends Notification
{
    public function __construct(private Pedido $pedido) {}

    public function via(): array
    {
        return ['database'];
    }

    public function toDatabase(): array
    {
        return [
            'pedido_id' => $this->pedido->id,
            'cliente'   => $this->pedido->cliente->name,
            'total'     => $this->pedido->total,
            'items'     => $this->pedido->items->count(),
        ];
    }
}
