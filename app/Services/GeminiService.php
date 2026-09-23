<?php

namespace App\Services;

use App\Models\Restaurante;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
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
    private const MODELO_RESPALDO = 'gemini-flash-lite-latest';

    private const MENSAJE_NO_DISPONIBLE = 'El asistente no está disponible en este momento. Intenta de nuevo más tarde.';

    public function responder(string $mensaje, ?int $restauranteId = null): string
    {
        $apiKey = config('services.gemini.key');
        if (! $apiKey) {
            return self::MENSAJE_NO_DISPONIBLE;
        }

        $systemPrompt = $this->systemPrompt();
        $contexto = $this->construirContexto($restauranteId);

        $instrucciones = $systemPrompt
            ."\n\n## Contexto actual de la plataforma (JSON)\n"
            .json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $respuesta = $this->llamarGemini($apiKey, $instrucciones, $mensaje);

        return $respuesta ?? self::MENSAJE_NO_DISPONIBLE;
    }

    /**
     * Gemini devuelve 503 "high demand" o se queda colgado de forma intermitente,
     * así que se prueba el modelo configurado y, si falla, un modelo de respaldo.
     * Cada modelo se reintenta una vez ante 429/5xx o timeout.
     */
    private function llamarGemini(string $apiKey, string $systemInstruction, string $mensaje): ?string
    {
        $principal = config('services.gemini.model', 'gemini-flash-latest');
        $modelos = array_values(array_unique([$principal, self::MODELO_RESPALDO]));

        foreach ($modelos as $model) {
            $texto = $this->llamarModelo($apiKey, $model, $systemInstruction, $mensaje);
            if ($texto !== null) {
                return $texto;
            }
        }

        return null;
    }

    private function llamarModelo(string $apiKey, string $model, string $systemInstruction, string $mensaje): ?string
    {
        try {
            $res = Http::timeout(12)
                ->withHeaders(['x-goog-api-key' => $apiKey]) // header, no query: la key no queda en logs de errores
                ->retry(2, 500, fn ($e) => $e instanceof ConnectionException
                    || ($e instanceof RequestException && in_array($e->response->status(), [429, 500, 502, 503, 504])), throw: false)
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                    [
                        'system_instruction' => [
                            'parts' => [['text' => $systemInstruction]],
                        ],
                        'contents' => [
                            ['role' => 'user', 'parts' => [['text' => $mensaje]]],
                        ],
                    ]
                );

            if ($res->failed()) {
                Log::warning('Gemini: llamada falló', ['model' => $model, 'status' => $res->status(), 'body' => $res->body()]);
                return null;
            }

            $texto = $res->json('candidates.0.content.parts.0.text');
            return is_string($texto) ? trim($texto) : null;
        } catch (\Throwable $e) {
            Log::warning("Gemini: excepción al llamar ({$model}): ".$e->getMessage());
            return null;
        }
    }

    /**
     * JSON de contexto para el bot. Si se pasa $restauranteId (el cliente está
     * viendo el menú de ese restaurante), se envía su menú completo. Si no, se
     * envía un resumen de los restaurantes activos para preguntas generales.
     */
    private function construirContexto(?int $restauranteId): array
    {
        if ($restauranteId) {
            $restaurante = Restaurante::with('productosDisponibles')
                ->where('activo', true)
                ->find($restauranteId);

            if ($restaurante) {
                return [
                    'restaurantes' => [$this->serializarRestauranteDetallado($restaurante)],
                ];
            }
        }

        $restaurantes = Restaurante::where('activo', true)
            ->withCount('productosDisponibles')
            ->limit(30)
            ->get();

        return [
            'restaurantes' => $restaurantes->map(fn (Restaurante $r) => [
                'id'                 => $r->id,
                'nombre'             => $r->nombre,
                'categoria'          => $r->categoria,
                'descripcion'        => $r->descripcion,
                'costo_domicilio'    => (float) $r->costo_domicilio,
                'promedio_estrellas' => $r->getPromedioEstrellas(),
                'total_productos'    => $r->productos_disponibles_count,
            ])->all(),
        ];
    }

    private function serializarRestauranteDetallado(Restaurante $r): array
    {
        return [
            'id'                 => $r->id,
            'nombre'             => $r->nombre,
            'categoria'          => $r->categoria,
            'descripcion'        => $r->descripcion,
            'costo_domicilio'    => (float) $r->costo_domicilio,
            'promedio_estrellas' => $r->getPromedioEstrellas(),
            'productos'          => $r->productosDisponibles->map(fn ($p) => [
                'id'          => $p->id,
                'nombre'      => $p->nombre,
                'categoria'   => $p->categoria,
                'precio'      => (float) $p->precio,
                'descripcion' => $p->descripcion,
            ])->all(),
        ];
    }

    private function systemPrompt(): string
    {
        $path = storage_path('app/chatbot/system-prompt.md');
        return is_file($path)
            ? file_get_contents($path)
            : 'Eres el asistente de esta plataforma de domicilios. Solo respondes sobre restaurantes, productos y pedidos del sitio.';
    }
}
