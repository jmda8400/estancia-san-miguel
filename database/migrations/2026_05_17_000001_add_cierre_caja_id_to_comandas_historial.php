<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('comandas_historial', function (Blueprint $table) {
            if (!Schema::hasColumn('comandas_historial', 'cierre_caja_id')) {
                $table->foreignId('cierre_caja_id')->nullable()->after('cobrada_en')->constrained('cierres_caja')->nullOnDelete();
                $table->index('cierre_caja_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('comandas_historial', function (Blueprint $table) {
            if (Schema::hasColumn('comandas_historial', 'cierre_caja_id')) {
                $table->dropForeign(['cierre_caja_id']);
                $table->dropIndex(['cierre_caja_id']);
                $table->dropColumn('cierre_caja_id');
            }
        });
    }
};
