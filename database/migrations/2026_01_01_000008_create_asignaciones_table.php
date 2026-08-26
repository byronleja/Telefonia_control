<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispositivo_id')->constrained('dispositivos')->cascadeOnDelete();
            $table->foreignId('empleado_id')->constrained('empleados');
            $table->date('fecha_asignacion');
            $table->date('fecha_devolucion')->nullable();
            $table->string('razon_asignacion')->nullable();
            $table->string('razon_devolucion')->nullable();
            $table->string('asignado_por')->nullable();
            $table->string('devuelto_por')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
