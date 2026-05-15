<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comandas', function (Blueprint $table) {
            $table->string('cliente_documento')->nullable()->after('nombre');
            $table->string('cliente_telefono')->nullable()->after('cliente_documento');
            $table->string('cliente_detalle')->nullable()->after('cliente_telefono');
            $table->string('medio_pago')->default('efectivo')->after('cliente_detalle');
        });
    }

    public function down(): void
    {
        Schema::table('comandas', function (Blueprint $table) {
            $table->dropColumn(['cliente_documento', 'cliente_telefono', 'cliente_detalle', 'medio_pago']);
        });
    }
};
