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
        ]);

        $respuesta = $gemini->responder($data['mensaje'], $data['restaurante_id'] ?? null);

        return response()->json(['respuesta' => $respuesta]);
    }
}
