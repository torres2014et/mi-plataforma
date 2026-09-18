<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'cliente_id',
        'restaurante_id',
        'domiciliario_id',
        'recogiendo_at',
        'estado',
        'total',
        'costo_domicilio',
        'direccion_entrega',
        'lat_entrega',
        'lng_entrega',
        'codigo_confirmacion',
        'medio_transporte',
        'notas',
    ];

    protected $casts = [
        'total'           => 'decimal:2',
        'costo_domicilio' => 'decimal:2',
        'lat_entrega'     => 'float',
        'lng_entrega'     => 'float',
        'recogiendo_at'   => 'datetime',
    ];

    // ── Relaciones ─────────────────────────────────────────

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class);
    }

    public function domiciliario()
    {
        return $this->belongsTo(User::class, 'domiciliario_id');
    }

    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function calificacion()
    {
        return $this->hasOne(Calificacion::class);
    }

    // ── Helpers de instancia ───────────────────────────────

    public function estadoLabel(): string
    {
        return match($this->estado) {
            'pendiente'      => 'Pendiente',
            'confirmado'     => 'Confirmado',
            'en_preparacion' => 'En preparación',
            'en_camino'      => 'En camino',
            'entregado'      => 'Entregado',
            'cancelado'      => 'Cancelado',
            default          => $this->estado,
        };
    }

    public function estadoBadgeClass(): string
    {
        return match($this->estado) {
            'pendiente'      => 'badge-orange',
            'confirmado'     => 'badge-blue',
            'en_preparacion' => 'badge-orange',
            'en_camino'      => 'badge-blue',
            'entregado'      => 'badge-green',
            'cancelado'      => 'badge-red',
            default          => 'badge-gray',
        };
    }

    public function estaActivo(): bool
    {
        return !in_array($this->estado, ['entregado', 'cancelado']);
    }

    // ── Helpers estáticos ──────────────────────────────────

    public static function transicionesValidas(): array
    {
        return [
            'pendiente'      => ['confirmado', 'cancelado'],
            'confirmado'     => ['en_preparacion', 'cancelado'],
            'en_preparacion' => ['en_camino'],
            'en_camino'      => ['entregado'],
            'entregado'      => [],
            'cancelado'      => [],
        ];
    }

    public static function labelAccion(string $estado): string
    {
        return match($estado) {
            'confirmado'     => 'Confirmar pedido',
            'en_preparacion' => 'Iniciar preparación',
            'en_camino'      => 'Enviar a domicilio',
            'entregado'      => 'Marcar entregado',
            'cancelado'      => 'Cancelar',
            default          => $estado,
        };
    }
}
