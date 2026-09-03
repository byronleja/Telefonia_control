<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bloques', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);                         // Bloque A, Ejecutivo, etc.
            $table->string('descripcion', 255)->nullable();
            $table->enum('gama', ['alta', 'media', 'basica']);    // gama del dispositivo
            $table->decimal('costo_mensual_linea', 10, 2)->default(0); // costo mensual con linea
            $table->string('operadora', 80)->nullable();           // Claro, Tigo, etc.
            $table->string('color_etiqueta', 7)->default('#2563eb'); // color hex para UI
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Agregar bloque_id a dispositivos
        Schema::table('dispositivos', function (Blueprint $table) {
            $table->foreignId('bloque_id')->nullable()->after('id')->constrained('bloques')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('dispositivos', function (Blueprint $table) {
            $table->dropForeign(['bloque_id']);
            $table->dropColumn('bloque_id');
        });
        Schema::dropIfExists('bloques');
    }
};