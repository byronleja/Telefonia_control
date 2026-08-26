<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('dispositivos', function(Blueprint $t) {
            $t->id(); $t->string('numero_serie')->unique(); $t->string('marca'); $t->string('modelo');
            $t->string('imei')->nullable()->unique(); $t->string('numero_telefonico')->nullable();
            $t->string('operadora')->nullable();
            $t->enum('tipo',['smartphone','basico','tablet'])->default('smartphone');
            $t->enum('estado',['disponible','asignado','en_reparacion','dado_de_baja'])->default('disponible');
            $t->date('fecha_compra')->nullable(); $t->date('fecha_asignacion')->nullable();
            $t->unsignedSmallInteger('meses_renovacion')->default(18);
            $t->decimal('costo',10,2)->nullable(); $t->string('color')->nullable();
            $t->text('observaciones')->nullable();
            $t->foreignId('empleado_id')->nullable()->constrained('empleados')->nullOnDelete();
            $t->timestamps(); $t->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('dispositivos'); }
};