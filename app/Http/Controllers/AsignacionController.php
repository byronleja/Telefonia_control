<?php
namespace App\Http\Controllers;

use App\Models\{Asignacion, Bloque, Configuracion, Dispositivo, Empleado};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class AsignacionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Asignacion::with(['empleado','dispositivo.bloque','dispositivo.empleado'])
            ->whereNull('fecha_devolucion');

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(fn($q) => $q
                ->whereHas('empleado', fn($e) => $e
                    ->where('nombre','like',"%{$b}%")
                    ->orWhere('apellido','like',"%{$b}%")
                    ->orWhere('codigo_empleado','like',"%{$b}%"))
                ->orWhereHas('dispositivo', fn($d) => $d
                    ->where('numero_serie','like',"%{$b}%")
                    ->orWhere('marca','like',"%{$b}%")
                    ->orWhere('imei','like',"%{$b}%"))
            );
        }

        if ($request->filled('departamento')) {
            $query->whereHas('empleado', fn($q) => $q->where('departamento', $request->departamento));
        }

        if ($request->filled('bloque_id')) {
            $query->whereHas('dispositivo', fn($q) => $q->where('bloque_id', $request->bloque_id));
        }

        $asignaciones  = $query->orderByDesc('fecha_asignacion')->paginate(15)->withQueryString();
        $departamentos = Empleado::distinct()->pluck('departamento')->filter()->sort()->values();
        $bloques       = Bloque::activos()->get();

        return view('asignaciones.index', compact('asignaciones','departamentos','bloques'));
    }

    public function create(Request $request): View
    {
        $dispositivos = Dispositivo::disponibles()->with('bloque')->orderBy('marca')->get();
        $empleados    = Empleado::activos()->orderBy('apellido')->get();
        $razonesExcepcion = [
            'segundo_dispositivo' => 'Segundo dispositivo autorizado',
            'reemplazo_temporal'  => 'Reemplazo temporal',
            'proyecto_especial'   => 'Proyecto especial',
            'otro'                => 'Otro motivo autorizado',
        ];
        $dispositivoSeleccionado = $request->filled('dispositivo_id')
            ? Dispositivo::find($request->dispositivo_id) : null;

        return view('asignaciones.create', compact('dispositivos','empleados','razonesExcepcion','dispositivoSeleccionado'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'dispositivo_id'  => 'required|exists:dispositivos,id',
            'empleado_id'     => 'required|exists:empleados,id',
            'fecha_asignacion'=> 'required|date',
            'asignado_por'    => 'nullable|string|max:150',
            'razon_asignacion'=> 'nullable|string|max:255',
            'razon_excepcion' => 'nullable|string|max:255',
            'observaciones'   => 'nullable|string',
        ]);

        $dispositivo = Dispositivo::findOrFail($request->dispositivo_id);
        $empleado    = Empleado::findOrFail($request->empleado_id);

        // Registrar asignacion
        Asignacion::create([
            'dispositivo_id'  => $dispositivo->id,
            'empleado_id'     => $empleado->id,
            'fecha_asignacion'=> $request->fecha_asignacion,
            'asignado_por'    => $request->asignado_por,
            'razon_asignacion'=> $request->razon_asignacion,
            'razon_excepcion' => $request->razon_excepcion,
            'observaciones'   => $request->observaciones,
        ]);

        $dispositivo->update([
            'estado'          => 'asignado',
            'empleado_id'     => $empleado->id,
            'fecha_asignacion'=> $request->fecha_asignacion,
        ]);

        return redirect()->route('dispositivos.show', $dispositivo)
            ->with('success', 'Dispositivo asignado a '.$empleado->nombre_completo.' exitosamente.')
            ->with('mostrar_carta', true);
    }

    public function devolver(Dispositivo $dispositivo): View
    {
        $asignacion = Asignacion::where('dispositivo_id', $dispositivo->id)
            ->whereNull('fecha_devolucion')->latest()->firstOrFail();
        return view('asignaciones.devolver', compact('dispositivo','asignacion'));
    }

    public function procesarDevolucion(Request $request, Dispositivo $dispositivo): RedirectResponse
    {
        $request->validate([
            'fecha_devolucion' => 'required|date',
            'devuelto_por'     => 'nullable|string|max:150',
            'razon_devolucion' => 'nullable|string|max:255',
            'estado_destino'   => 'required|in:disponible,en_reparacion,dado_de_baja',
            'observaciones'    => 'nullable|string',
        ]);

        Asignacion::where('dispositivo_id', $dispositivo->id)
            ->whereNull('fecha_devolucion')
            ->update([
                'fecha_devolucion' => $request->fecha_devolucion,
                'devuelto_por'     => $request->devuelto_por,
                'razon_devolucion' => $request->razon_devolucion,
                'observaciones'    => $request->observaciones,
            ]);

        $dispositivo->update([
            'estado'          => $request->estado_destino,
            'empleado_id'     => null,
            'fecha_asignacion'=> null,
        ]);

        return redirect()->route('dispositivos.index')
            ->with('success', 'Devolucion registrada. Dispositivo en estado: '.ucfirst($request->estado_destino).'.');
    }

    public function historial(Request $request, Dispositivo $dispositivo): View
    {
        $asignaciones = Asignacion::with('empleado')
            ->where('dispositivo_id', $dispositivo->id)
            ->orderByDesc('fecha_asignacion')
            ->paginate(10);
        return view('asignaciones.historial', compact('dispositivo','asignaciones'));
    }
}