<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->boolean('ilimitado')->default(false)->after('cantidad');
        });
    }

    public function down(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->dropColumn('ilimitado');
        });
    }
};
