<?php
namespace App\Http\Controllers;

use App\Models\{Asignacion, Dispositivo, Empleado, SolicitudEntrega};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class SolicitudEntregaController extends Controller
{
    public function index(Request $request): View
    {
        $query = SolicitudEntrega::with(['empleado','dispositivo','creadoPor']);
        if (auth()->user()->esContabilidad()) $query->where('creado_por', auth()->id());
        if ($request->filled('estado')) $query->where('estado', $request->estado);
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(fn($q) => $q
                ->where('numero_solicitud','like',"%{$b}%")
                ->orWhere('numero_boleta','like',"%{$b}%")
                ->orWhereHas('empleado', fn($e) => $e
                    ->where('nombre','like',"%{$b}%")
                    ->orWhere('apellido','like',"%{$b}%")));
        }
        $solicitudes = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $pendientes  = SolicitudEntrega::where('estado','pendiente')->count();
        return view('solicitudes.index', compact('solicitudes','pendientes'));
    }

    public function create(): View
    {
        // Solo empleados CON dispositivo asignado
        $empleados = Empleado::activos()
            ->with(['dispositivos' => fn($q) => $q->where('estado','asignado')])
            ->orderBy('apellido')->get()
            ->filter(fn($e) => $e->dispositivos->isNotEmpty())
            ->map(function($e) { $e->dispositivoActual = $e->dispositivos->first(); return $e; });

        return view('solicitudes.create', compact('empleados'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'empleado_id'           => 'required|exists:empleados,id',
            'dispositivo_actual_id' => 'required|exists:dispositivos,id',
            'numero_boleta'         => 'required|string|max:100',
            'fecha_solicitud'       => 'required|date',
            'boleta'                => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'observaciones'         => 'nullable|string',
        ],[
            'dispositivo_actual_id.required' => 'El empleado debe tener un dispositivo asignado.',
            'boleta.required' => 'Debes adjuntar la boleta de pago.',
            'boleta.mimes'    => 'Solo JPG, PNG o PDF.',
        ]);

        $dispActual = Dispositivo::find($request->dispositivo_actual_id);
        if (!$dispActual || $dispActual->empleado_id != $request->empleado_id)
            return back()->with('error','El dispositivo no corresponde al empleado seleccionado.');

        $archivo = $request->file('boleta');
        SolicitudEntrega::create([
            'empleado_id'           => $request->empleado_id,
            'dispositivo_id'        => null,
            'dispositivo_actual_id' => $request->dispositivo_actual_id,
            'creado_por'            => auth()->id(),
            'numero_boleta'         => $request->numero_boleta,
            'ruta_boleta'           => $archivo->store('boletas','public'),
            'nombre_boleta'         => $archivo->getClientOriginalName(),
            'fecha_solicitud'       => $request->fecha_solicitud,
            'observaciones'         => $request->observaciones,
            'estado'                => 'pendiente',
        ]);

        return redirect()->route('solicitudes.index')
            ->with('success','Solicitud enviada a Sistemas. Queda pendiente de entrega.');
    }

    public function show(SolicitudEntrega $solicitude): View
    {
        $solicitude->load(['empleado','dispositivoActual','dispositivo','creadoPor','atendidoPor']);
        $dispositivosDisponibles = Dispositivo::disponibles()->orderBy('marca')->get();
        return view('solicitudes.show', compact('solicitude','dispositivosDisponibles'));
    }

    /**
     * Sistemas confirma:
     * - Dispositivo anterior → dado_de_baja con observacion "Comprado por empleado"
     * - Dispositivo nuevo    → asignado al empleado
     */
    public function entregar(Request $request, SolicitudEntrega $solicitude): RedirectResponse
    {
        $request->validate([
            'dispositivo_id' => 'required|exists:dispositivos,id',
            'fecha_entrega'  => 'required|date',
            'observaciones'  => 'nullable|string',
        ],['dispositivo_id.required' => 'Debes seleccionar el dispositivo a entregar.']);

        if ($solicitude->estado !== 'pendiente')
            return back()->with('error','Esta solicitud ya fue procesada.');

        $empleado         = $solicitude->empleado;
        $dispositivoNuevo = Dispositivo::findOrFail($request->dispositivo_id);
        $dispAnterior     = $solicitude->dispositivoActual;
        $fechaEntrega     = $request->fecha_entrega;

        // ── 1. Dispositivo anterior → DADO DE BAJA (comprado por empleado) ──
        if ($dispAnterior && $dispAnterior->estado === 'asignado') {
            // Cerrar asignacion en historial
            Asignacion::where('dispositivo_id', $dispAnterior->id)
                ->whereNull('fecha_devolucion')
                ->update([
                    'fecha_devolucion' => $fechaEntrega,
                    'devuelto_por'     => auth()->user()->name,
                    'razon_devolucion' => 'Comprado por el empleado — Boleta: '.$solicitude->numero_boleta,
                ]);

            // Dado de baja con observacion "Comprado"
            $dispAnterior->update([
                'estado'          => 'dado_de_baja',
                'empleado_id'     => null,
                'fecha_asignacion'=> null,
                'observaciones'   => trim(
                    'Comprado por empleado: '.$empleado->nombre_completo.
                    ' | Boleta: '.$solicitude->numero_boleta.
                    ' | Fecha: '.$fechaEntrega.
                    ($dispAnterior->observaciones ? ' | '.$dispAnterior->observaciones : '')
                ),
            ]);
        }

        // ── 2. Dispositivo nuevo → asignado ──────────────────────────────────
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
            'razon_asignacion'=> 'Solicitud N.'.$solicitude->numero_solicitud.' — Boleta: '.$solicitude->numero_boleta,
            'asignado_por'    => auth()->user()->name,
        ]);

        // ── 4. Cerrar solicitud ───────────────────────────────────────────────
        $solicitude->update([
            'estado'        => 'entregado',
            'dispositivo_id'=> $dispositivoNuevo->id,
            'atendido_por'  => auth()->id(),
            'fecha_entrega' => $fechaEntrega,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('solicitudes.show', $solicitude)
            ->with('success', 'Dispositivo entregado. El equipo anterior quedo registrado como Comprado.')
            ->with('mostrar_carta', true);
    }

    public function rechazar(Request $request, SolicitudEntrega $solicitude): RedirectResponse
    {
        $request->validate(['motivo_rechazo' => 'required|string|max:500']);
        $solicitude->update([
            'estado'         => 'rechazado',
            'atendido_por'   => auth()->id(),
            'motivo_rechazo' => $request->motivo_rechazo,
        ]);
        return redirect()->route('solicitudes.index')
            ->with('success','Solicitud rechazada.');
    }
}