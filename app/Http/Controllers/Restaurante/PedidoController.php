<?php

namespace App\Http\Controllers\Restaurante;

use App\Events\PedidoActualizado;
use App\Http\Controllers\Controller;
use App\Mail\PedidoEstadoActualizado;
use App\Models\Pedido;
use App\Models\Restaurante;
use App\Models\User;
use App\Notifications\PedidoAsignado;
use App\Services\FcmSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PedidoController extends Controller
{
    private function restaurante(): Restaurante
    {
        return Restaurante::firstOrCreate(
            ['user_id' => auth()->id()],
            ['nombre' => auth()->user()->name, 'activo' => true]
        );
    }

    public function index()
    {
        $restaurante = $this->restaurante();

        auth()->user()->unreadNotifications()
            ->where('type', \App\Notifications\NuevoPedido::class)
            ->update(['read_at' => now()]);

        $pedidosActivos = Pedido::where('restaurante_id', $restaurante->id)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->with(['cliente', 'items', 'domiciliario'])
            ->latest()
            ->get();

        $pedidosHistorial = Pedido::where('restaurante_id', $restaurante->id)
            ->whereIn('estado', ['entregado', 'cancelado'])
            ->with(['cliente', 'items'])
            ->latest()
            ->paginate(15);

        $domiciliariosDisponibles = User::role('domiciliario')
            ->where('disponible', true)
            ->get(['id', 'name']);

        return view('restaurante.pedidos.index', compact(
            'restaurante', 'pedidosActivos', 'pedidosHistorial', 'domiciliariosDisponibles'
        ));
    }

    public function updateEstado(Request $request, Pedido $pedido)
    {
        abort_unless($pedido->restaurante_id === $this->restaurante()->id, 403);

        $request->validate([
            'estado' => 'required|string|in:confirmado,en_preparacion,en_camino,entregado,cancelado',
        ]);

        $nuevoEstado  = $request->estado;
        $transiciones = Pedido::transicionesValidas()[$pedido->estado] ?? [];

        abort_unless(in_array($nuevoEstado, $transiciones), 422);

        $pedido->update(['estado' => $nuevoEstado]);

        broadcast(new PedidoActualizado($pedido))->toOthers();

        // Push real al cliente: "tu pedido salió" cuando el restaurante lo
        // despacha (en_camino). Mismo aviso que dispara la app del domiciliario.
        if ($nuevoEstado === 'en_camino') {
            app(FcmSender::class)->avisarSalio($pedido);
        }

        // Email al cliente para estados relevantes
        if (in_array($nuevoEstado, ['confirmado', 'entregado', 'cancelado'])) {
            $pedido->load(['cliente', 'restaurante', 'items']);
            Mail::to($pedido->cliente->email)->queue(new PedidoEstadoActualizado($pedido));
        }

        return back()->with('success', "Estado actualizado a «{$pedido->estadoLabel()}».");
    }

    public function asignarDomiciliario(Request $request, Pedido $pedido)
    {
        abort_unless($pedido->restaurante_id === $this->restaurante()->id, 403);
        abort_unless($pedido->estado === 'en_preparacion', 422);

        $request->validate([
            'domiciliario_id' => 'required|exists:users,id',
        ]);

        $domiciliario = User::findOrFail($request->domiciliario_id);
        abort_unless($domiciliario->hasRole('domiciliario'), 422);

        $pedido->update([
            'domiciliario_id' => $domiciliario->id,
            'estado'          => 'en_camino',
        ]);

        broadcast(new PedidoActualizado($pedido))->toOthers();

        $pedido->load(['restaurante', 'cliente', 'items']);
        $domiciliario->notify(new PedidoAsignado($pedido));

        // Email al cliente: su pedido está en camino
        Mail::to($pedido->cliente->email)->queue(new PedidoEstadoActualizado($pedido));

        return back()->with('success', "Pedido asignado a {$domiciliario->name} · en camino.");
    }
}
