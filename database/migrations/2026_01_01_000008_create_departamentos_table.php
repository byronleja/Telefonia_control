<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema,DB};
return new class extends Migration {
    public function up(): void {
        Schema::create('departamentos', function(Blueprint $t) {
            $t->id(); $t->string('nombre')->unique();
            $t->string('descripcion')->nullable();
            $t->boolean('activo')->default(true);
            $t->timestamps();
        });
        // Migrar departamentos existentes de empleados
        DB::statement("INSERT IGNORE INTO departamentos (nombre, created_at, updated_at)
            SELECT DISTINCT departamento, NOW(), NOW() FROM empleados
            WHERE departamento IS NOT NULL AND departamento != ''");
    }
    public function down(): void { Schema::dropIfExists('departamentos'); }
};