<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login por token (Sanctum). Devuelve { token, user }.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        $token = $user->createToken($data['device_name'] ?? 'app-movil')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ]);
    }

    /**
     * Registro desde la app. Solo roles de calle: cliente / domiciliario.
     */
    public function register(Request $request)
    {
        $role = $request->input('rol', 'cliente');

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'telefono' => ['nullable', 'digits:10'],
            'direccion'=> ['nullable', 'string', 'max:255'],
            'rol'      => ['required', 'in:cliente,domiciliario'],
        ];

        if ($role === 'domiciliario') {
            // La app móvil no recoge estos datos en el registro; quedan
            // opcionales (el domiciliario los completa luego en su perfil/web).
            $rules['cedula']         = ['nullable', 'string', 'max:20'];
            $rules['tipo_vehiculo']  = ['nullable', 'string', 'max:50'];
            $rules['placa_vehiculo'] = ['nullable', 'string', 'max:10'];
        }

        $request->validate($rules);

        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'telefono'       => $request->telefono,
            'direccion'      => $request->direccion,
            'cedula'         => $request->cedula,
            'tipo_vehiculo'  => $request->tipo_vehiculo,
            'placa_vehiculo' => $request->placa_vehiculo,
        ]);

        $user->assignRole($role);

        $token = $user->createToken('app-movil')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ], 201);
    }

    /**
     * Datos del usuario autenticado.
     */
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Cierra la sesión: revoca el token actual.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    /**
     * Fase 3, Paso 3 — registra/actualiza el token FCM del dispositivo del
     * usuario autenticado, para poder enviarle push (salió / por llegar).
     */
    public function guardarFcmToken(Request $request)
    {
        $data = $request->validate([
            'fcm_token' => ['required', 'string', 'max:255'],
            'platform'  => ['nullable', 'string', 'in:android,ios,web'],
        ]);

        \App\Models\DeviceToken::updateOrCreate(
            ['token' => $data['fcm_token']],
            ['user_id' => $request->user()->id, 'platform' => $data['platform'] ?? 'android'],
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Borra el token FCM (al cerrar sesión), para no seguir enviando push a un
     * dispositivo deslogueado. Si se manda `fcm_token`, borra solo ese; si no,
     * borra todos los del usuario.
     */
    public function eliminarFcmToken(Request $request)
    {
        $token = $request->input('fcm_token');
        if ($token) {
            \App\Models\DeviceToken::where('token', $token)->delete();
        } else {
            $request->user()->deviceTokens()->delete();
        }

        return response()->json(['ok' => true]);
    }
}
