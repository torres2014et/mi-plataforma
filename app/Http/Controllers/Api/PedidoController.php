<?php

namespace App\Http\Controllers\Api;

use App\Events\DomiciliarioAcepto;
use App\Events\DomiciliarioUbicacion;
use App\Events\PedidoActualizado;
use App\Http\Controllers\Controller;
use App\Http\Resources\PedidoResource;
use App\Models\Calificacion;
use App\Models\Pedido;
use App\Models\Restaurante;
use App\Services\FcmSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    // ───────────────────────── Cliente ─────────────────────────

    /** Pedidos del cliente autenticado (historial + activos). */
    public function index(Request $request)
    {
        $pedidos = Pedido::where('cliente_id', $request->user()->id)
            ->with(['restaurante', 'items', 'calificacion'])
            ->latest()
            ->get();

        return PedidoResource::collection($pedidos);
    }

    /** Detalle de un pedido (cliente dueño o domiciliario asignado). */
    public function show(Request $request, Pedido $pedido)
    {
        $user = $request->user();
        abort_unless(
            $pedido->cliente_id === $user->id || $pedido->domiciliario_id === $user->id,
            403
        );

        $pedido->load(['restaurante', 'items', 'calificacion']);

        return new PedidoResource($pedido);
    }

    /** Crea un pedido (checkout desde la app). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'restaurante_id'           => ['required', 'integer', 'exists:restaurantes,id'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.producto_id'      => ['required', 'integer', 'exists:productos,id'],
            'items.*.nombre_producto'  => ['required', 'string'],
            'items.*.precio_unitario'  => ['required', 'numeric', 'min:0'],
            'items.*.cantidad'         => ['required', 'integer', 'min:1'],
            'costo_domicilio'          => ['nullable', 'numeric', 'min:0'],
            'direccion_entrega'        => ['required', 'string', 'max:255'],
            'lat_entrega'              => ['nullable', 'numeric'],
            'lng_entrega'              => ['nullable', 'numeric'],
            'medio_transporte'         => ['nullable', 'string', 'max:20'],
            'notas'                    => ['nullable', 'string', 'max:500'],
        ]);

        $restaurante = Restaurante::findOrFail($data['restaurante_id']);
        abort_unless($restaurante->activo, 422, 'El restaurante no está disponible.');

        $subtotal       = collect($data['items'])->sum(fn ($i) => $i['precio_unitario'] * $i['cantidad']);
        $costoDomicilio = $restaurante->costo_domicilio ?? ($data['costo_domicilio'] ?? 0);
        $total          = $subtotal + $costoDomicilio;

        $pedido = DB::transaction(function () use ($data, $request, $restaurante, $total, $costoDomicilio) {
            $pedido = Pedido::create([
                'cliente_id'          => $request->user()->id,
                'restaurante_id'      => $restaurante->id,
                'estado'              => 'pendiente',
                'total'               => $total,
                'costo_domicilio'     => $costoDomicilio,
                'direccion_entrega'   => $data['direccion_entrega'],
                'lat_entrega'         => $data['lat_entrega'] ?? null,
                'lng_entrega'         => $data['lng_entrega'] ?? null,
                'medio_transporte'    => $data['medio_transporte'] ?? 'moto',
                'codigo_confirmacion' => $this->generarCodigo(),
                'notas'               => $data['notas'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $pedido->items()->create([
                    'producto_id'     => $item['producto_id'],
                    'nombre_producto' => $item['nombre_producto'],
                    'precio_unitario' => $item['precio_unitario'],
                    'cantidad'        => $item['cantidad'],
                    'subtotal'        => $item['precio_unitario'] * $item['cantidad'],
                ]);
            }

            return $pedido;
        });

        $pedido->load(['restaurante', 'items', 'calificacion']);

        return (new PedidoResource($pedido))->response()->setStatusCode(201);
    }

    /** El cliente califica un pedido entregado. */
    public function calificar(Request $request, Pedido $pedido)
    {
        abort_unless($pedido->cliente_id === $request->user()->id, 403);
        abort_unless($pedido->estado === 'entregado', 422, 'Solo puedes calificar pedidos entregados.');
        abort_if($pedido->calificacion()->exists(), 422, 'Este pedido ya fue calificado.');

        $data = $request->validate([
            'estrellas'   => ['required', 'integer', 'min:1', 'max:5'],
            'comentario'  => ['nullable', 'string', 'max:500'],
        ]);

        $calificacion = Calificacion::create([
            'pedido_id'      => $pedido->id,
            'cliente_id'     => $pedido->cliente_id,
            'restaurante_id' => $pedido->restaurante_id,
            'estrellas'      => $data['estrellas'],
            'comentario'     => $data['comentario'] ?? null,
        ]);

        return response()->json($calificacion, 201);
    }

    // ────────────────────── Domiciliario ───────────────────────

    /** Pedidos que el domiciliario atiende ahora (asignados a él y activos). */
    public function activos(Request $request)
    {
        $pedidos = Pedido::where('domiciliario_id', $request->user()->id)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->with(['restaurante', 'items', 'calificacion'])
            ->latest()
            ->get();

        return PedidoResource::collection($pedidos);
    }

    /** Pedidos sin domiciliario y activos: disponibles para aceptar. */
    public function disponibles(Request $request)
    {
        $pedidos = Pedido::whereNull('domiciliario_id')
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->with(['restaurante', 'items', 'calificacion'])
            ->latest()
            ->get();

        return PedidoResource::collection($pedidos);
    }

    /** El domiciliario acepta un pedido disponible. */
    public function aceptar(Request $request, Pedido $pedido)
    {
        abort_unless($pedido->domiciliario_id === null, 422, 'Este pedido ya fue tomado.');
        abort_if(in_array($pedido->estado, ['entregado', 'cancelado']), 422, 'El pedido ya no está activo.');

        $data = $request->validate([
            'medio_transporte' => ['nullable', 'string', 'max:20'],
        ]);

        $pedido->update([
            'domiciliario_id'  => $request->user()->id,
            'medio_transporte' => $data['medio_transporte'] ?? $pedido->medio_transporte ?? 'moto',
        ]);

        $pedido->load(['restaurante', 'items', 'calificacion', 'domiciliario']);

        broadcast(new PedidoActualizado($pedido));
        // El restaurante se entera de que el domiciliario va a recoger cuando
        // este pulse "Voy a recoger" (ver `recoger`), no al aceptar.

        return new PedidoResource($pedido);
    }

    /**
     * El domiciliario asignado marca que **sale a recoger** el pedido al
     * restaurante. No cambia el estado del pedido; solo registra el momento y
     * **avisa EN VIVO al restaurante** que va en camino a recoger.
     */
    public function recoger(Request $request, Pedido $pedido)
    {
        abort_unless($pedido->domiciliario_id === $request->user()->id, 403);
        abort_if(in_array($pedido->estado, ['en_camino', 'entregado', 'cancelado']), 422,
            'El pedido ya no está en fase de recogida.');

        if (is_null($pedido->recogiendo_at)) {
            $pedido->update(['recogiendo_at' => now()]);
            $pedido->load('domiciliario');
            broadcast(new DomiciliarioAcepto($pedido));   // banner del restaurante
            broadcast(new PedidoActualizado($pedido));    // refresca app/web del domi
        }

        $pedido->load(['restaurante', 'items', 'calificacion']);

        return new PedidoResource($pedido);
    }

    /** Historial de entregas del domiciliario. */
    public function historial(Request $request)
    {
        $pedidos = Pedido::where('domiciliario_id', $request->user()->id)
            ->whereIn('estado', ['entregado', 'cancelado'])
            ->with(['restaurante', 'items', 'calificacion'])
            ->latest()
            ->get();

        return PedidoResource::collection($pedidos);
    }

    /** Avanza el estado del pedido (respetando las transiciones válidas). */
    public function actualizarEstado(Request $request, Pedido $pedido)
    {
        $user = $request->user();
        abort_unless($pedido->domiciliario_id === $user->id, 403);

        $data = $request->validate([
            'estado' => ['required', 'string'],
        ]);

        $validas = Pedido::transicionesValidas()[$pedido->estado] ?? [];
        abort_unless(in_array($data['estado'], $validas), 422, "Transición no válida desde «{$pedido->estado}».");

        $pedido->update(['estado' => $data['estado']]);
        broadcast(new PedidoActualizado($pedido));

        // Push real al cliente: "tu pedido salió" cuando entra a `en_camino`.
        if ($data['estado'] === 'en_camino') {
            app(FcmSender::class)->avisarSalio($pedido);
        }

        $pedido->load(['restaurante', 'items', 'calificacion']);

        return new PedidoResource($pedido);
    }

    /** Confirma la entrega (escaneo del QR): pasa el pedido a entregado. */
    public function confirmarEntrega(Request $request, Pedido $pedido)
    {
        $user = $request->user();
        abort_unless(
            $pedido->domiciliario_id === $user->id || $pedido->cliente_id === $user->id,
            403
        );
        abort_unless($pedido->estado === 'en_camino', 422, 'El pedido no está en camino.');

        // El domiciliario manda el código del QR; el cliente puede cerrar sin él.
        if ($request->filled('codigo')) {
            $ok = strcasecmp(trim($request->input('codigo')), (string) $pedido->codigo_confirmacion) === 0;
            abort_unless($ok, 422, 'El código no coincide.');
        }

        $pedido->update(['estado' => 'entregado']);
        broadcast(new PedidoActualizado($pedido));
        $pedido->load(['restaurante', 'items', 'calificacion']);

        return new PedidoResource($pedido);
    }

    /**
     * Fase 3 — el domiciliario emite su posición GPS para un pedido en camino.
     * Solo el repartidor asignado puede emitir, y solo mientras va en camino. El
     * backend la difunde por websocket (canal privado del pedido); no se guarda.
     */
    public function ubicacion(Request $request, Pedido $pedido)
    {
        abort_unless($pedido->domiciliario_id === $request->user()->id, 403);
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

    /** Código legible de 6 caracteres (sin 0/O/1/I/L) para el QR. */
    private function generarCodigo(): string
    {
        $alfabeto = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        return collect(range(1, 6))
            ->map(fn () => $alfabeto[random_int(0, strlen($alfabeto) - 1)])
            ->implode('');
    }
}
