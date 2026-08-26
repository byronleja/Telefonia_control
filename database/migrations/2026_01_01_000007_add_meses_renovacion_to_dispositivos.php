<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Marcar migraciones huérfanas como ejecutadas para limpiar conflictos
        $maxBatch = DB::table('migrations')->max('batch') ?? 1;

        $huerfanas = [
            '2026_01_01_000005_add_renovacion_fields',
            '2026_01_01_000005_create_configuraciones_table',
        ];

        foreach ($huerfanas as $nombre) {
            if (!DB::table('migrations')->where('migration', $nombre)->exists()) {
                DB::table('migrations')->insert([
                    'migration' => $nombre,
                    'batch'     => $maxBatch,
                ]);
            }
        }

        // Agregar meses_renovacion solo si no existe
        if (!Schema::hasColumn('dispositivos', 'meses_renovacion')) {
            Schema::table('dispositivos', function (Blueprint $table) {
                $table->unsignedSmallInteger('meses_renovacion')
                    ->default(18)
                    ->after('fecha_asignacion')
                    ->comment('Meses del ciclo de renovación de este dispositivo');
            });

            if (Schema::hasTable('configuraciones')) {
                $ciclo = (int)(DB::table('configuraciones')
                    ->where('clave', 'ciclo_renovacion_meses')
                    ->value('valor') ?? 18);

                DB::table('dispositivos')->update(['meses_renovacion' => $ciclo]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('dispositivos', 'meses_renovacion')) {
            Schema::table('dispositivos', function (Blueprint $table) {
                $table->dropColumn('meses_renovacion');
            });
        }
    }
};
