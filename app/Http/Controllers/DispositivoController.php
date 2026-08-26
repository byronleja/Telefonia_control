<?php
namespace App\Http\Controllers;
use App\Models\{Configuracion,Dispositivo};
use Illuminate\Http\{RedirectResponse,Request};
use Illuminate\View\View;
class DispositivoController extends Controller {
    private array $rulesBase=['marca'=>'required|string|max:100','modelo'=>'required|string|max:100','numero_telefonico'=>'nullable|string|max:20','operadora'=>'nullable|string|max:50','tipo'=>'required|in:smartphone,basico,tablet','meses_renovacion'=>'required|integer|min:1|max:120','fecha_compra'=>'nullable|date','costo'=>'nullable|numeric|min:0','color'=>'nullable|string|max:50','observaciones'=>'nullable|string'];
    public function index(Request $request): View {
        $query=Dispositivo::with('empleado')->whereNotIn('estado',['dado_de_baja','en_reparacion']);
        if($request->filled('buscar')){$b=$request->buscar;$query->where(fn($q)=>$q->where('numero_serie','like',"%{$b}%")->orWhere('marca','like',"%{$b}%")->orWhere('modelo','like',"%{$b}%")->orWhere('imei','like',"%{$b}%")->orWhere('numero_telefonico','like',"%{$b}%"));}
        if($request->filled('estado'))$query->where('estado',$request->estado);
        if($request->filled('tipo'))$query->where('tipo',$request->tipo);
        return view('dispositivos.index',['dispositivos'=>$query->orderBy('marca')->paginate(15)->withQueryString()]);
    }
    public function create(): View { return view('dispositivos.create',['mesesDefault'=>Configuracion::cicloRenovacionMeses()]); }
    public function store(Request $request): RedirectResponse {
        $v=$request->validate(array_merge($this->rulesBase,['numero_serie'=>'required|string|max:100|unique:dispositivos','imei'=>'nullable|string|max:20|unique:dispositivos']));
        $v['estado']='disponible';
        $d=Dispositivo::create($v);
        return redirect()->route('dispositivos.show',$d)->with('success','Dispositivo registrado exitosamente.');
    }
    public function show(Dispositivo $dispositivo): View {
        $dispositivo->load(['empleado','asignaciones.empleado','renovacionesComoActual.empleado','renovacionesComoNuevo.empleado']);
        return view('dispositivos.show',compact('dispositivo'));
    }
    public function edit(Dispositivo $dispositivo): View { return view('dispositivos.edit',['dispositivo'=>$dispositivo,'mesesDefault'=>Configuracion::cicloRenovacionMeses()]); }
    public function update(Request $request, Dispositivo $dispositivo): RedirectResponse {
        $v=$request->validate(array_merge($this->rulesBase,['numero_serie'=>"required|string|max:100|unique:dispositivos,numero_serie,{$dispositivo->id}",'imei'=>"nullable|string|max:20|unique:dispositivos,imei,{$dispositivo->id}",'estado'=>'required|in:disponible,asignado,en_reparacion,dado_de_baja']));
        if($v['estado']='dado_de_baja' && $dispositivo->estado!=='dado_de_baja') return redirect()->route('dispositivos.confirmar_baja',$dispositivo)->with('info','Para dar de baja usa el formulario de confirmacion.');
        $dispositivo->update($v);
        return redirect()->route('dispositivos.show',$dispositivo)->with('success','Dispositivo actualizado exitosamente.');
    }
    public function destroy(Dispositivo $dispositivo): RedirectResponse { $dispositivo->delete(); return redirect()->route('dispositivos.index')->with('success','Dispositivo eliminado exitosamente.'); }
    public function confirmarBaja(Dispositivo $dispositivo): View|RedirectResponse {
        if($dispositivo->estado==='asignado') return redirect()->route('dispositivos.show',$dispositivo)->with('error','Debes devolver el dispositivo antes de darlo de baja.');
        return view('dispositivos.confirmar_baja',compact('dispositivo'));
    }
    public function procesarBaja(Request $request, Dispositivo $dispositivo): RedirectResponse {
        $v=$request->validate(['motivo_baja'=>'required|string|max:255','fecha_baja'=>'required|date','tipo_baja'=>'required|in:danio_fisico,danio_irreparable,robo_extravio,fin_vida_util,otro','confirmacion'=>'required|accepted'],['motivo_baja.required'=>'El motivo de baja es obligatorio.','tipo_baja.required'=>'Selecciona el tipo de baja.','confirmacion.accepted'=>'Debes confirmar esta accion.']);
        $nota="[BAJA {$v['fecha_baja']}] ".strtoupper(str_replace('_',' ',$v['tipo_baja'])).": {$v['motivo_baja']}";
        $dispositivo->update(['estado'=>'dado_de_baja','observaciones'=>$dispositivo->observaciones ? $dispositivo->observaciones."\n".$nota : $nota]);
        return redirect()->route('reportes.equipos_danados')->with('success',"Dispositivo {$dispositivo->nombre_completo} dado de baja correctamente.");
    }
    public function restaurar(Request $request, Dispositivo $dispositivo): RedirectResponse {
        $request->validate(['motivo_restauracion'=>'required|string|max:255']);
        $nota="[RESTAURADO ".now()->format('Y-m-d')."]: {$request->motivo_restauracion}";
        $dispositivo->update(['estado'=>'disponible','observaciones'=>$dispositivo->observaciones ? $dispositivo->observaciones."\n".$nota : $nota]);
        return redirect()->route('dispositivos.show',$dispositivo)->with('success','Dispositivo restaurado a estado disponible.');
    }
}