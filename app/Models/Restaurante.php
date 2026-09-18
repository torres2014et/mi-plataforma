<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurante extends Model
{
    protected $fillable = [
        'user_id', 'nombre', 'descripcion', 'categoria', 'direccion', 'lat', 'lng',
        'telefono', 'imagen', 'activo', 'costo_domicilio',
        'tiempo_entrega_min', 'tiempo_preparacion_min',
    ];

    protected $casts = [
        'activo'          => 'boolean',
        'horarios'        => 'array',
        'costo_domicilio' => 'float',
        'lat'             => 'float',
        'lng'             => 'float',
    ];

    /** URL pública de la foto: admite URL absoluta (curada) o archivo en storage. */
    public function fotoUrl(): ?string
    {
        if (!$this->imagen) {
            return null;
        }
        return str_starts_with($this->imagen, 'http')
            ? $this->imagen
            : asset('storage/' . $this->imagen);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function productosDisponibles()
    {
        return $this->hasMany(Producto::class)->where('disponible', true);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }

    public function getPromedioEstrellas(): ?float
    {
        $avg = $this->calificaciones()->avg('estrellas');
        return $avg ? round($avg, 1) : null;
    }

    public function getTotalCalificaciones(): int
    {
        return $this->calificaciones()->count();
    }
}
