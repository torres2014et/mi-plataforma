<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'telefono',
        'direccion',
        'cedula',
        'tipo_vehiculo',
        'placa_vehiculo',
        'nombre_negocio',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'fcm_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // ── Relaciones ─────────────────────────────────────────

    /** El restaurante del que este usuario (rol vendedor) es dueño. */
    public function restaurante()
    {
        return $this->hasOne(Restaurante::class);
    }

    /** Pedidos hechos por este usuario como cliente. */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }

    /** Pedidos que este usuario ha entregado como domiciliario. */
    public function entregas()
    {
        return $this->hasMany(Pedido::class, 'domiciliario_id');
    }

    /** Tokens FCM de sus dispositivos (app y/o web) para los push. */
    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }
}