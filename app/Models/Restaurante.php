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

    private const DIAS = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];

    /**
     * Estado del horario en un instante dado (por defecto, ahora en Bogotá).
     * Soporta cierre pasada la medianoche (ej. 18:00–02:00). Sin horarios
     * configurados devuelve `horario_registrado = false`.
     */
    public function estadoHorario(?\Carbon\Carbon $ahora = null): array
    {
        $ahora ??= now('America/Bogota');
        $h = $this->horarios;

        if (empty($h)) {
            return ['horario_registrado' => false, 'abierto_ahora' => $this->activo];
        }

        $hoy = $h[self::DIAS[$ahora->dayOfWeekIso]] ?? null;
        $ayer = $h[self::DIAS[$ahora->copy()->subDay()->dayOfWeekIso]] ?? null;
        $hora = $ahora->format('H:i');

        $abierto = false;
        $cierra = null;

        if (($hoy['abierto'] ?? false) && isset($hoy['apertura'], $hoy['cierre'])) {
            $cruza = $hoy['cierre'] <= $hoy['apertura'];
            if ($hora >= $hoy['apertura'] && ($cruza || $hora < $hoy['cierre'])) {
                $abierto = true;
                $cierra = $hoy['cierre'];
            }
        }
        // Turno de ayer que se extiende después de medianoche.
        if (! $abierto && ($ayer['abierto'] ?? false) && isset($ayer['apertura'], $ayer['cierre'])
            && $ayer['cierre'] <= $ayer['apertura'] && $hora < $ayer['cierre']) {
            $abierto = true;
            $cierra = $ayer['cierre'];
        }

        return [
            'horario_registrado' => true,
            'abierto_ahora'      => $abierto && $this->activo,
            'cierra_a_las'       => $abierto ? $cierra : null,
            'horario_hoy'        => ($hoy['abierto'] ?? false) ? "{$hoy['apertura']} - {$hoy['cierre']}" : 'cerrado hoy',
        ];
    }

    /** Horario semanal compacto: ['lunes' => '08:00-20:00', 'domingo' => 'cerrado']. */
    public function horarioSemanal(): ?array
    {
        if (empty($this->horarios)) {
            return null;
        }

        $semana = [];
        foreach (self::DIAS as $dia) {
            $d = $this->horarios[$dia] ?? null;
            $semana[$dia] = ($d['abierto'] ?? false) ? "{$d['apertura']}-{$d['cierre']}" : 'cerrado';
        }
        return $semana;
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
