<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Restaurante;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $role = $request->input('role', 'cliente');

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'telefono' => ['nullable', 'digits:10'],
            'direccion'=> ['nullable', 'string', 'max:255'],
            'role'     => ['required', 'in:cliente,domiciliario,vendedor'],
        ];

        if ($role === 'domiciliario') {
            $rules['cedula']         = ['required', 'string', 'max:20'];
            $rules['tipo_vehiculo']  = ['required', 'string', 'max:50'];
            $rules['placa_vehiculo'] = ['nullable', 'string', 'max:10'];
        }

        if ($role === 'vendedor') {
            $rules['nombre_negocio'] = ['required', 'string', 'max:255'];
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
            'nombre_negocio' => $request->nombre_negocio,
        ]);

        $spatieRole = $role === 'vendedor' ? 'restaurante' : $role;
        $user->assignRole($spatieRole);

        // Crear perfil del restaurante al registrarse como vendedor
        if ($role === 'vendedor') {
            Restaurante::create([
                'user_id'  => $user->id,
                'nombre'   => $request->nombre_negocio,
                'activo'   => true,
            ]);
        }

        event(new Registered($user));
        Auth::login($user);

        return match($spatieRole) {
            'domiciliario' => redirect()->route('domiciliario.dashboard'),
            'restaurante'  => redirect()->route('restaurante.dashboard'),
            default        => redirect()->route('cliente.dashboard'),
        };
    }
}
