<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->dropColumn('unidad');
        });

        Schema::table('stock_historial', function (Blueprint $table) {
            $table->dropColumn('unidad');
        });
    }

    public function down(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->string('unidad', 20)->default('u');
        });

        Schema::table('stock_historial', function (Blueprint $table) {
            $table->string('unidad', 30)->default('u');
        });
    }
};
