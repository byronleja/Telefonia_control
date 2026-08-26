<?php
namespace App\Http\Controllers;
use App\Models\{Departamento,Empleado};
use Illuminate\Http\{RedirectResponse,Request};
use Illuminate\View\View;
class EmpleadoController extends Controller {
    private array $rulesBase=['nombre'=>'required|string|max:100','apellido'=>'required|string|max:100','departamento'=>'required|string|max:100','cargo'=>'required|string|max:100','telefono'=>'nullable|string|max:20','estado'=>'required|in:activo,inactivo'];
    public function index(Request $request): View {
        $query=Empleado::query();
        if($request->filled('buscar')){$b=$request->buscar;$query->where(fn($q)=>$q->where('nombre','like',"%{$b}%")->orWhere('apellido','like',"%{$b}%")->orWhere('codigo_empleado','like',"%{$b}%")->orWhere('email','like',"%{$b}%"));}
        if($request->filled('estado'))$query->where('estado',$request->estado);
        if($request->filled('departamento'))$query->where('departamento',$request->departamento);
        $empleados=$query->orderBy('apellido')->paginate(15)->withQueryString();
        $departamentos=Departamento::activos()->orderBy('nombre')->pluck('nombre');
        return view('empleados.index',compact('empleados','departamentos'));
    }
    public function create(): View { return view('empleados.create',['departamentos'=>Departamento::activos()->orderBy('nombre')->get()]); }
    public function store(Request $request): RedirectResponse {
        $v=$request->validate(array_merge($this->rulesBase,['codigo_empleado'=>'required|string|max:50|unique:empleados','email'=>'required|email|unique:empleados']));
        Empleado::create($v);
        return redirect()->route('empleados.index')->with('success','Empleado registrado exitosamente.');
    }
    public function show(Empleado $empleado): View { $empleado->load(['dispositivos','renovaciones.dispositivoNuevo']); return view('empleados.show',compact('empleado')); }
    public function edit(Empleado $empleado): View { return view('empleados.edit',['empleado'=>$empleado,'departamentos'=>Departamento::activos()->orderBy('nombre')->get()]); }
    public function update(Request $request, Empleado $empleado): RedirectResponse {
        $v=$request->validate(array_merge($this->rulesBase,['codigo_empleado'=>"required|string|max:50|unique:empleados,codigo_empleado,{$empleado->id}",'email'=>"required|email|unique:empleados,email,{$empleado->id}"]));
        $empleado->update($v);
        return redirect()->route('empleados.index')->with('success','Empleado actualizado exitosamente.');
    }
    public function destroy(Empleado $empleado): RedirectResponse { $empleado->delete(); return redirect()->route('empleados.index')->with('success','Empleado eliminado.'); }
}