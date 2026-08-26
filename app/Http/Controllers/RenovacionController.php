<?php
namespace App\Http\Controllers;
use App\Models\{Adjunto,Dispositivo,Empleado,Renovacion};
use Illuminate\Http\{RedirectResponse,Request};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
class RenovacionController extends Controller {
    private array $rulesBase=['empleado_id'=>'required|exists:empleados,id','dispositivo_actual_id'=>'nullable|exists:dispositivos,id','dispositivo_nuevo_id'=>'nullable|exists:dispositivos,id','fecha_solicitud'=>'required|date','motivo'=>'required|string|max:255','descripcion'=>'nullable|string','destino_equipo_anterior'=>'nullable|in:devuelto,comprado,no_aplica','precio_compra_empleado'=>'nullable|numeric|min:0','notas_destino_equipo'=>'nullable|string'];
    public function index(Request $request): View {
        $q=Renovacion::with(['empleado','dispositivoActual','dispositivoNuevo']);
        if($request->filled('buscar')){$b=$request->buscar;$q->where(fn($x)=>$x->where('numero_solicitud','like',"%{$b}%")->orWhereHas('empleado',fn($e)=>$e->where('nombre','like',"%{$b}%")->orWhere('apellido','like',"%{$b}%")));}
        if($request->filled('estado'))$q->where('estado',$request->estado);
        if($request->filled('anio'))$q->whereYear('fecha_solicitud',$request->anio);
        if($request->filled('ciclo'))$q->whereHas('dispositivoNuevo',fn($x)=>$x->where('meses_renovacion',$request->ciclo));
        $renovaciones=$q->orderByDesc('created_at')->paginate(15)->withQueryString();
        $ciclosDisponibles=Dispositivo::distinct()->orderBy('meses_renovacion')->pluck('meses_renovacion')->filter()->unique()->values()->toArray();
        return view('renovaciones.index',compact('renovaciones','ciclosDisponibles'));
    }
    public function create(): View { return view('renovaciones.create',$this->formData()); }
    public function store(Request $request): RedirectResponse {
        $v=$request->validate($this->rulesBase);
        $r=Renovacion::create($v+['estado'=>'pendiente']);
        $this->guardarAdjuntos($request,$r,$request->tipo_adjunto??'solicitud');
        return redirect()->route('renovaciones.show',$r)->with('success',"Solicitud {$r->numero_solicitud} creada.");
    }
    public function show(Renovacion $renovacion): View { $renovacion->load(['empleado','dispositivoActual','dispositivoNuevo','adjuntos']); return view('renovaciones.show',compact('renovacion')); }
    public function edit(Renovacion $renovacion): View { return view('renovaciones.edit',array_merge(['renovacion'=>$renovacion],$this->formData())); }
    public function update(Request $request, Renovacion $renovacion): RedirectResponse {
        $v=$request->validate(array_merge($this->rulesBase,['estado'=>'required|in:pendiente,aprobada,rechazada,entregada','fecha_aprobacion'=>'nullable|date','fecha_entrega'=>'nullable|date','usuario_entrega'=>'nullable|string|max:150','observaciones_aprobacion'=>'nullable|string','aprobado_por'=>'nullable|string|max:150']));
        $prev=$renovacion->estado; $renovacion->update($v);
        $this->guardarAdjuntos($request,$renovacion,$request->tipo_adjunto??'otro');
        if($prev!=='entregada'&&$v['estado']==='entregada'){$this->procesarEntrega($renovacion);return redirect()->route('renovaciones.show',$renovacion)->with('success','Renovacion entregada. Imprime la carta de custodia.')->with('mostrar_carta',true);}
        return redirect()->route('renovaciones.show',$renovacion)->with('success','Renovacion actualizada.');
    }
    public function destroy(Renovacion $renovacion): RedirectResponse {
        $renovacion->adjuntos->each(fn($a)=>Storage::disk('public')->delete($a->ruta));
        $renovacion->delete();
        return redirect()->route('renovaciones.index')->with('success','Renovacion eliminada.');
    }
    public function eliminarAdjunto(Adjunto $adjunto): RedirectResponse {
        $r=$adjunto->renovacion; Storage::disk('public')->delete($adjunto->ruta); $adjunto->delete();
        return redirect()->route('renovaciones.show',$r)->with('success','Adjunto eliminado.');
    }
    private function procesarEntrega(Renovacion $r): void {
        if($r->dispositivo_nuevo_id&&$r->empleado_id) Dispositivo::where('id',$r->dispositivo_nuevo_id)->update(['estado'=>'asignado','empleado_id'=>$r->empleado_id,'fecha_asignacion'=>$r->fecha_entrega??now()->toDateString()]);
        if($r->dispositivo_actual_id) Dispositivo::where('id',$r->dispositivo_actual_id)->update(['estado'=>match($r->destino_equipo_anterior){'devuelto'=>'disponible','comprado'=>'dado_de_baja',default=>'disponible'},'empleado_id'=>null]);
    }
    private function formData(): array { return ['empleados'=>Empleado::activos()->orderBy('apellido')->get(),'dispositivosActuales'=>Dispositivo::where('estado','asignado')->with('empleado')->orderBy('marca')->get(),'dispositivosNuevos'=>Dispositivo::disponibles()->orderBy('marca')->get()]; }
    private function guardarAdjuntos(Request $request, Renovacion $r, string $tipo): void {
        if(!$request->hasFile('adjuntos'))return;
        foreach($request->file('adjuntos') as $a) { $ruta=$a->store("adjuntos/renovaciones/{$r->id}",'public'); Adjunto::create(['renovacion_id'=>$r->id,'nombre_original'=>$a->getClientOriginalName(),'nombre_archivo'=>basename($ruta),'ruta'=>$ruta,'tipo_mime'=>$a->getMimeType(),'tamanio'=>$a->getSize(),'tipo_adjunto'=>$tipo]); }
    }
}