<?php

namespace App\Http\Controllers\Restaurante;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    private function restaurante(): Restaurante
    {
        return Restaurante::firstOrCreate(
            ['user_id' => auth()->id()],
            ['nombre' => auth()->user()->name, 'activo' => true]
        );
    }

    public function index()
    {
        $restaurante = $this->restaurante();
        $productos   = $restaurante->productos()->orderBy('categoria')->orderBy('nombre')->get();
        $categorias  = $restaurante->productos()->distinct()->pluck('categoria')->filter()->sort()->values();

        return view('restaurante.productos.index', compact('restaurante', 'productos', 'categorias'));
    }

    public function create()
    {
        $categorias = $this->restaurante()->productos()->distinct()->pluck('categoria')->filter()->sort()->values();
        return view('restaurante.productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'categoria'   => 'nullable|string|max:100',
            'precio'      => 'required|numeric|min:0',
            'disponible'  => 'boolean',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data['disponible'] = $request->boolean('disponible', true);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        } else {
            unset($data['imagen']);
        }

        $this->restaurante()->productos()->create($data);

        return redirect()->route('restaurante.productos.index')
            ->with('success', 'Producto agregado correctamente.');
    }

    public function edit(Producto $producto)
    {
        $this->authorize_producto($producto);
        $categorias = $this->restaurante()->productos()->distinct()->pluck('categoria')->filter()->sort()->values();
        return view('restaurante.productos.edit', compact('producto', 'categorias'));
    }

    public function update(Request $request, Producto $producto)
    {
        $this->authorize_producto($producto);

        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'categoria'   => 'nullable|string|max:100',
            'precio'      => 'required|numeric|min:0',
            'disponible'  => 'boolean',
            'imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data['disponible'] = $request->boolean('disponible', false);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        } else {
            unset($data['imagen']);
        }

        $producto->update($data);

        return redirect()->route('restaurante.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $this->authorize_producto($producto);

        if ($producto->imagen) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return redirect()->route('restaurante.productos.index')
            ->with('success', 'Producto eliminado.');
    }

    public function toggleDisponible(Producto $producto)
    {
        $this->authorize_producto($producto);
        $producto->update(['disponible' => !$producto->disponible]);

        return back()->with('success', 'Disponibilidad actualizada.');
    }

    private function authorize_producto(Producto $producto): void
    {
        abort_unless($producto->restaurante_id === $this->restaurante()->id, 403);
    }
}
