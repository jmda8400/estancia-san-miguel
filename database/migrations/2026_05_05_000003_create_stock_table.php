<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->string('producto');
            $table->unsignedInteger('cantidad');
            $table->string('unidad', 20);
            $table->timestamps();
        });

        DB::table('stock')->insert([
            ['producto' => 'Harina', 'cantidad' => 25, 'unidad' => 'kg', 'created_at' => now(), 'updated_at' => now()],
            ['producto' => 'Leche', 'cantidad' => 40, 'unidad' => 'litros', 'created_at' => now(), 'updated_at' => now()],
            ['producto' => 'Carbón', 'cantidad' => 18, 'unidad' => 'bolsas', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
