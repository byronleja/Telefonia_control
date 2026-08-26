<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema,DB};
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('configuraciones')) {
            Schema::create('configuraciones', function(Blueprint $t) {
                $t->id(); $t->string('clave')->unique(); $t->string('valor');
                $t->string('descripcion')->nullable(); $t->string('tipo')->default('string');
                $t->timestamps();
            });
        }
        $defaults = [
            ['clave'=>'ciclo_renovacion_meses','valor'=>'18','descripcion'=>'Meses ciclo renovacion','tipo'=>'integer'],
            ['clave'=>'alerta_anticipacion_meses','valor'=>'2','descripcion'=>'Anticipacion alertas','tipo'=>'integer'],
            ['clave'=>'nombre_empresa','valor'=>'Gas Zeta, S.A.','descripcion'=>'Empresa','tipo'=>'string'],
            ['clave'=>'departamento_sistemas','valor'=>'Direccion de Sistemas','descripcion'=>'Dept TI','tipo'=>'string'],
        ];
        foreach ($defaults as $d) {
            if (!DB::table('configuraciones')->where('clave',$d['clave'])->exists())
                DB::table('configuraciones')->insert(array_merge($d,['created_at'=>now(),'updated_at'=>now()]));
        }
    }
    public function down(): void { Schema::dropIfExists('configuraciones'); }
};