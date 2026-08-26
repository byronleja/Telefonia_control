<?php
namespace App\Http\Controllers;

use App\Models\{Asignacion, Configuracion, Dispositivo, Empleado, Renovacion};
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(): View
    {
        $tieneAsignacion = \Illuminate\Support\Facades\Schema::hasColumn('dispositivos','fecha_asignacion');
        $stats = [
            'empleados_activos'        => Empleado::activos()->count(),
            'dispositivos_disponibles' => Dispositivo::disponibles()->count(),
            'dispositivos_asignados'   => Dispositivo::where('estado','asignado')->count(),
            'dispositivos_baja'        => Dispositivo::where('estado','dado_de_baja')->count(),
            'dispositivos_reparacion'  => Dispositivo::where('estado','en_reparacion')->count(),
            'renovaciones_pendientes'  => Renovacion::where('estado','pendiente')->count(),
            'renovaciones_entregadas'  => Renovacion::where('estado','entregada')->count(),
            'alertas_renovacion'       => $tieneAsignacion ? Dispositivo::where('estado','asignado')->get()->filter(fn($d)=>$d->meses_para_renovacion<=2)->count() : 0,
            'total_dispositivos'       => Dispositivo::whereNotIn('estado',['dado_de_baja','en_reparacion'])->count(),
            'dispositivos_comprados'   => Dispositivo::where('estado','dado_de_baja')->where('observaciones','like','Comprado por empleado%')->count(),
        ];

        $mesesGrafica = [];
        for ($i=11; $i>=0; $i--) {
            $d = now()->subMonths($i);
            $total = Renovacion::whereYear('fecha_solicitud',$d->year)->whereMonth('fecha_solicitud',$d->month)->count();
            $mesesGrafica[] = [
                'label'     => $d->format('M Y'),
                'total'     => $total,
                'entregadas'=> Renovacion::whereYear('fecha_solicitud',$d->year)->whereMonth('fecha_solicitud',$d->month)->where('estado','entregada')->count(),
                'pendientes'=> Renovacion::whereYear('fecha_solicitud',$d->year)->whereMonth('fecha_solicitud',$d->month)->where('estado','pendiente')->count(),
            ];
        }

        $porDepartamento = Empleado::join('dispositivos','empleados.id','=','dispositivos.empleado_id')
            ->where('dispositivos.estado','asignado')->selectRaw('empleados.departamento, count(*) as total')
            ->groupBy('empleados.departamento')->orderByDesc('total')->get();

        $topMarcas = Dispositivo::whereNotIn('estado',['dado_de_baja','en_reparacion'])
            ->selectRaw('marca, count(*) as total')->groupBy('marca')->orderByDesc('total')->limit(5)->get();

        $renovacionesRecientes = Renovacion::with('empleado')->latest('fecha_solicitud')->limit(6)->get();

        return view('reportes.index', compact('stats','mesesGrafica','porDepartamento','topMarcas','renovacionesRecientes'));
    }

    public function renovaciones(Request $request): View
    {
        $query = Renovacion::with(['empleado','dispositivoActual','dispositivoNuevo']);
        if ($request->filled('estado'))  $query->where('estado',$request->estado);
        if ($request->filled('ciclo'))   $query->whereHas('dispositivoNuevo',fn($q)=>$q->where('meses_renovacion',$request->ciclo));
        if ($request->filled('destino')) $query->where('destino_equipo_anterior',$request->destino);
        if ($request->filled('anio'))    $query->whereYear('fecha_solicitud',$request->anio);
        if ($request->filled('desde'))   $query->where('fecha_solicitud','>=',$request->desde);
        if ($request->filled('hasta'))   $query->where('fecha_solicitud','<=',$request->hasta);
        $renovaciones = $query->orderByDesc('fecha_solicitud')->paginate(15)->withQueryString();
        $ciclosDisponibles = Renovacion::join('dispositivos','renovaciones.dispositivo_nuevo_id','=','dispositivos.id')->distinct()->pluck('dispositivos.meses_renovacion')->sort()->values();
        $anios = Renovacion::selectRaw('YEAR(fecha_solicitud) as anio')->distinct()->pluck('anio')->sortDesc()->values();
        $totalBaja = Dispositivo::where('estado','dado_de_baja')->count();
        return view('reportes.renovaciones', compact('renovaciones','ciclosDisponibles','anios','totalBaja'));
    }

    public function dispositivos(Request $request): View
    {
        $tieneAsignacion = \Illuminate\Support\Facades\Schema::hasColumn('dispositivos','fecha_asignacion');
        $query = Dispositivo::with('empleado');
        if ($request->filled('estado')) $query->where('estado',$request->estado);
        else $query->whereNotIn('estado',['dado_de_baja','en_reparacion']);
        if ($request->filled('tipo'))   $query->where('tipo',$request->tipo);
        if ($request->filled('marca'))  $query->where('marca','like','%'.$request->marca.'%');
        $dispositivos = $query->orderBy('marca')->paginate(20)->withQueryString();
        return view('reportes.dispositivos', compact('dispositivos','tieneAsignacion'));
    }

    public function alertas(): View
    {
        $tieneAsignacion = \Illuminate\Support\Facades\Schema::hasColumn('dispositivos','fecha_asignacion');
        $dispositivos = $tieneAsignacion
            ? Dispositivo::with('empleado')->where('estado','asignado')->get()->filter(fn($d)=>$d->meses_para_renovacion<=2)->sortBy('meses_para_renovacion')
            : collect();
        return view('reportes.alertas', compact('dispositivos','tieneAsignacion'));
    }

    public function equiposDanados(Request $request): View
    {
        $query = Dispositivo::with('empleado')->whereIn('estado',['dado_de_baja','en_reparacion']);
        // Excluir comprados de este reporte (tienen su propio reporte)
        $query->where(fn($q) => $q->where('observaciones','not like','Comprado por empleado%')->orWhereNull('observaciones'));
        if ($request->filled('estado')) $query->where('estado',$request->estado);
        if ($request->filled('tipo'))   $query->where('tipo',$request->tipo);
        if ($request->filled('marca'))  $query->where('marca','like','%'.$request->marca.'%');
        $totalDadosDeBaja  = Dispositivo::where('estado','dado_de_baja')->where(fn($q)=>$q->where('observaciones','not like','Comprado por empleado%')->orWhereNull('observaciones'))->count();
        $totalEnReparacion = Dispositivo::where('estado','en_reparacion')->count();
        $dispositivos = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();
        return view('reportes.equipos_danados', compact('dispositivos','totalDadosDeBaja','totalEnReparacion'));
    }

    public function comprados(Request $request): View
    {
        $query = Dispositivo::where('estado','dado_de_baja')
            ->where('observaciones','like','Comprado por empleado%');

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(fn($q) => $q
                ->where('numero_serie','like',"%{$b}%")
                ->orWhere('observaciones','like',"%{$b}%")
                ->orWhere('marca','like',"%{$b}%")
                ->orWhere('modelo','like',"%{$b}%")
            );
        }
        if ($request->filled('marca')) $query->where('marca','like','%'.$request->marca.'%');
        if ($request->filled('anio'))  $query->whereYear('updated_at', $request->anio);

        $dispositivos = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();
        $total       = $query->count();
        $valorTotal  = $query->sum('costo');
        $anios       = Dispositivo::where('estado','dado_de_baja')->where('observaciones','like','Comprado por empleado%')->selectRaw('YEAR(updated_at) as anio')->distinct()->pluck('anio')->sortDesc()->values();
        $anio        = $request->filled('anio') ? $request->anio : 'Todos';

        return view('reportes.comprados', compact('dispositivos','total','valorTotal','anios','anio'));
    }
}