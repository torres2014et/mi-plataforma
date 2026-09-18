<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Restaurante;
use App\Events\NuevoPedidoRecibido;
use App\Notifications\NuevoPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarritoController extends Controller
{
    private const SESSION_KEY = 'carrito';

    private function getCarrito(): array
    {
        return session(self::SESSION_KEY, []);
    }

    private function saveCarrito(array $carrito): void
    {
        session([self::SESSION_KEY => $carrito]);
    }

    private function clearCarrito(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function show()
    {
        $carrito     = $this->getCarrito();
        $restaurante = null;

        if (!empty($carrito['restaurante_id'])) {
            $restaurante = Restaurante::find($carrito['restaurante_id']);
        }

        $items           = $carrito['items'] ?? [];
        $subtotal        = collect($items)->sum(fn($i) => $i['precio'] * $i['cantidad']);
        $costo_domicilio = $restaurante?->costo_domicilio ?? 0;
        $total           = $subtotal + $costo_domicilio;
        $direccion       = auth()->user()->direccion ?? '';

        return view('cliente.carrito.show', compact('items', 'restaurante', 'subtotal', 'costo_domicilio', 'total', 'direccion'));
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
            'cantidad'    => 'nullable|integer|min:1|max:20',
        ]);

        $cantidad = (int) ($request->input('cantidad', 1));
        $producto = Producto::findOrFail($request->producto_id);

        abort_unless($producto->disponible, 422);
        abort_unless($producto->restaurante->activo, 422);

        $carrito = $this->getCarrito();

        if (
            !empty($carrito['restaurante_id']) &&
            $carrito['restaurante_id'] !== $producto->restaurante_id &&
            !$request->boolean('confirmar')
        ) {
            return response()->json([
                'conflicto'   => true,
                'restaurante' => $producto->restaurante->nombre,
            ], 409);
        }

        if (!empty($carrito['restaurante_id']) && $carrito['restaurante_id'] !== $producto->restaurante_id) {
            $carrito = [];
        }

        if (empty($carrito)) {
            $carrito = [
                'restaurante_id' => $producto->restaurante_id,
                'items'          => [],
            ];
        }

        $items = $carrito['items'];
        $idx   = collect($items)->search(fn($i) => $i['producto_id'] === $producto->id);

        if ($idx !== false) {
            $items[$idx]['cantidad'] += $cantidad;
        } else {
            $items[] = [
                'producto_id' => $producto->id,
                'nombre'      => $producto->nombre,
                'precio'      => (float) $producto->precio,
                'cantidad'    => $cantidad,
                'imagen'      => $producto->imagen,
            ];
        }

        $carrito['items'] = $items;
        $this->saveCarrito($carrito);

        $totalItems = collect($items)->sum('cantidad');

        return response()->json([
            'ok'          => true,
            'total_items' => $totalItems,
            'message'     => "«{$producto->nombre}» agregado",
        ]);
    }

    public function actualizar(Request $request, int $productoId)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:0|max:20',
        ]);

        $carrito = $this->getCarrito();
        $items   = $carrito['items'] ?? [];
        $idx     = collect($items)->search(fn($i) => $i['producto_id'] === $productoId);

        abort_if($idx === false, 404);

        if ((int) $request->cantidad === 0) {
            array_splice($items, $idx, 1);
        } else {
            $items[$idx]['cantidad'] = (int) $request->cantidad;
        }

        if (empty($items)) {
            $this->clearCarrito();
        } else {
            $carrito['items'] = $items;
            $this->saveCarrito($carrito);
        }

        return back()->with('success', 'Carrito actualizado.');
    }

    public function eliminar(int $productoId)
    {
        $carrito = $this->getCarrito();
        $items   = array_values(array_filter(
            $carrito['items'] ?? [],
            fn($i) => $i['producto_id'] !== $productoId
        ));

        if (empty($items)) {
            $this->clearCarrito();
        } else {
            $carrito['items'] = $items;
            $this->saveCarrito($carrito);
        }

        return back()->with('success', 'Producto eliminado del carrito.');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'direccion_entrega' => 'required|string|max:255',
            'lat_entrega'       => 'nullable|numeric|between:-90,90',
            'lng_entrega'       => 'nullable|numeric|between:-180,180',
            'notas'             => 'nullable|string|max:500',
        ]);

        $carrito = $this->getCarrito();

        abort_if(empty($carrito['items']), 422);

        $restaurante = Restaurante::findOrFail($carrito['restaurante_id']);
        abort_unless($restaurante->activo, 422);

        $subtotal        = collect($carrito['items'])->sum(fn($i) => $i['precio'] * $i['cantidad']);
        $costo_domicilio = $restaurante->costo_domicilio ?? 0;
        $total           = $subtotal + $costo_domicilio;

        $pedido = DB::transaction(function () use ($carrito, $request, $restaurante, $total, $costo_domicilio) {
            $pedido = Pedido::create([
                'cliente_id'        => auth()->id(),
                'restaurante_id'    => $restaurante->id,
                'estado'            => 'pendiente',
                'total'             => $total,
                'costo_domicilio'   => $costo_domicilio,
                'direccion_entrega' => $request->direccion_entrega,
                'lat_entrega'       => $request->lat_entrega,
                'lng_entrega'       => $request->lng_entrega,
                'notas'             => $request->notas,
            ]);

            foreach ($carrito['items'] as $item) {
                $pedido->items()->create([
                    'producto_id'     => $item['producto_id'],
                    'nombre_producto' => $item['nombre'],
                    'precio_unitario' => $item['precio'],
                    'cantidad'        => $item['cantidad'],
                    'subtotal'        => $item['precio'] * $item['cantidad'],
                ]);
            }

            return $pedido;
        });

        $this->clearCarrito();

        // Notificar al dueño del restaurante
        $pedido->load(['items', 'cliente']);
        $restaurante->user->notify(new NuevoPedido($pedido));
        broadcast(new NuevoPedidoRecibido($pedido));

        return redirect()
            ->route('cliente.pedidos.show', $pedido)
            ->with('success', '¡Pedido realizado! El restaurante lo confirmará pronto.');
    }
}
