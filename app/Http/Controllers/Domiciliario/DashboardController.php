<?php

namespace App\Http\Controllers\Domiciliario;

use App\Events\DomiciliarioAcepto;
use App\Events\DomiciliarioUbicacion;
use App\Events\PedidoActualizado;
use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\FcmSender;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $hoy  = today();

        $pedidoActivo = Pedido::where('domiciliario_id', $user->id)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->with(['restaurante', 'cliente', 'items'])
            ->first();

        $entregasHoy = Pedido::where('domiciliario_id', $user->id)
            ->whereDate('updated_at', $hoy)
            ->where('estado', 'entregado')
            ->count();

        $gananciasHoy = Pedido::where('domiciliario_id', $user->id)
            ->whereDate('updated_at', $hoy)
            ->where('estado', 'entregado')
            ->sum('total');

        $historial = Pedido::where('domiciliario_id', $user->id)
            ->whereIn('estado', ['entregado', 'cancelado'])
            ->with('restaurante')
            ->latest()
            ->limit(10)
            ->get();

        // Pedidos sin domiciliario y todavía activos: el domiciliario puede
        // autoaceptarlos (mismo criterio que la app móvil).
        $disponibles = Pedido::whereNull('domiciliario_id')
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->with(['restaurante', 'items'])
            ->latest()
            ->get();

        return view('domiciliario.dashboard', compact(
            'pedidoActivo', 'entregasHoy', 'gananciasHoy', 'historial', 'disponibles'
        ));
    }

    /** El domiciliario se autoasigna un pedido disponible (con su medio). */
    public function aceptar(Request $request, Pedido $pedido)
    {
        abort_unless($pedido->domiciliario_id === null, 422, 'Este pedido ya fue tomado.');
        abort_if(in_array($pedido->estado, ['entregado', 'cancelado']), 422, 'El pedido ya no está activo.');

        $data = $request->validate([
            'medio_transporte' => ['nullable', 'string', 'in:moto,bici,auto,a_pie'],
        ]);

        $pedido->update([
            'domiciliario_id'  => auth()->id(),
            'medio_transporte' => $data['medio_transporte'] ?? $pedido->medio_transporte ?? 'moto',
        ]);

        // El aviso al restaurante ("va a recoger") se dispara cuando el domi
        // pulse "Voy a recoger" (ver `recoger`), no al aceptar.

        return back()->with('success', "Aceptaste el pedido #{$pedido->id}.");
    }

    /**
     * El domiciliario (web) sale a recoger: registra el momento y avisa EN VIVO
     * al restaurante. No cambia el estado del pedido. Mismo evento que la app.
     */
    public function recoger(Request $request, Pedido $pedido)
    {
        abort_unless($pedido->domiciliario_id === auth()->id(), 403);
        abort_if(in_array($pedido->estado, ['en_camino', 'entregado', 'cancelado']), 422,
            'El pedido ya no está en fase de recogida.');

        if (is_null($pedido->recogiendo_at)) {
            $pedido->update(['recogiendo_at' => now()]);
            $pedido->load('domiciliario');
            broadcast(new DomiciliarioAcepto($pedido));
            broadcast(new PedidoActualizado($pedido))->toOthers();
        }

        return back()->with('success', "Vas en camino a recoger el pedido #{$pedido->id}.");
    }

    /**
     * Confirma la entrega con el código del QR del cliente (o tipeado a mano).
     * Mismo criterio que la API: comparación case-insensitive con trim.
     */
    public function confirmarEntrega(Request $request, Pedido $pedido)
    {
        abort_unless($pedido->domiciliario_id === auth()->id(), 403);
        abort_unless($pedido->estado === 'en_camino', 422, 'El pedido no está en camino.');

        $data = $request->validate([
            'codigo' => ['required', 'string'],
        ]);

        $ok = strcasecmp(trim($data['codigo']), (string) $pedido->codigo_confirmacion) === 0;
        if (! $ok) {
            return back()->with('error', 'El código no coincide. Revisa el QR del cliente.');
        }

        $pedido->update(['estado' => 'entregado']);
        broadcast(new PedidoActualizado($pedido))->toOthers();

        return back()->with('success', "¡Entrega del pedido #{$pedido->id} confirmada!");
    }

    /**
     * Fase 3 — GPS real: el domiciliario (web) emite su posición para el pedido
     * en camino. Difunde el mismo evento `DomiciliarioUbicacion` que la app, así
     * un cliente —en app o en web— lo ve moverse en tiempo real. No se guarda.
     */
    public function ubicacion(Request $request, Pedido $pedido)
    {
        abort_unless($pedido->domiciliario_id === auth()->id(), 403);
        abort_unless($pedido->estado === 'en_camino', 422, 'El pedido no está en camino.');

        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        broadcast(new DomiciliarioUbicacion($pedido, (float) $data['lat'], (float) $data['lng']));

        // Push real al cliente: "tu pedido está por llegar" si quedó a ≤250 m.
        app(FcmSender::class)->avisarProximidad($pedido, (float) $data['lat'], (float) $data['lng']);

        return response()->json(['ok' => true]);
    }

    public function toggleDisponibilidad()
    {
        $user = auth()->user();
        $user->update(['disponible' => !$user->disponible]);

        $msg = $user->disponible ? 'Ahora estás disponible para recibir pedidos.' : 'Marcado como no disponible.';

        return back()->with('success', $msg);
    }
}
