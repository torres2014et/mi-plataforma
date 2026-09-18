<?php

namespace App\Http\Controllers\Restaurante;

use App\Http\Controllers\Controller;
use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    private function restaurante(): Restaurante
    {
        return Restaurante::firstOrCreate(
            ['user_id' => auth()->id()],
            ['nombre' => auth()->user()->name, 'activo' => true]
        );
    }

    public function edit()
    {
        $restaurante = $this->restaurante();
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        return view('restaurante.configuracion', compact('restaurante', 'dias'));
    }

    public function update(Request $request)
    {
        $restaurante = $this->restaurante();

        $data = $request->validate([
            'nombre'           => 'required|string|max:255',
            'descripcion'      => 'nullable|string|max:1000',
            'telefono'         => 'nullable|string|max:20',
            'direccion'        => 'nullable|string|max:255',
            'activo'           => 'boolean',
            'lat'              => 'nullable|numeric|between:-90,90',
            'lng'              => 'nullable|numeric|between:-180,180',
            'costo_domicilio'  => 'nullable|numeric|min:0|max:99999',
            'imagen'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'horarios'         => 'nullable|array',
            'horarios.*.abierto'  => 'boolean',
            'horarios.*.apertura' => 'nullable|string|max:5',
            'horarios.*.cierre'   => 'nullable|string|max:5',
        ]);

        $data['activo'] = $request->boolean('activo', false);

        if ($request->hasFile('imagen')) {
            if ($restaurante->imagen) {
                Storage::disk('public')->delete($restaurante->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('restaurantes', 'public');
        } else {
            unset($data['imagen']);
        }

        $restaurante->update($data);

        return back()->with('success', 'Configuración guardada correctamente.');
    }
}
