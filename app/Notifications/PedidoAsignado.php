<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Notifications\Notification;

class PedidoAsignado extends Notification
{
    public function __construct(private Pedido $pedido) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'pedido_id'   => $this->pedido->id,
            'restaurante' => $this->pedido->restaurante->nombre,
            'direccion'   => $this->pedido->direccion_entrega,
            'total'       => $this->pedido->total,
            'cliente'     => $this->pedido->cliente->name,
        ];
    }
}
