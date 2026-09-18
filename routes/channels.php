<?php

use App\Models\Pedido;
use App\Models\Restaurante;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('pedido.{pedidoId}', function ($user, $pedidoId) {
    $pedido = Pedido::find($pedidoId);

    if (!$pedido) {
        return false;
    }

    return $pedido->cliente_id === $user->id
        || optional($pedido->restaurante)->user_id === $user->id
        || $pedido->domiciliario_id === $user->id;
});

Broadcast::channel('restaurante.{restauranteId}', function ($user, $restauranteId) {
    $restaurante = Restaurante::find($restauranteId);

    return $restaurante && $restaurante->user_id === $user->id;
});
