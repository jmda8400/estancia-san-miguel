<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeria_imagenes', function (Blueprint $table) {
            $table->id();
            $table->string('titulo')->nullable();
            $table->string('categoria', 40)->nullable();
            $table->string('ruta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeria_imagenes');
    }
};
