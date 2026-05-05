<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comandas', function (Blueprint $table) {
            $table->unsignedInteger('mesa_numero')->nullable()->after('id');
        });

        $existing = DB::table('comandas')->orderBy('id')->get();
        if ($existing->isEmpty()) {
            for ($i = 1; $i <= 10; $i++) {
                DB::table('comandas')->insert(['mesa_numero' => $i, 'mesa' => "Mesa $i", 'estado' => 'abierta', 'created_at' => now(), 'updated_at' => now()]);
            }
        } else {
            foreach ($existing as $idx => $comanda) {
                DB::table('comandas')->where('id', $comanda->id)->update(['mesa_numero' => $idx + 1]);
            }
        }

        Schema::table('comandas', function (Blueprint $table) {
            $table->unique('mesa_numero');
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comanda_id')->constrained('comandas')->cascadeOnDelete();
            $table->string('nombre');
            $table->unsignedInteger('cantidad')->default(1);
            $table->string('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
        Schema::table('comandas', function (Blueprint $table) {
            $table->dropUnique(['mesa_numero']);
            $table->dropColumn('mesa_numero');
        });
    }
};
