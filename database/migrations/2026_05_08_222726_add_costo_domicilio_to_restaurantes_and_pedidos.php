<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->decimal('costo_domicilio', 10, 2)->default(0)->after('activo');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->decimal('costo_domicilio', 10, 2)->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->dropColumn('costo_domicilio');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('costo_domicilio');
        });
    }
};
