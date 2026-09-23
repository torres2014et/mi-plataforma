<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Restaurante;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Chatbot IA — habla con Gemini 2.5 Flash (Google AI Studio) para responder
 * preguntas de clientes sobre restaurantes/productos de la plataforma.
 *
 * **Falla suave** (mismo patrón que FcmSender): si GEMINI_API_KEY no está
 * configurada o la llamada falla, retorna un mensaje de "no disponible" en
 * vez de romper la página.
 */
class GeminiService
{
    /**
     * Cadena de respaldo. La cuota gratuita de Google es POR MODELO (p. ej. 20
     * peticiones/día en gemini-flash-latest), así que encadenar modelos multiplica
     * la capacidad y evita caer en "no disponible" cuando uno se agota o se satura.
     */
    private const MODELOS_RESPALDO = ['gemini-3.6-flash', 'gemini-3.5-flash-lite', 'gemini-flash-lite-latest'];

    /** Tope de tiempo total por mensaje (PHP suele cortar a los 30 s). */
    private const PRESUPUESTO_SEGUNDOS = 24;

    /** Segundos que se cachea una respuesta idéntica (ahorra cuota). */
    private const CACHE_SEGUNDOS = 120;

    private const MENSAJE_NO_DISPONIBLE = 'El asistente no está disponible en este momento. Intenta de nuevo más tarde.';

    /**
     * @param  array<int, array{rol: string, texto: string}>  $historial  turnos previos (rol: user|bot)
     */
    public function responder(string $mensaje, ?int $restauranteId = null, ?User $usuario = null, array $historial = []): string
    {
        $apiKey = config('services.gemini.key');
        if (! $apiKey) {
            return self::MENSAJE_NO_DISPONIBLE;
        }

        $systemPrompt = $this->systemPrompt();
        $contexto = $this->construirContexto($restauranteId, $usuario);

        $instrucciones = $systemPrompt
            ."\n\n## Contexto actual de la plataforma (JSON)\n"
            .json_encode($contexto, JSON_UNESCAPED_UNICODE);

        // Solo se cachean preguntas sueltas (sin historial), por usuario y restaurante.
        $claveCache = empty($historial)
            ? 'chatbot:'.md5($mensaje.'|'.($restauranteId ?? '').'|'.($usuario?->id ?? '').'|'.now('America/Bogota')->format('YmdHi') )
            : null;

        if ($claveCache && ($guardada = Cache::get($claveCache))) {
            return $guardada;
        }

        $respuesta = $this->llamarGemini($apiKey, $instrucciones, $mensaje, $historial);

        if ($respuesta !== null && $claveCache) {
            Cache::put($claveCache, $respuesta, self::CACHE_SEGUNDOS);
        }

        return $respuesta ?? self::MENSAJE_NO_DISPONIBLE;
    }

    /**
     * Prueba el modelo configurado y luego la cadena de respaldo hasta obtener
     * respuesta o agotar el presupuesto de tiempo. Gemini devuelve 503 "high
     * demand", 429 por cuota o se cuelga de forma intermitente.
     */
    private function llamarGemini(string $apiKey, string $systemInstruction, string $mensaje, array $historial = []): ?string
    {
        $principal = config('services.gemini.model', 'gemini-flash-latest');
        $modelos = array_values(array_unique([$principal, ...self::MODELOS_RESPALDO]));
        $limite = microtime(true) + self::PRESUPUESTO_SEGUNDOS;

        foreach ($modelos as $model) {
            if (microtime(true) >= $limite) {
                break;
            }
            // Modelo con cuota agotada hace poco: no gastar tiempo en él.
            if (Cache::has("gemini:agotado:{$model}")) {
                continue;
            }
            $texto = $this->llamarModelo($apiKey, $model, $systemInstruction, $mensaje, $historial, $limite);
            if ($texto !== null) {
                return $texto;
            }
        }

        return null;
    }

