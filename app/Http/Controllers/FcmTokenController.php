<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;

/**
 * Registra/borra el token FCM **web** (Service Worker del navegador) del usuario
 * autenticado por sesión, para los push reales de la web (Fase 3, Paso 3).
 * La app móvil usa su equivalente en `Api\AuthController` (token Sanctum).
 */
class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            ['user_id' => $request->user()->id, 'platform' => 'web'],
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        $token = $request->input('token');
        if ($token) {
            DeviceToken::where('token', $token)->delete();
        }

        return response()->json(['ok' => true]);
    }
}
