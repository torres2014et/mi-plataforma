<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envía notificaciones push por Firebase Cloud Messaging (API HTTP v1) —
 * Fase 3, Paso 3.
 *
 * **Sin dependencias nuevas de composer:** construye el JWT de la cuenta de
 * servicio con `openssl` y pide el access token OAuth2 a mano (cacheado).
 *
 * **Falla suave:** si todavía no se ha colocado el JSON de la cuenta de
 * servicio (`config('services.fcm.credentials')` → ruta inexistente), todo
 * método retorna sin hacer nada y solo deja un aviso en el log. Así el resto
 * del backend (estados, websockets) sigue funcionando exactamente igual hasta
 * que el usuario agregue las credenciales de Firebase.
 */
class FcmSender
{
    /**
     * Envía un push a TODOS los dispositivos del usuario (app + web). No-op si no
     * tiene tokens o si no hay credenciales de Firebase.
     */
    public function aUsuario(?User $user, string $titulo, string $cuerpo, array $data = []): void
    {
        if (! $user) {
            return;
        }
        $tokens = $user->deviceTokens()->pluck('token')->all();
        // Compatibilidad: si quedara algún token en la columna vieja, también.
        if (empty($tokens) && ! empty($user->fcm_token)) {
            $tokens = [$user->fcm_token];
        }
        foreach (array_unique($tokens) as $token) {
            $this->enviar($token, $titulo, $cuerpo, $data);
        }
    }

    public function enviar(string $token, string $titulo, string $cuerpo, array $data = []): void
    {
        $access = $this->accessToken();
        $projectId = $this->projectId();
        if (! $access || ! $projectId) {
            return; // sin credenciales: push deshabilitado, no se rompe nada
        }

        try {
            $res = Http::withToken($access)->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                [
                    'message' => [
                        'token'        => $token,
                        'notification' => ['title' => $titulo, 'body' => $cuerpo],
                        'android'      => [
                            'priority'     => 'high',
                            'notification' => ['channel_id' => 'pedidos'],
                        ],
                        // FCM exige que todos los valores de `data` sean string.
                        'data' => array_map(fn ($v) => (string) $v, $data),
                    ],
                ]
            );

            if ($res->failed()) {
                $body = $res->body();
                // Token muerto/invalido: lo borramos para no reintentar siempre.
                if ($res->status() === 404 || str_contains($body, 'UNREGISTERED') || str_contains($body, 'INVALID_ARGUMENT')) {
                    \App\Models\DeviceToken::where('token', $token)->delete();
                }
                Log::warning('FCM: envío falló', ['status' => $res->status(), 'body' => $body]);
            }
        } catch (\Throwable $e) {
            Log::warning('FCM: excepción al enviar: '.$e->getMessage());
        }
    }

    // ── Avisos de pedido (se llaman desde la app Y la web, para paridad) ──────

    /** "Tu pedido salió": cuando el pedido entra a `en_camino`. */
    public function avisarSalio(Pedido $pedido): void
    {
        $pedido->loadMissing(['cliente', 'restaurante']);
        $this->aUsuario(
            $pedido->cliente,
            'Tu pedido salió 🛵',
            'Tu pedido de '.optional($pedido->restaurante)->nombre.' ya va en camino.',
            ['pedido_id' => $pedido->id, 'tipo' => 'salio'],
        );
    }

    /**
     * "Tu pedido está por llegar": cuando el domiciliario emite una posición a
     * ≤250 m del destino. Una sola vez por pedido (dedupe en caché).
     */
    public function avisarProximidad(Pedido $pedido, float $lat, float $lng): void
    {
        if (is_null($pedido->lat_entrega) || is_null($pedido->lng_entrega)) {
            return;
        }
        $metros = $this->distanciaMetros($lat, $lng, (float) $pedido->lat_entrega, (float) $pedido->lng_entrega);
        if ($metros > 250) {
            return;
        }
        if (! Cache::add("pedido.{$pedido->id}.por_llegar", true, now()->addHours(2))) {
            return; // ya se avisó
        }
        $pedido->loadMissing(['cliente', 'restaurante']);
        $this->aUsuario(
            $pedido->cliente,
            'Tu pedido está por llegar 📍',
            'El domiciliario de '.optional($pedido->restaurante)->nombre.' está cerca de tu dirección.',
            ['pedido_id' => $pedido->id, 'tipo' => 'por_llegar'],
        );
    }

    /** Distancia en metros entre dos coordenadas (fórmula de Haversine). */
    private function distanciaMetros(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** Ruta al JSON de la cuenta de servicio si existe; si no, null (no-op). */
    private function credentialsPath(): ?string
    {
        $path = config('services.fcm.credentials');
        return ($path && is_file($path)) ? $path : null;
    }

    private function credenciales(): ?array
    {
        $path = $this->credentialsPath();
        if (! $path) {
            return null;
        }
        $json = json_decode(file_get_contents($path), true);
        return is_array($json) ? $json : null;
    }

    private function projectId(): ?string
    {
        return $this->credenciales()['project_id'] ?? null;
    }

    /** Access token OAuth2 a partir de la cuenta de servicio (cacheado ~50 min). */
    private function accessToken(): ?string
    {
        $sa = $this->credenciales();
        if (! $sa) {
            return null;
        }

        return Cache::remember('fcm_access_token', now()->addMinutes(50), function () use ($sa) {
            $now = time();
            $jwt = $this->firmarJwt([
                'iss'   => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ], $sa['private_key']);

            try {
                $res = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ]);
                return $res->ok() ? $res->json('access_token') : null;
            } catch (\Throwable $e) {
                Log::warning('FCM: no se pudo obtener el access token: '.$e->getMessage());
                return null;
            }
        });
    }

    private function firmarJwt(array $claims, string $privateKey): string
    {
        $segmentos = [
            $this->b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])),
            $this->b64url(json_encode($claims)),
        ];

        $firma = '';
        openssl_sign(implode('.', $segmentos), $firma, $privateKey, 'sha256WithRSAEncryption');
        $segmentos[] = $this->b64url($firma);

        return implode('.', $segmentos);
    }

    private function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