    private function llamarModelo(string $apiKey, string $model, string $systemInstruction, string $mensaje, array $historial, float $limite): ?string
    {
        // Turnos previos (memoria de la conversación) + el mensaje actual.
        $contents = [];
        foreach (array_slice($historial, -8) as $turno) {
            $contents[] = [
                'role'  => ($turno['rol'] ?? '') === 'bot' ? 'model' : 'user',
                'parts' => [['text' => (string) ($turno['texto'] ?? '')]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $mensaje]]];

        $timeout = (int) max(3, min(10, floor($limite - microtime(true))));

        try {
            $res = Http::timeout($timeout)
                ->withHeaders(['x-goog-api-key' => $apiKey]) // header, no query: la key no queda en logs de errores
                // Reintenta solo ante 5xx/timeout. Un 429 es cuota agotada: reintentar no sirve, mejor el siguiente modelo.
                ->retry(2, 400, fn ($e) => $e instanceof ConnectionException
                    || ($e instanceof RequestException && $e->response->status() >= 500), throw: false)
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                    [
                        'system_instruction' => [
                            'parts' => [['text' => $systemInstruction]],
                        ],
                        'contents' => $contents,
                    ]
                );

            if ($res->status() === 429) {
                Cache::put("gemini:agotado:{$model}", true, now()->addMinutes(10));
            }

            if ($res->failed()) {
                Log::warning('Gemini: llamada falló', [
                    'model' => $model, 'status' => $res->status(), 'body' => mb_substr($res->body(), 0, 400),
                ]);
                return null;
            }

            // La respuesta puede venir partida en varias partes (y algunas de "razonamiento"):
            // se unen solo las de texto final, no únicamente la primera.
            $texto = collect($res->json('candidates.0.content.parts', []))
                ->filter(fn ($p) => empty($p['thought']) && isset($p['text']))
                ->pluck('text')
                ->implode('');

            $fin = $res->json('candidates.0.finishReason');
            if ($fin && $fin !== 'STOP') {
                Log::warning('Gemini: respuesta terminó con finishReason distinto de STOP', ['model' => $model, 'finishReason' => $fin]);
            }

            return trim($texto) !== '' ? trim($texto) : null;
        } catch (\Throwable $e) {
            Log::warning("Gemini: excepción al llamar ({$model}): ".$e->getMessage());
            return null;
        }
    }

    /**
     * JSON de contexto para el bot: fecha/hora actual en Bogotá (para "abierto
     * ahora"), los restaurantes activos con horario, datos de contacto y menú
     * compacto, y —si hay usuario— sus pedidos recientes. Si se pasa
     * $restauranteId, ese restaurante va primero y con el menú completo.
     */
    private function construirContexto(?int $restauranteId, ?User $usuario = null): array
    {
        $ahora = now('America/Bogota');

        $restaurantes = Restaurante::where('activo', true)
            ->with('productosDisponibles')
            ->withAvg('calificaciones', 'estrellas')
            ->withCount('calificaciones')
            ->orderBy('nombre')
            ->limit(40)
            ->get();

        $contexto = [
            'ahora' => [
                'fecha'    => $ahora->format('Y-m-d'),
                'dia'      => $ahora->locale('es')->dayName,
                'hora'     => $ahora->format('H:i'),
                'zona'     => 'America/Bogota',
            ],
            'restaurante_que_ve_el_cliente' => $restauranteId,
            'restaurantes' => $restaurantes
                ->sortByDesc(fn (Restaurante $r) => $r->id === $restauranteId)
                ->values()
                ->map(fn (Restaurante $r) => $this->serializarRestaurante($r, $ahora, $r->id === $restauranteId))
                ->all(),
        ];

        if ($usuario) {
            $contexto['cliente'] = [
                'nombre'  => $usuario->name,
                'rol'     => $usuario->getRoleNames()->first(),
                'pedidos' => $this->pedidosRecientes($usuario),
            ];
        }

        return $contexto;
    }

    private function serializarRestaurante(Restaurante $r, \Carbon\Carbon $ahora, bool $detallado): array
    {
        $productos = $r->productosDisponibles;

        // Menú compacto ("Nombre $precio") para no inflar el prompt con 400+ productos.
        $menu = $detallado
            ? $productos->map(fn ($p) => [
                'nombre'      => $p->nombre,
                'categoria'   => $p->categoria,
                'precio'      => (float) $p->precio,
                'descripcion' => $p->descripcion,
            ])->all()
            : $productos->take(25)->map(fn ($p) => "{$p->nombre} $".number_format($p->precio, 0, ',', '.'))->all();

        return [
            'id'                  => $r->id,
            'nombre'              => $r->nombre,
            'categoria'           => $r->categoria,
            'descripcion'         => $r->descripcion,
            'direccion'           => $r->direccion,
            'telefono'            => $r->telefono,
            'costo_domicilio'     => (float) $r->costo_domicilio,
            'tiempo_entrega_min'  => $r->tiempo_entrega_min,
            'promedio_estrellas'  => $r->calificaciones_avg_estrellas ? round($r->calificaciones_avg_estrellas, 1) : null,
            'total_calificaciones' => $r->calificaciones_count,
            'horario'             => $r->estadoHorario($ahora),
            'horario_semanal'     => $r->horarioSemanal(),
            'total_productos'     => $productos->count(),
            $detallado ? 'productos' : 'menu' => $menu,
        ];
    }

    /** Últimos pedidos del usuario (según su rol) con estado legible. */
    private function pedidosRecientes(User $usuario): array
    {
        $columna = match (true) {
            $usuario->hasRole('domiciliario') => 'domiciliario_id',
            $usuario->hasRole('restaurante')  => null,
            default                           => 'cliente_id',
        };

        $query = Pedido::with(['restaurante:id,nombre', 'items'])->latest()->limit(5);

        if ($columna) {
            $query->where($columna, $usuario->id);
        } else {
            $restauranteId = $usuario->restaurante?->id;
            if (! $restauranteId) {
                return [];
            }
            $query->where('restaurante_id', $restauranteId);
        }

        return $query->get()->map(fn (Pedido $p) => [
            'numero'       => $p->id,
            'estado'       => $p->estadoLabel(),
            'restaurante'  => $p->restaurante?->nombre,
            'total'        => (float) $p->total,
            'domicilio'    => (float) $p->costo_domicilio,
            'direccion'    => $p->direccion_entrega,
            'hecho_el'     => $p->created_at?->timezone('America/Bogota')->format('Y-m-d H:i'),
            'productos'    => $p->items->map(fn ($i) => "{$i->cantidad}x {$i->nombre_producto}")->all(),
        ])->all();
    }

    private function systemPrompt(): string
    {
        // Versionado en el repo (storage/app está en .gitignore y no llegaría a producción).
        $path = resource_path('chatbot/system-prompt.md');
        return is_file($path)
            ? file_get_contents($path)
            : 'Eres el asistente de esta plataforma de domicilios. Solo respondes sobre restaurantes, productos y pedidos del sitio.';
    }
}
