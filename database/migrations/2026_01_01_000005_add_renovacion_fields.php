<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nuevos campos en renovaciones
        Schema::table('renovaciones', function (Blueprint $table) {
            // Qué pasó con el equipo anterior
            if (!Schema::hasColumn('renovaciones', 'destino_equipo_anterior')) {
                $table->enum('destino_equipo_anterior', [
                    'devuelto',
                    'comprado',
                    'no_aplica',
                ])->default('no_aplica')->after('usuario_entrega');
            }

            if (!Schema::hasColumn('renovaciones', 'precio_compra_empleado')) {
                $table->decimal('precio_compra_empleado', 10, 2)
                    ->nullable()
                    ->after('destino_equipo_anterior')
                    ->comment('Precio si el empleado compró el equipo anterior');
            }

            if (!Schema::hasColumn('renovaciones', 'notas_destino_equipo')) {
                $table->text('notas_destino_equipo')
                    ->nullable()
                    ->after('precio_compra_empleado');
            }
        });

        // Fecha de asignación en dispositivos para calcular renovación a 18 meses
        Schema::table('dispositivos', function (Blueprint $table) {
            if (!Schema::hasColumn('dispositivos', 'fecha_asignacion')) {
                $table->date('fecha_asignacion')
                    ->nullable()
                    ->after('fecha_compra')
                    ->comment('Fecha en que se asignó al empleado. Renovación aplica a los 18 meses.');
            }
        });
    }

    public function down(): void
    {
        Schema::table('renovaciones', function (Blueprint $table) {
            if (Schema::hasColumn('renovaciones', 'destino_equipo_anterior')) {
                $table->dropColumn('destino_equipo_anterior');
            }

            if (Schema::hasColumn('renovaciones', 'precio_compra_empleado')) {
                $table->dropColumn('precio_compra_empleado');
            }

            if (Schema::hasColumn('renovaciones', 'notas_destino_equipo')) {
                $table->dropColumn('notas_destino_equipo');
            }
        });

        Schema::table('dispositivos', function (Blueprint $table) {
            if (Schema::hasColumn('dispositivos', 'fecha_asignacion')) {
                $table->dropColumn('fecha_asignacion');
            }
        });
    }
};