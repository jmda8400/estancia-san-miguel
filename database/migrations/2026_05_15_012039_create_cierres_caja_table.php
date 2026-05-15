<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_cobrado', 12, 2)->default(0);
            $table->unsignedInteger('comandas_cobradas')->default(0);
            $table->unsignedInteger('productos_cobrados')->default(0);
            $table->unsignedInteger('comandas_abiertas')->default(0);
            $table->json('detalle')->nullable();
            $table->string('comprobante_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierres_caja');
    }
};

