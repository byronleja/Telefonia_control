<?php
namespace App\Http\Controllers;

use App\Models\{Bloque, Configuracion, Dispositivo};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class DispositivoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Dispositivo::with(['empleado','bloque'])
            ->whereNotIn('estado',['dado_de_baja','en_reparacion']);

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(fn($q) => $q
                ->where('numero_serie','like',"%{$b}%")
                ->orWhere('marca','like',"%{$b}%")
                ->orWhere('modelo','like',"%{$b}%")
                ->orWhere('imei','like',"%{$b}%")
            );
        }
        if ($request->filled('estado'))    $query->where('estado',$request->estado);
        if ($request->filled('tipo'))      $query->where('tipo',$request->tipo);
        if ($request->filled('bloque_id')) $query->where('bloque_id',$request->bloque_id);

        $dispositivos = $query->orderBy('marca')->orderBy('modelo')->paginate(15)->withQueryString();
        $bloques      = Bloque::activos()->get();

        return view('dispositivos.index', compact('dispositivos','bloques'));
    }

    public function create(): View
    {
        $mesesDefault = Configuracion::where('clave','ciclo_renovacion_meses')->value('valor') ?? 18;
        $bloques      = Bloque::activos()->get();
        return view('dispositivos.create', compact('mesesDefault','bloques'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'numero_serie'      => 'required|string|max:100|unique:dispositivos,numero_serie',
            'marca'             => 'required|string|max:80',
            'modelo'            => 'required|string|max:100',
            'imei'              => 'nullable|string|max:20|unique:dispositivos,imei',
            'tipo'              => 'required|in:smartphone,basico,tablet',
            'bloque_id'         => 'required|exists:bloques,id',
            'numero_telefonico' => 'nullable|string|max:20',
            'operadora'         => 'nullable|string|max:60',
            'color'             => 'nullable|string|max:40',
            'fecha_compra'      => 'nullable|date',
            'costo'             => 'nullable|numeric|min:0',
            'meses_renovacion'  => 'required|integer|min:1|max:120',
            'observaciones'     => 'nullable|string',
        ],[
            'bloque_id.required' => 'El bloque es obligatorio.',
            'numero_serie.unique'=> 'Ya existe un dispositivo con ese numero de serie.',
            'imei.unique'        => 'Ya existe un dispositivo con ese IMEI.',
        ]);

        $validated['estado'] = 'disponible';
        Dispositivo::create($validated);

        return redirect()->route('dispositivos.index')
            ->with('success', 'Dispositivo registrado exitosamente.');
    }

    public function show(Dispositivo $dispositivo): View
    {
        $dispositivo->load(['empleado','bloque','asignaciones.empleado']);
        return view('dispositivos.show', compact('dispositivo'));
    }

    public function edit(Dispositivo $dispositivo): View
    {
        $mesesDefault = Configuracion::where('clave','ciclo_renovacion_meses')->value('valor') ?? 18;
        $bloques      = Bloque::activos()->get();
        return view('dispositivos.edit', compact('dispositivo','mesesDefault','bloques'));
    }

    public function update(Request $request, Dispositivo $dispositivo): RedirectResponse
    {
        $validated = $request->validate([
            'numero_serie'      => 'required|string|max:100|unique:dispositivos,numero_serie,'.$dispositivo->id,
            'marca'             => 'required|string|max:80',
            'modelo'            => 'required|string|max:100',
            'imei'              => 'nullable|string|max:20|unique:dispositivos,imei,'.$dispositivo->id,
            'tipo'              => 'required|in:smartphone,basico,tablet',
            'estado'            => 'required|in:disponible,asignado,en_reparacion,dado_de_baja',
            'bloque_id'         => 'required|exists:bloques,id',
            'numero_telefonico' => 'nullable|string|max:20',
            'operadora'         => 'nullable|string|max:60',
            'color'             => 'nullable|string|max:40',
            'fecha_compra'      => 'nullable|date',
            'costo'             => 'nullable|numeric|min:0',
            'meses_renovacion'  => 'required|integer|min:1|max:120',
            'observaciones'     => 'nullable|string',
        ],[
            'bloque_id.required' => 'El bloque es obligatorio.',
        ]);

        $dispositivo->update($validated);

        return redirect()->route('dispositivos.show', $dispositivo)
            ->with('success', 'Dispositivo actualizado exitosamente.');
    }

    public function confirmarBaja(Dispositivo $dispositivo): View
    {
        return view('dispositivos.confirmar_baja', compact('dispositivo'));
    }

    public function procesarBaja(Request $request, Dispositivo $dispositivo): RedirectResponse
    {
        $request->validate([
            'fecha_baja'   => 'required|date',
            'tipo_baja'    => 'required|string',
            'motivo_baja'  => 'required|string|max:500',
            'confirmacion' => 'accepted',
        ],[
            'confirmacion.accepted' => 'Debes confirmar que deseas dar de baja el dispositivo.',
        ]);

        // Liberar del empleado si estaba asignado
        if ($dispositivo->empleado_id) {
            \App\Models\Asignacion::where('dispositivo_id',$dispositivo->id)
                ->whereNull('fecha_devolucion')
                ->update([
                    'fecha_devolucion' => $request->fecha_baja,
                    'razon_devolucion' => 'Baja del dispositivo: '.$request->motivo_baja,
                ]);
        }

        $dispositivo->update([
            'estado'      => 'dado_de_baja',
            'empleado_id' => null,
            'observaciones' => trim(
                $request->tipo_baja.' — '.$request->motivo_baja.
                ($dispositivo->observaciones ? ' | '.$dispositivo->observaciones : '')
            ),
        ]);

        return redirect()->route('dispositivos.index')
            ->with('success', 'Dispositivo dado de baja exitosamente.');
    }

    public function restaurar(Request $request, Dispositivo $dispositivo): RedirectResponse
    {
        $dispositivo->update([
            'estado'          => 'disponible',
            'empleado_id'     => null,
            'fecha_asignacion'=> null,
            'observaciones'   => $request->motivo_restauracion
                ? 'Restaurado: '.$request->motivo_restauracion
                : null,
        ]);

        return redirect()->route('dispositivos.show',$dispositivo)
            ->with('success', 'Dispositivo restaurado a Disponible.');
    }
}