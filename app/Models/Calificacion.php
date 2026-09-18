<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificaciones';

    protected $fillable = ['pedido_id', 'cliente_id', 'restaurante_id', 'estrellas', 'comentario'];

    public function pedido()      { return $this->belongsTo(Pedido::class); }
    public function cliente()     { return $this->belongsTo(User::class, 'cliente_id'); }
    public function restaurante() { return $this->belongsTo(Restaurante::class); }
}
