<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Fase 3, Paso 3 — push reales (Firebase Cloud Messaging).
    // `credentials` = ruta al JSON de la cuenta de servicio descargado de la
    // consola de Firebase (Configuración del proyecto → Cuentas de servicio →
    // Generar nueva clave privada). Por defecto se busca en
    // storage/app/firebase/service-account.json. Mientras el archivo no exista,
    // FcmSender no envía nada (falla suave) y todo lo demás sigue igual.
    'fcm' => [
        'credentials' => env('FCM_CREDENTIALS', storage_path('app/firebase/service-account.json')),

        // Push WEB (navegador). Valores PÚBLICOS del SDK web de Firebase + la
        // clave VAPID ("Certificados push web"). Mientras `api_key` esté vacío,
        // la web no intenta registrar push (queda inerte, no rompe nada).
        'web' => [
            'api_key'             => env('FCM_WEB_API_KEY'),
            'auth_domain'         => env('FCM_WEB_AUTH_DOMAIN'),
            'project_id'          => env('FCM_WEB_PROJECT_ID'),
            'storage_bucket'      => env('FCM_WEB_STORAGE_BUCKET'),
            'messaging_sender_id' => env('FCM_WEB_SENDER_ID'),
            'app_id'              => env('FCM_WEB_APP_ID'),
            'vapid_key'           => env('FCM_WEB_VAPID_KEY'),
        ],
    ],

];
