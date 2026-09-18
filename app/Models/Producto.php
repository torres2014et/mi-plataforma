<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'restaurante_id', 'nombre', 'descripcion', 'categoria', 'precio', 'imagen', 'disponible',
    ];

    protected $casts = [
        'precio'     => 'decimal:2',
        'disponible' => 'boolean',
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

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class);
    }
}
