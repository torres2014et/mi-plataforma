<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Fase 3 — marca el momento en que el domiciliario sale a recoger el pedido al
// restaurante (botón "Voy a recoger"). Es lo que dispara el aviso EN VIVO al
// restaurante ("va en camino a recoger"), separado del momento de aceptación.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->timestamp('recogiendo_at')->nullable()->after('domiciliario_id');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('recogiendo_at');
        });
    }
};
