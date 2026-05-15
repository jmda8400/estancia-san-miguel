<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('comandas_historial', function (Blueprint $table) {
            if (!Schema::hasColumn('comandas_historial', 'medio_pago')) {
                $table->string('medio_pago')->default('efectivo')->after('estado');
            }
        });
        Schema::table('cierres_caja', function (Blueprint $table) {
            $table->string('turno')->nullable()->after('comandas_abiertas');
            $table->string('responsable')->nullable()->after('turno');
            $table->decimal('caja_inicial', 12, 2)->default(0)->after('responsable');
            $table->decimal('efectivo_esperado', 12, 2)->default(0)->after('caja_inicial');
            $table->decimal('efectivo_contado', 12, 2)->default(0)->after('efectivo_esperado');
            $table->decimal('diferencia_efectivo', 12, 2)->default(0)->after('efectivo_contado');
            $table->text('observaciones')->nullable()->after('diferencia_efectivo');
        });
    }
    public function down(): void {
        Schema::table('comandas_historial', function (Blueprint $table) {
            if (Schema::hasColumn('comandas_historial', 'medio_pago')) $table->dropColumn('medio_pago');
        });
        Schema::table('cierres_caja', function (Blueprint $table) {
            $table->dropColumn(['turno','responsable','caja_inicial','efectivo_esperado','efectivo_contado','diferencia_efectivo','observaciones']);
        });
    }
};
