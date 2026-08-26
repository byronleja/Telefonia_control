<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('solicitudes_entrega', function (Blueprint $table) {
            $table->id();
            $table->string('numero_solicitud')->unique();
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->foreignId('dispositivo_id')->nullable()->constrained('dispositivos');          // nuevo — lo elige Sistemas
            $table->foreignId('dispositivo_actual_id')->nullable()->constrained('dispositivos');   // el que pago — lo registra Contabilidad
            $table->foreignId('creado_por')->constrained('users');
            $table->foreignId('atendido_por')->nullable()->constrained('users');
            $table->enum('estado', ['pendiente','entregado','rechazado'])->default('pendiente');
            $table->string('numero_boleta');
            $table->string('ruta_boleta')->nullable();
            $table->string('nombre_boleta')->nullable();
            $table->date('fecha_solicitud');
            $table->date('fecha_entrega')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->timestamps();
        });

        if (Schema::hasColumn('users', 'rol')) {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE users MODIFY COLUMN rol ENUM('admin','visualizador','contabilidad') DEFAULT 'visualizador'"
            );
        }
    }

    public function down(): void {
        Schema::dropIfExists('solicitudes_entrega');
    }
};
