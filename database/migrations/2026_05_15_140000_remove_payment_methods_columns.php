<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comandas', function (Blueprint $table) {
            if (Schema::hasColumn('comandas', 'medio_pago')) {
                $table->dropColumn('medio_pago');
            }
        });

        Schema::table('comandas_historial', function (Blueprint $table) {
            if (Schema::hasColumn('comandas_historial', 'medio_pago')) {
                $table->dropColumn('medio_pago');
            }
        });
    }

    public function down(): void
    {
        Schema::table('comandas', function (Blueprint $table) {
            if (!Schema::hasColumn('comandas', 'medio_pago')) {
                $table->string('medio_pago')->default('efectivo')->after('cliente_detalle');
            }
        });

        Schema::table('comandas_historial', function (Blueprint $table) {
            if (!Schema::hasColumn('comandas_historial', 'medio_pago')) {
                $table->string('medio_pago')->default('efectivo')->after('estado');
            }
        });
    }
};
