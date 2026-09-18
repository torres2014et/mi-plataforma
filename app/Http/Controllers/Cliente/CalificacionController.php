<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\Pedido;
use Illuminate\Http\Request;

class CalificacionController extends Controller
{
    public function store(Request $request, Pedido $pedido)
    {
        abort_unless($pedido->cliente_id === auth()->id(), 403);
        abort_unless($pedido->estado === 'entregado', 422);
        abort_if($pedido->calificacion()->exists(), 422);

        $data = $request->validate([
            'estrellas'  => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        Calificacion::create([
            'pedido_id'      => $pedido->id,
            'cliente_id'     => auth()->id(),
            'restaurante_id' => $pedido->restaurante_id,
            'estrellas'      => $data['estrellas'],
            'comentario'     => $data['comentario'] ?? null,
        ]);

        return back()->with('success', '¡Gracias por calificar! Tu opinión ayuda a otros clientes.');
    }
}
