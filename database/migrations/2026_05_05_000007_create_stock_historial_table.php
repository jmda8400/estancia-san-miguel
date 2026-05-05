<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->nullable()->constrained('stock')->nullOnDelete();
            $table->string('producto');
            $table->string('unidad', 30);
            $table->enum('accion', ['suma', 'resta', 'agregado', 'quitado']);
            $table->integer('cantidad')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_historial');
    }
};
