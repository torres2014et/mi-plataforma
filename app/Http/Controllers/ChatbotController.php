<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function enviarMensaje(Request $request, GeminiService $gemini)
    {
        $data = $request->validate([
            'mensaje'         => ['required', 'string', 'max:500'],
            'restaurante_id'  => ['nullable', 'integer', 'exists:restaurantes,id'],
            // Memoria de la conversación (opcional; la app móvil puede no enviarla).
            'historial'         => ['nullable', 'array', 'max:10'],
            'historial.*.rol'   => ['required_with:historial', 'in:user,bot'],
            'historial.*.texto' => ['required_with:historial', 'string', 'max:1000'],
        ]);

        $respuesta = $gemini->responder(
            $data['mensaje'],
            $data['restaurante_id'] ?? null,
            $request->user(),
            $data['historial'] ?? [],
        );

        return response()->json(['respuesta' => $respuesta]);
    }
}
