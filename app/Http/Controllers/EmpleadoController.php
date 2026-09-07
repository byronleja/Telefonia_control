<?php
namespace App\Http\Controllers;

use App\Models\{Asignacion, Departamento, Empleado};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class EmpleadoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Empleado::query();
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(fn($q) => $q
                ->where('nombre','like',"%{$b}%")
                ->orWhere('apellido','like',"%{$b}%")
                ->orWhere('codigo_empleado','like',"%{$b}%")
                ->orWhere('email','like',"%{$b}%")
            );
        }
        if ($request->filled('departamento')) $query->where('departamento',$request->departamento);
        if ($request->filled('estado'))       $query->where('estado',$request->estado);

        $empleados     = $query->orderBy('apellido')->paginate(15)->withQueryString();
        $departamentos = Empleado::distinct()->pluck('departamento')->filter()->sort()->values();

        return view('empleados.index', compact('empleados','departamentos'));
    }

    public function create(): View
    {
        $departamentos = Departamento::activos()->orderBy('nombre')->get();
        return view('empleados.create', compact('departamentos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido'         => 'required|string|max:100',
            'codigo_empleado'  => 'required|string|max:30|unique:empleados,codigo_empleado',
            'email'            => 'required|email|unique:empleados,email',
            'departamento'     => 'required|string|max:100',
            'cargo'            => 'required|string|max:100',
            'telefono'         => 'nullable|string|max:20',
            'estado'           => 'required|in:activo,inactivo',
        ],[
            'codigo_empleado.unique' => 'Ya existe un empleado con ese codigo.',
            'email.unique'           => 'Ya existe un empleado con ese email.',
        ]);
        Empleado::create($validated);
        return redirect()->route('empleados.index')->with('success','Empleado registrado exitosamente.');
    }

    public function show(Empleado $empleado): View
    {
        $empleado->load(['dispositivos.bloque']);
        return view('empleados.show', compact('empleado'));
    }

    public function edit(Empleado $empleado): View
    {
        $departamentos = Departamento::activos()->orderBy('nombre')->get();
        return view('empleados.edit', compact('empleado','departamentos'));
    }

    public function update(Request $request, Empleado $empleado): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'          => 'required|string|max:100',
            'apellido'        => 'required|string|max:100',
            'codigo_empleado' => 'required|string|max:30|unique:empleados,codigo_empleado,'.$empleado->id,
            'email'           => 'required|email|unique:empleados,email,'.$empleado->id,
            'departamento'    => 'required|string|max:100',
            'cargo'           => 'required|string|max:100',
            'telefono'        => 'nullable|string|max:20',
            'estado'          => 'required|in:activo,inactivo',
        ]);
        $empleado->update($validated);
        return redirect()->route('empleados.show',$empleado)->with('success','Empleado actualizado exitosamente.');
    }

    public function destroy(Empleado $empleado): RedirectResponse
    {
        if ($empleado->dispositivos()->exists()) {
            return back()->with('error','No se puede eliminar: el empleado tiene dispositivos asignados.');
        }
        $nombre = $empleado->nombre_completo;
        $empleado->delete();
        return redirect()->route('empleados.index')->with('success','Empleado '.$nombre.' eliminado.');
    }

    public function historial(Empleado $empleado): View
    {
        $empleado->load(['dispositivos.bloque']);
        $asignaciones = Asignacion::with(['dispositivo.bloque'])
            ->where('empleado_id', $empleado->id)
            ->orderByDesc('fecha_asignacion')
            ->paginate(10);
        return view('empleados.historial', compact('empleado','asignaciones'));
    }
}