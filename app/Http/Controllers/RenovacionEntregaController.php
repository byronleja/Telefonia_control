<?php
namespace App\Http\Controllers;

use App\Models\{Asignacion, Dispositivo, Empleado, Renovacion};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class RenovacionEntregaController extends Controller
{
    public function create(): View
    {
        // Leer meses de anticipacion desde configuracion (defecto 2)
        $mesesAlerta = (int) (\App\Models\Configuracion::where('clave','alerta_anticipacion_meses')
            ->value('valor') ?? 2);

        // Solo empleados cuyo dispositivo asignado ya vencio o vence dentro del periodo de alerta
        $empleados = Empleado::activos()
            ->with(['dispositivos' => fn($q) => $q->where('estado','asignado')])
            ->orderBy('apellido')->get()
            ->filter(function($e) use ($mesesAlerta) {
                $disp = $e->dispositivos->first();
                if (!$disp) return false;
                // meses_para_renovacion <= mesesAlerta significa vencido o proximo
                return $disp->meses_para_renovacion <= $mesesAlerta;
            })
            ->map(function($e) {
                $disp = $e->dispositivos->first();
                $e->dispositivoActual       = $disp;
                $e->meses_para_renovacion   = $disp?->meses_para_renovacion ?? 0;
                $e->fecha_vencimiento       = $disp?->fecha_vencimiento_renovacion;
                return $e;
            })
            ->sortBy('meses_para_renovacion'); // vencidos primero

        // Solo dispositivos disponibles para entregar
        $dispositivos = Dispositivo::disponibles()->orderBy('marca')->get();

        return view('renovaciones.entregar', compact('empleados','dispositivos','mesesAlerta'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'empleado_id'          => 'required|exists:empleados,id',
            'dispositivo_actual_id'=> 'nullable|exists:dispositivos,id',
            'dispositivo_nuevo_id' => 'required|exists:dispositivos,id',
            'fecha_entrega'        => 'required|date',
            'destino_anterior'     => 'required|in:devuelto,baja',
            'observaciones'        => 'nullable|string',
        ],[
            'dispositivo_nuevo_id.required' => 'Debes seleccionar el dispositivo nuevo a entregar.',
        ]);

        $empleado         = Empleado::findOrFail($request->empleado_id);
        $dispositivoNuevo = Dispositivo::findOrFail($request->dispositivo_nuevo_id);
        $dispAnterior     = $request->filled('dispositivo_actual_id')
            ? Dispositivo::find($request->dispositivo_actual_id)
            : null;
        $fechaEntrega     = $request->fecha_entrega;

        // ── 1. Manejar dispositivo anterior ───────────────────────────────────
        if ($dispAnterior && $dispAnterior->estado === 'asignado') {
            Asignacion::where('dispositivo_id', $dispAnterior->id)
                ->whereNull('fecha_devolucion')
                ->update([
                    'fecha_devolucion' => $fechaEntrega,
                    'devuelto_por'     => auth()->user()->name,
                    'razon_devolucion' => 'Renovacion — entrega de equipo nuevo',
                ]);

            $nuevoEstado = $request->destino_anterior === 'baja' ? 'dado_de_baja' : 'disponible';
            $dispAnterior->update([
                'estado'          => $nuevoEstado,
                'empleado_id'     => null,
                'fecha_asignacion'=> null,
                'observaciones'   => $request->observaciones
                    ? 'Renovacion: '.$request->observaciones
                    : 'Equipo devuelto por renovacion',
            ]);
        }

        // ── 2. Asignar nuevo ──────────────────────────────────────────────────
        $dispositivoNuevo->update([
            'estado'          => 'asignado',
            'empleado_id'     => $empleado->id,
            'fecha_asignacion'=> $fechaEntrega,
        ]);

        // ── 3. Historial ──────────────────────────────────────────────────────
        Asignacion::create([
            'dispositivo_id'  => $dispositivoNuevo->id,
            'empleado_id'     => $empleado->id,
            'fecha_asignacion'=> $fechaEntrega,
            'razon_asignacion'=> 'Entrega por renovacion'
                .($dispAnterior ? ' — equipo anterior: '.$dispAnterior->numero_serie : ''),
            'asignado_por'    => auth()->user()->name,
        ]);

        // ── 4. Crear registro de renovacion ───────────────────────────────────
        Renovacion::create([
            'empleado_id'              => $empleado->id,
            'dispositivo_actual_id'    => $dispAnterior?->id,
            'dispositivo_nuevo_id'     => $dispositivoNuevo->id,
            'estado'                   => 'entregada',
            'fecha_solicitud'          => $fechaEntrega,
            'fecha_aprobacion'         => $fechaEntrega,
            'fecha_entrega'            => $fechaEntrega,
            'motivo'                   => 'Renovacion por vencimiento de ciclo',
            'descripcion'              => $request->observaciones,
            'aprobado_por'             => auth()->user()->name,
            'usuario_entrega'          => auth()->user()->name,
            'destino_equipo_anterior'  => $request->destino_anterior === 'baja' ? 'devuelto' : 'devuelto',
        ]);

        return redirect()->route('dispositivos.show', $dispositivoNuevo)
            ->with('success', 'Entrega por renovacion registrada exitosamente.')
            ->with('mostrar_carta', true);
    }
}