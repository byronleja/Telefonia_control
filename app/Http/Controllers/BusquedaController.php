<?php
namespace App\Http\Controllers;
use App\Models\{Asignacion,Dispositivo,Empleado,Renovacion};
use Illuminate\Http\Request;
use Illuminate\View\View;
class BusquedaController extends Controller {
    public function index(Request $request): View {
        $query=trim($request->get('q',''));
        $resultados=[];$total=0;
        if(strlen($query)>=2){
            $resultados=['empleados'=>$this->buscarEmpleados($query),'dispositivos'=>$this->buscarDispositivos($query),'asignaciones'=>$this->buscarAsignaciones($query),'renovaciones'=>$this->buscarRenovaciones($query)];
            $total=array_sum(array_map('count',$resultados));
        }
        return view('busqueda.index',compact('query','resultados','total'));
    }
    private function buscarEmpleados(string $q){ return Empleado::where(fn($x)=>$x->where('nombre','like',"%{$q}%")->orWhere('apellido','like',"%{$q}%")->orWhere('codigo_empleado','like',"%{$q}%")->orWhere('email','like',"%{$q}%")->orWhere('departamento','like',"%{$q}%")->orWhere('cargo','like',"%{$q}%"))->limit(8)->get(); }
    private function buscarDispositivos(string $q){ return Dispositivo::with('empleado')->where(fn($x)=>$x->where('numero_serie','like',"%{$q}%")->orWhere('marca','like',"%{$q}%")->orWhere('modelo','like',"%{$q}%")->orWhere('imei','like',"%{$q}%")->orWhere('numero_telefonico','like',"%{$q}%")->orWhere('color','like',"%{$q}%")->orWhere('operadora','like',"%{$q}%"))->limit(8)->get(); }
    private function buscarAsignaciones(string $q){ return Asignacion::with(['empleado','dispositivo'])->whereNull('fecha_devolucion')->where(fn($x)=>$x->whereHas('empleado',fn($e)=>$e->where('nombre','like',"%{$q}%")->orWhere('apellido','like',"%{$q}%"))->orWhereHas('dispositivo',fn($d)=>$d->where('numero_serie','like',"%{$q}%")->orWhere('marca','like',"%{$q}%")->orWhere('modelo','like',"%{$q}%")))->limit(5)->get(); }
    private function buscarRenovaciones(string $q){ return Renovacion::with(['empleado','dispositivoNuevo'])->where(fn($x)=>$x->where('numero_solicitud','like',"%{$q}%")->orWhere('motivo','like',"%{$q}%")->orWhereHas('empleado',fn($e)=>$e->where('nombre','like',"%{$q}%")->orWhere('apellido','like',"%{$q}%")))->limit(5)->get(); }
}