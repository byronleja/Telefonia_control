<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('renovaciones', function(Blueprint $t) {
            $t->id(); $t->string('numero_solicitud')->unique();
            $t->foreignId('empleado_id')->constrained('empleados');
            $t->foreignId('dispositivo_actual_id')->nullable()->constrained('dispositivos')->nullOnDelete();
            $t->foreignId('dispositivo_nuevo_id')->nullable()->constrained('dispositivos')->nullOnDelete();
            $t->enum('estado',['pendiente','aprobada','rechazada','entregada'])->default('pendiente');
            $t->date('fecha_solicitud'); $t->date('fecha_aprobacion')->nullable();
            $t->date('fecha_entrega')->nullable(); $t->string('usuario_entrega')->nullable();
            $t->string('motivo'); $t->text('descripcion')->nullable();
            $t->text('observaciones_aprobacion')->nullable(); $t->string('aprobado_por')->nullable();
            $t->enum('destino_equipo_anterior',['devuelto','comprado','no_aplica'])->default('no_aplica');
            $t->decimal('precio_compra_empleado',10,2)->nullable();
            $t->text('notas_destino_equipo')->nullable();
            $t->timestamps(); $t->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('renovaciones'); }
};