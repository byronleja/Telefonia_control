<?php
namespace App\Http\Controllers;

use App\Models\{Bloque, Dispositivo};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class BloqueController extends Controller
{
    public function index(): View
    {
        $bloques = Bloque::withCount([
            'dispositivos',
            'dispositivos as asignados_count' => fn($q) => $q->where('estado','asignado'),
            'dispositivos as disponibles_count' => fn($q) => $q->where('estado','disponible'),
        ])->orderBy('nombre')->paginate(20);

        $totalCostoMensual = Bloque::activos()->get()->sum('costo_total_mensual');

        return view('bloques.index', compact('bloques', 'totalCostoMensual'));
    }

    public function create(): View
    {
        return view('bloques.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'nombre'              => 'required|string|max:100|unique:bloques,nombre',
            'descripcion'         => 'nullable|string|max:255',
            'gama'                => 'required|in:alta,media,basica',
            'costo_mensual_linea' => 'required|numeric|min:0',
            'operadora'           => 'nullable|string|max:80',
            'color_etiqueta'      => 'required|string|max:7',
        ], ['nombre.unique' => 'Ya existe un bloque con ese nombre.']);

        $v['activo'] = $request->boolean('activo', true);
        Bloque::create($v);

        return redirect()->route('bloques.index')
            ->with('success', 'Bloque ' . $v['nombre'] . ' creado exitosamente.');
    }

    public function show(Bloque $bloque): View
    {
        $bloque->loadCount([
            'dispositivos',
            'dispositivos as asignados_count'   => fn($q) => $q->where('estado','asignado'),
            'dispositivos as disponibles_count' => fn($q) => $q->where('estado','disponible'),
            'dispositivos as baja_count'        => fn($q) => $q->where('estado','dado_de_baja'),
        ]);

        $dispositivos = Dispositivo::with('empleado')
            ->where('bloque_id', $bloque->id)
            ->orderBy('estado')->orderBy('marca')
            ->paginate(15);

        // Historial de movimientos (dispositivos que alguna vez estuvieron en este bloque)
        $historial = \App\Models\Asignacion::with(['empleado','dispositivo'])
            ->whereHas('dispositivo', fn($q) => $q->where('bloque_id', $bloque->id))
            ->orderByDesc('fecha_asignacion')->limit(20)->get();

        return view('bloques.show', compact('bloque', 'dispositivos', 'historial'));
    }

    public function edit(Bloque $bloque): View
    {
        $totalDispositivos = $bloque->dispositivos()->count();
        return view('bloques.edit', compact('bloque', 'totalDispositivos'));
    }

    public function update(Request $request, Bloque $bloque): RedirectResponse
    {
        $v = $request->validate([
            'nombre'              => 'required|string|max:100|unique:bloques,nombre,' . $bloque->id,
            'descripcion'         => 'nullable|string|max:255',
            'gama'                => 'required|in:alta,media,basica',
            'costo_mensual_linea' => 'required|numeric|min:0',
            'operadora'           => 'nullable|string|max:80',
            'color_etiqueta'      => 'required|string|max:7',
        ]);
        $v['activo'] = $request->boolean('activo', true);
        $bloque->update($v);

        return redirect()->route('bloques.index')
            ->with('success', 'Bloque actualizado exitosamente.');
    }

    public function destroy(Bloque $bloque): RedirectResponse
    {
        if ($bloque->dispositivos()->exists()) {
            return back()->with('error',
                'No se puede eliminar el bloque porque tiene dispositivos asignados. Cambia el bloque de esos dispositivos primero.');
        }
        $nombre = $bloque->nombre;
        $bloque->delete();
        return redirect()->route('bloques.index')
            ->with('success', 'Bloque ' . $nombre . ' eliminado.');
    }

    // Cambiar bloque de un dispositivo
    public function cambiarBloque(Request $request, Dispositivo $dispositivo): RedirectResponse
    {
        $request->validate([
            'bloque_id' => 'nullable|exists:bloques,id',
            'motivo'    => 'nullable|string|max:255',
        ]);

        $bloqueAnterior = $dispositivo->bloque?->nombre ?? 'Sin bloque';
        $dispositivo->update(['bloque_id' => $request->bloque_id ?: null]);
        $bloqueNuevo = $dispositivo->fresh()->bloque?->nombre ?? 'Sin bloque';

        return back()->with('success',
            'Dispositivo movido de "' . $bloqueAnterior . '" a "' . $bloqueNuevo . '".');
    }

    // Reporte por bloque
    public function reporte(Request $request): View
    {
        $bloques = Bloque::with(['dispositivos.empleado'])
            ->withCount([
                'dispositivos',
                'dispositivos as asignados_count'   => fn($q) => $q->where('estado','asignado'),
                'dispositivos as disponibles_count' => fn($q) => $q->where('estado','disponible'),
                'dispositivos as baja_count'        => fn($q) => $q->where('estado','dado_de_baja'),
            ])
            ->when($request->filled('gama'),   fn($q) => $q->where('gama',   $request->gama))
            ->when($request->filled('activo'), fn($q) => $q->where('activo', $request->activo))
            ->orderBy('gama')->orderBy('nombre')
            ->get();

        $totalMensual = $bloques->sum('costo_total_mensual');
        $totalDispositivos = $bloques->sum('dispositivos_count');
        $totalAsignados    = $bloques->sum('asignados_count');

        return view('bloques.reporte', compact(
            'bloques', 'totalMensual', 'totalDispositivos', 'totalAsignados'
        ));
    }
}