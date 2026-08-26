<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('adjuntos', function(Blueprint $t) {
            $t->id();
            $t->foreignId('renovacion_id')->constrained('renovaciones')->cascadeOnDelete();
            $t->string('nombre_original'); $t->string('nombre_archivo'); $t->string('ruta');
            $t->string('tipo_mime'); $t->unsignedBigInteger('tamanio');
            $t->enum('tipo_adjunto',['solicitud','aprobacion','entrega','otro'])->default('otro');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('adjuntos'); }
};