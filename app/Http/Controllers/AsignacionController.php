<?php
namespace App\Http\Controllers;
use App\Models\{Asignacion,Dispositivo,Empleado};
use App\Services\AsignacionDispositivoService;
use Illuminate\Http\{RedirectResponse,Request};
use Illuminate\View\View;
class AsignacionController extends Controller {
    public function __construct(private readonly AsignacionDispositivoService $svc) {}
    public function index(Request $request): View {
        $q=Asignacion::with(['dispositivo','empleado'])->whereNull('fecha_devolucion');
        if($request->filled('buscar')){$b=$request->buscar;$q->where(fn($x)=>$x->whereHas('empleado',fn($e)=>$e->where('nombre','like',"%{$b}%")->orWhere('apellido','like',"%{$b}%")->orWhere('codigo_empleado','like',"%{$b}%"))->orWhereHas('dispositivo',fn($d)=>$d->where('numero_serie','like',"%{$b}%")->orWhere('marca','like',"%{$b}%")->orWhere('modelo','like',"%{$b}%")));}
        if($request->filled('departamento'))$q->whereHas('empleado',fn($e)=>$e->where('departamento',$request->departamento));
        $asignaciones=$q->orderByDesc('fecha_asignacion')->paginate(15)->withQueryString();
        $departamentos=Empleado::distinct()->orderBy('departamento')->pluck('departamento');
        return view('asignaciones.index',compact('asignaciones','departamentos'));
    }
    public function create(Request $request): View {
        return view('asignaciones.create',['empleados'=>Empleado::activos()->orderBy('apellido')->get(),'dispositivos'=>Dispositivo::disponibles()->orderBy('marca')->get(),'razonesExcepcion'=>AsignacionDispositivoService::RAZONES_EXCEPCION,'dispositivoSeleccionado'=>$request->filled('dispositivo_id')?Dispositivo::find($request->dispositivo_id):null]);
    }
    public function store(Request $request): RedirectResponse {
        $v=$request->validate(['dispositivo_id'=>'required|exists:dispositivos,id','empleado_id'=>'required|exists:empleados,id','fecha_asignacion'=>'required|date','razon_asignacion'=>'nullable|string|max:255','razon_excepcion'=>'nullable|in:ciclo_vencido,danio_fisico,danio_irreparable,robo_extravio,falla_tecnica','asignado_por'=>'nullable|string|max:150','observaciones'=>'nullable|string']);
        $empleado=Empleado::find($v['empleado_id']);
        $dispositivo=Dispositivo::find($v['dispositivo_id']);
        $error=$this->svc->validarAsignacion($empleado,$dispositivo,$v['razon_excepcion']??null);
        if($error) return back()->withInput()->withErrors(['empleado_id'=>$error]);
        if(!empty($v['razon_excepcion'])) $this->cerrarAsignacionAnterior($empleado,$v['fecha_asignacion'],$v['razon_excepcion']);
        Asignacion::create(['dispositivo_id'=>$dispositivo->id,'empleado_id'=>$empleado->id,'fecha_asignacion'=>$v['fecha_asignacion'],'razon_asignacion'=>$v['razon_asignacion']??null,'asignado_por'=>$v['asignado_por']??null,'observaciones'=>$v['observaciones']??null]);
        $dispositivo->update(['estado'=>'asignado','empleado_id'=>$empleado->id,'fecha_asignacion'=>$v['fecha_asignacion']]);
        return redirect()->route('dispositivos.show',$dispositivo)->with('success',"Dispositivo asignado a {$empleado->nombre_completo} exitosamente.")->with('mostrar_carta',true);
    }
    public function devolver(Dispositivo $dispositivo): View|RedirectResponse {
        $asignacion=$dispositivo->asignacionActiva;
        if(!$asignacion) return redirect()->route('dispositivos.show',$dispositivo)->with('error','Este dispositivo no tiene una asignacion activa.');
        return view('asignaciones.devolver',compact('dispositivo','asignacion'));
    }
    public function procesarDevolucion(Request $request, Dispositivo $dispositivo): RedirectResponse {
        $v=$request->validate(['fecha_devolucion'=>'required|date','razon_devolucion'=>'nullable|string|max:255','devuelto_por'=>'nullable|string|max:150','estado_destino'=>'required|in:disponible,en_reparacion,dado_de_baja','observaciones'=>'nullable|string']);
        $asignacion=$dispositivo->asignacionActiva;
        if(!$asignacion) return back()->with('error','No se encontro una asignacion activa.');
        $asignacion->update(['fecha_devolucion'=>$v['fecha_devolucion'],'razon_devolucion'=>$v['razon_devolucion']??null,'devuelto_por'=>$v['devuelto_por']??null,'observaciones'=>$v['observaciones']??null]);
        $dispositivo->update(['estado'=>$v['estado_destino'],'empleado_id'=>null,'fecha_asignacion'=>null]);
        return redirect()->route('dispositivos.show',$dispositivo)->with('success','Dispositivo devuelto y actualizado correctamente.');
    }
    public function historial(Dispositivo $dispositivo, Request $request): View {
        $asignaciones=$dispositivo->asignaciones()->with('empleado')->paginate(10)->withQueryString();
        return view('asignaciones.historial',compact('dispositivo','asignaciones'));
    }
    private function cerrarAsignacionAnterior(Empleado $empleado, string $fecha, string $razon): void {
        $a=Asignacion::where('empleado_id',$empleado->id)->whereNull('fecha_devolucion')->first();
        if(!$a) return;
        $a->update(['fecha_devolucion'=>$fecha,'razon_devolucion'=>AsignacionDispositivoService::RAZONES_EXCEPCION[$razon]??$razon]);
        $estado=AsignacionDispositivoService::ESTADO_ANTERIOR_POR_RAZON[$razon]??'disponible';
        Dispositivo::where('id',$a->dispositivo_id)->update(['estado'=>$estado,'empleado_id'=>null]);
    }
}