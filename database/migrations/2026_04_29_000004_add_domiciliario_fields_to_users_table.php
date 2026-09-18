<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cedula', 20)->nullable()->after('direccion');
            $table->string('tipo_vehiculo', 50)->nullable()->after('cedula');
            $table->string('placa_vehiculo', 10)->nullable()->after('tipo_vehiculo');
            $table->string('nombre_negocio')->nullable()->after('placa_vehiculo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cedula', 'tipo_vehiculo', 'placa_vehiculo', 'nombre_negocio']);
        });
    }
};
