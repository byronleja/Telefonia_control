<?php
namespace Database\Seeders;
use App\Models\{Dispositivo,Empleado,Renovacion};
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        $this->call(DepartamentoSeeder::class);
        $emp=$this->empleados(); $disp=$this->dispositivos($emp); $this->renovaciones($emp,$disp);
    }
    private function empleados() {
        $data=[['nombre'=>'Ana','apellido'=>'Garcia','codigo_empleado'=>'EMP-001','departamento'=>'Tecnologia e Informatica','cargo'=>'Analista de Sistemas','email'=>'ana.garcia@empresa.com'],['nombre'=>'Luis','apellido'=>'Perez','codigo_empleado'=>'EMP-002','departamento'=>'Ventas','cargo'=>'Ejecutivo de Ventas','email'=>'luis.perez@empresa.com'],['nombre'=>'Maria','apellido'=>'Lopez','codigo_empleado'=>'EMP-003','departamento'=>'Recursos Humanos','cargo'=>'Coordinadora de RRHH','email'=>'maria.lopez@empresa.com'],['nombre'=>'Carlos','apellido'=>'Morales','codigo_empleado'=>'EMP-004','departamento'=>'Tecnologia e Informatica','cargo'=>'Desarrollador Web','email'=>'carlos.morales@empresa.com'],['nombre'=>'Sofia','apellido'=>'Hernandez','codigo_empleado'=>'EMP-005','departamento'=>'Finanzas y Contabilidad','cargo'=>'Contadora','email'=>'sofia.hernandez@empresa.com']];
        return collect($data)->map(fn($d)=>Empleado::firstOrCreate(['codigo_empleado'=>$d['codigo_empleado']],array_merge($d,['estado'=>'activo','telefono'=>'5555-'.rand(1000,9999)])));
    }
    private function dispositivos($emp) {
        $data=[['numero_serie'=>'SN-001','marca'=>'Samsung','modelo'=>'Galaxy S24','imei'=>'352999001234567','tipo'=>'smartphone','estado'=>'asignado','operadora'=>'Claro','empleado_id'=>$emp[0]->id,'fecha_compra'=>'2024-01-15','fecha_asignacion'=>'2024-01-20','meses_renovacion'=>18,'costo'=>3500.00],['numero_serie'=>'SN-002','marca'=>'Apple','modelo'=>'iPhone 15','imei'=>'352999007654321','tipo'=>'smartphone','estado'=>'asignado','operadora'=>'Tigo','empleado_id'=>$emp[1]->id,'fecha_compra'=>'2024-02-20','fecha_asignacion'=>'2024-02-25','meses_renovacion'=>24,'costo'=>7200.00],['numero_serie'=>'SN-003','marca'=>'Samsung','modelo'=>'Galaxy A54','tipo'=>'smartphone','estado'=>'disponible','meses_renovacion'=>18,'costo'=>2200.00],['numero_serie'=>'SN-004','marca'=>'Motorola','modelo'=>'Edge 40','tipo'=>'smartphone','estado'=>'disponible','meses_renovacion'=>18,'costo'=>2800.00],['numero_serie'=>'SN-005','marca'=>'Xiaomi','modelo'=>'Redmi Note 13','tipo'=>'smartphone','estado'=>'en_reparacion','meses_renovacion'=>18,'costo'=>1800.00]];
        return collect($data)->map(fn($d)=>Dispositivo::firstOrCreate(['numero_serie'=>$d['numero_serie']],$d));
    }
    private function renovaciones($emp,$disp) {
        if(!Renovacion::where('numero_solicitud','like','REN-%')->exists()){
            Renovacion::create(['empleado_id'=>$emp[0]->id,'dispositivo_actual_id'=>$disp[0]->id,'dispositivo_nuevo_id'=>$disp[2]->id,'estado'=>'aprobada','fecha_solicitud'=>now()->subDays(10),'fecha_aprobacion'=>now()->subDays(7),'motivo'=>'Actualizacion tecnologica','aprobado_por'=>'Gerente TI','destino_equipo_anterior'=>'devuelto']);
            Renovacion::create(['empleado_id'=>$emp[1]->id,'dispositivo_actual_id'=>$disp[1]->id,'estado'=>'pendiente','fecha_solicitud'=>now()->subDays(2),'motivo'=>'Dano fisico - pantalla rota','destino_equipo_anterior'=>'no_aplica']);
        }
    }
}