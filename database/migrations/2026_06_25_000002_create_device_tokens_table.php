<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Fase 3 (Paso 3) — tokens FCM por dispositivo. Un usuario puede tener varios
// (su teléfono con la app + el navegador con la web). Sustituye a la columna
// única `users.fcm_token`, que solo guardaba un dispositivo a la vez.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 255)->unique();
            $table->string('platform', 20)->default('android'); // android | ios | web
            $table->timestamps();
        });

        // Migrar los tokens que ya estaban en users.fcm_token (no se pierden).
        foreach (DB::table('users')->whereNotNull('fcm_token')->get(['id', 'fcm_token']) as $u) {
            DB::table('device_tokens')->insertOrIgnore([
                'user_id'    => $u->id,
                'token'      => $u->fcm_token,
                'platform'   => 'android',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
