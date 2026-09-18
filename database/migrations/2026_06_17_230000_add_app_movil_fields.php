<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos que la app móvil (Flutter) necesita y que el backend web no guardaba.
 *
 * - pedidos: punto de entrega (GPS), código de confirmación del QR y medio
 *   de transporte del domiciliario.
 * - restaurantes: coordenadas, categoría y tiempos estimados (cocina/entrega).
 *   Las coordenadas quedan nullable: por ahora son "falsas" (la app cae a un
 *   punto por defecto de Ubaté si vienen null), pero la columna ya está lista.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->decimal('lat_entrega', 10, 7)->nullable()->after('direccion_entrega');
            $table->decimal('lng_entrega', 10, 7)->nullable()->after('lat_entrega');
            $table->string('codigo_confirmacion', 8)->nullable()->after('lng_entrega');
            $table->string('medio_transporte', 20)->default('moto')->after('codigo_confirmacion');
        });

        Schema::table('restaurantes', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('direccion');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->string('categoria')->nullable()->after('descripcion');
            $table->unsignedSmallInteger('tiempo_entrega_min')->default(30)->after('costo_domicilio');
            $table->unsignedSmallInteger('tiempo_preparacion_min')->default(15)->after('tiempo_entrega_min');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['lat_entrega', 'lng_entrega', 'codigo_confirmacion', 'medio_transporte']);
        });

        Schema::table('restaurantes', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'categoria', 'tiempo_entrega_min', 'tiempo_preparacion_min']);
        });
    }
};
