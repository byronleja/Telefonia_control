<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('empleados', function(Blueprint $t) {
            $t->id(); $t->string('nombre'); $t->string('apellido');
            $t->string('codigo_empleado')->unique(); $t->string('departamento');
            $t->string('cargo'); $t->string('email')->unique();
            $t->string('telefono')->nullable();
            $t->enum('estado',['activo','inactivo'])->default('activo');
            $t->timestamps(); $t->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('empleados'); }
};