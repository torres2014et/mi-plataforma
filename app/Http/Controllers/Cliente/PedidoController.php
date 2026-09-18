<?php

namespace App\Http\Controllers\Cliente;

use App\Events\PedidoActualizado;
use App\Http\Controllers\Controller;
use App\Models\Pedido;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::where('cliente_id', auth()->id())
            ->with(['restaurante', 'items'])
            ->latest()
            ->paginate(10);

        return view('cliente.pedidos.index', compact('pedidos'));
    }

    public function show(Pedido $pedido)
    {
        abort_unless($pedido->cliente_id === auth()->id(), 403);

        $pedido->load(['restaurante', 'items.producto', 'calificacion']);

        return view('cliente.pedidos.show', compact('pedido'));
    }

    /**
     * Respaldo: el cliente cierra el pedido manualmente si el escaneo del QR
     * falla. Solo válido si el pedido está en camino. Avisa por websocket al
     * domiciliario para que su pantalla se actualice.
     */
    public function confirmarEntrega(Pedido $pedido)
    {
        abort_unless($pedido->cliente_id === auth()->id(), 403);
        abort_unless($pedido->estado === 'en_camino', 422, 'El pedido no está en camino.');

        $pedido->update(['estado' => 'entregado']);
        broadcast(new PedidoActualizado($pedido))->toOthers();

        return back()->with('success', '¡Confirmaste que recibiste tu pedido!');
    }
}
