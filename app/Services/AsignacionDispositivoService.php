<?php
namespace App\Services;
use App\Models\{Dispositivo,Empleado};
class AsignacionDispositivoService {
    public const RAZONES_EXCEPCION = ['ciclo_vencido'=>'Ciclo de renovacion vencido','danio_fisico'=>'Dano fisico del equipo anterior','danio_irreparable'=>'Dano irreparable del equipo anterior','robo_extravio'=>'Robo o extravio reportado','falla_tecnica'=>'Falla tecnica irreparable'];
    public const ESTADO_ANTERIOR_POR_RAZON = ['ciclo_vencido'=>'disponible','danio_fisico'=>'dado_de_baja','danio_irreparable'=>'dado_de_baja','robo_extravio'=>'dado_de_baja','falla_tecnica'=>'dado_de_baja'];
    public function validarAsignacion(Empleado $empleado, Dispositivo $dispositivoNuevo, ?string $razonExcepcion=null): ?string {
        $actual=$this->dispositivoActivoDelEmpleado($empleado);
        if (!$actual) return null;
        if (empty($razonExcepcion)) return "El empleado {$empleado->nombre_completo} ya tiene asignado el dispositivo {$actual->nombre_completo} (S/N: {$actual->numero_serie}). Debe seleccionar una razon de excepcion.";
        if (!array_key_exists($razonExcepcion,self::RAZONES_EXCEPCION)) return "La razon de excepcion seleccionada no es valida.";
        if ($razonExcepcion==='ciclo_vencido' && !$actual->cicloVencido()) return "El ciclo del dispositivo actual aun no ha vencido. Quedan {$actual->meses_para_renovacion} mes(es).";
        return null;
    }
    public function procesarDispositivoAnterior(Empleado $empleado, string $razonExcepcion): void {
        $actual=$this->dispositivoActivoDelEmpleado($empleado);
        if (!$actual) return;
        $actual->update(['estado'=>self::ESTADO_ANTERIOR_POR_RAZON[$razonExcepcion]??'disponible','empleado_id'=>null]);
    }
    public function dispositivoActivoDelEmpleado(Empleado $empleado): ?Dispositivo {
        return Dispositivo::where('empleado_id',$empleado->id)->where('estado','asignado')->first();
    }
}