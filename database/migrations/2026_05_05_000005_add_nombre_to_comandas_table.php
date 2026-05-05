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
            $table->string('nombre')->nullable()->after('mesa_numero');
        });

        DB::table('comandas')->orderBy('mesa_numero')->get()->each(function ($comanda) {
            DB::table('comandas')->where('id', $comanda->id)->update([
                'nombre' => 'Mesa ' . $comanda->mesa_numero,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('comandas', function (Blueprint $table) {
            $table->dropColumn('nombre');
        });
    }
};
