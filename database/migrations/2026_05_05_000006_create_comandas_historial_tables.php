<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comandas_historial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comanda_id');
            $table->string('nombre')->nullable();
            $table->unsignedInteger('mesa_numero');
            $table->string('estado')->default('abierta');
            $table->timestamp('cobrada_en');
            $table->timestamps();
        });

        Schema::create('productos_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comanda_historial_id')->constrained('comandas_historial')->cascadeOnDelete();
            $table->string('nombre');
            $table->unsignedInteger('cantidad')->default(1);
            $table->string('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos_historial');
        Schema::dropIfExists('comandas_historial');
    }
};
