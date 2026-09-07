<?php
namespace App\Http\Controllers;

use App\Models\{Asignacion, Bloque, Dispositivo, Empleado, Renovacion, SolicitudEntrega};
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Schema;

class ExportController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    private function csvResponse(array $headers, array $rows, string $filename): Response
    {
        $fp = fopen('php://temp', 'r+');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        fputcsv($fp, $headers, ';');
        foreach ($rows as $row) fputcsv($fp, $row, ';');
        rewind($fp);
        $content = stream_get_contents($fp);
        fclose($fp);
        return response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function xlsxResponse(array $headers, array $rows, string $filename, string $titulo = ''): Response
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            return $this->csvResponse($headers, $rows, str_replace('.xlsx', '.csv', $filename));
        }

        $sp = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s  = $sp->getActiveSheet()->setTitle($titulo ?: 'Datos');

        $estiloHeader = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
        ];

        // Headers
        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $s->setCellValue("{$col}1", $h);
            $s->getStyle("{$col}1")->applyFromArray($estiloHeader);
            $s->getColumnDimension($col)->setAutoSize(true);
        }

        // Datos
        foreach ($rows as $ri => $row) {
            foreach (array_values($row) as $ci => $val) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
                $s->setCellValue("{$col}" . ($ri + 2), $val);
            }
            // Alternar color de fila
            if ($ri % 2 === 0) {
                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
                $s->getStyle("A".($ri+2).":{$lastCol}".($ri+2))->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sp);
        ob_start(); $writer->save('php://output'); $content = ob_get_clean();
        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ── EMPLEADOS ─────────────────────────────────────────────────────────────
    public function empleados(Request $request): Response
    {
        $empleados = Empleado::when($request->filled('buscar'), fn($q) => $q
                ->where('nombre','like','%'.$request->buscar.'%')
                ->orWhere('apellido','like','%'.$request->buscar.'%')
                ->orWhere('codigo_empleado','like','%'.$request->buscar.'%'))
            ->when($request->filled('departamento'), fn($q) => $q->where('departamento',$request->departamento))
            ->when($request->filled('estado'), fn($q) => $q->where('estado',$request->estado))
            ->orderBy('apellido')->get();

        $headers = ['Codigo','Nombre','Apellido','Departamento','Cargo','Email','Telefono','Estado'];
        $rows = $empleados->map(fn($e) => [
            $e->codigo_empleado, $e->nombre, $e->apellido,
            $e->departamento, $e->cargo, $e->email,
            $e->telefono ?? '—', ucfirst($e->estado),
        ])->toArray();

        return $this->xlsxResponse($headers, $rows, 'empleados_'.now()->format('Ymd').'.xlsx', 'Empleados');
    }

    // ── DISPOSITIVOS ──────────────────────────────────────────────────────────
    public function dispositivos(Request $request): Response
    {
        $query = Dispositivo::with(['empleado','bloque']);

        if ($request->filled('estado'))    $query->where('estado',$request->estado);
        if ($request->filled('tipo'))      $query->where('tipo',$request->tipo);
        if ($request->filled('marca'))     $query->where('marca','like','%'.$request->marca.'%');
        if ($request->filled('bloque_id')) {
            if ($request->bloque_id === 'sin_bloque') $query->whereNull('bloque_id');
            else $query->where('bloque_id',$request->bloque_id);
        }
        // Tipo baja comprado
        if ($request->filled('tipo_baja') && $request->tipo_baja === 'comprado') {
            $query->where('estado','dado_de_baja')->where('observaciones','like','Comprado por empleado%');
        }

        $dispositivos = $query->orderBy('marca')->get();

        $headers = ['N Serie','Marca','Modelo','IMEI','Tipo','Bloque','Gama','Costo Linea/mes (Q)','Estado','Empleado','Departamento','N Telefonico','Operadora','Fecha Compra','Costo (Q)','Meses Renovacion','Vence Renovacion','Observaciones'];
        $rows = $dispositivos->map(fn($d) => [
            $d->numero_serie, $d->marca, $d->modelo, $d->imei ?? '—',
            ucfirst($d->tipo),
            $d->bloque?->nombre ?? '—',
            $d->bloque?->gama_label ?? '—',
            $d->bloque ? number_format($d->bloque->costo_mensual_linea,2) : '—',
            str_replace('_',' ',ucfirst($d->estado)),
            $d->empleado?->nombre_completo ?? '—',
            $d->empleado?->departamento ?? '—',
            $d->numero_telefonico ?? '—',
            $d->operadora ?? '—',
            $d->fecha_compra?->format('d/m/Y') ?? '—',
            $d->costo ? number_format($d->costo,2) : '—',
            $d->meses_renovacion,
            $d->fecha_vencimiento_renovacion?->format('d/m/Y') ?? '—',
            $d->observaciones ?? '—',
        ])->toArray();

        return $this->xlsxResponse($headers, $rows, 'dispositivos_'.now()->format('Ymd').'.xlsx', 'Dispositivos');
    }

    // ── RENOVACIONES ──────────────────────────────────────────────────────────
    public function renovaciones(Request $request): Response
    {
        $query = Renovacion::with(['empleado','dispositivoActual.bloque','dispositivoNuevo.bloque']);
        if ($request->filled('estado'))  $query->where('estado',$request->estado);
        if ($request->filled('anio'))    $query->whereYear('fecha_solicitud',$request->anio);
        $renovaciones = $query->orderByDesc('fecha_solicitud')->get();

        $headers = ['N Solicitud','Empleado','Departamento','Equipo Anterior','Bloque Anterior','Equipo Nuevo','Bloque Nuevo','Estado','F. Solicitud','F. Entrega','Motivo','Aprobado por','Destino Equipo Ant.'];
        $dm = ['devuelto'=>'Devuelto','comprado'=>'Comprado','no_aplica'=>'No aplica'];
        $rows = $renovaciones->map(fn($r) => [
            $r->numero_solicitud,
            $r->empleado?->nombre_completo ?? '—',
            $r->empleado?->departamento ?? '—',
            $r->dispositivoActual?->nombre_completo ?? '—',
            $r->dispositivoActual?->bloque?->nombre ?? '—',
            $r->dispositivoNuevo?->nombre_completo ?? '—',
            $r->dispositivoNuevo?->bloque?->nombre ?? '—',
            ucfirst($r->estado),
            $r->fecha_solicitud->format('d/m/Y'),
            $r->fecha_entrega?->format('d/m/Y') ?? '—',
            $r->motivo ?? '—',
            $r->aprobado_por ?? '—',
            $dm[$r->destino_equipo_anterior ?? 'no_aplica'],
        ])->toArray();

        return $this->xlsxResponse($headers, $rows, 'renovaciones_'.now()->format('Ymd').'.xlsx', 'Renovaciones');
    }

    // ── ALERTAS ───────────────────────────────────────────────────────────────
    public function alertasRenovacion(): Response
    {
        $dispositivos = Dispositivo::with(['empleado','bloque'])
            ->where('estado','asignado')->get()
            ->filter(fn($d) => $d->meses_para_renovacion <= 2)
            ->sortBy('meses_para_renovacion');

        $headers = ['N Serie','Dispositivo','Bloque','Gama','Empleado','Departamento','F. Asignacion','F. Vencimiento','Meses Restantes','Estado Ciclo'];
        $rows = $dispositivos->map(fn($d) => [
            $d->numero_serie, $d->nombre_completo,
            $d->bloque?->nombre ?? '—',
            $d->bloque?->gama_label ?? '—',
            $d->empleado?->nombre_completo ?? '—',
            $d->empleado?->departamento ?? '—',
            $d->fecha_asignacion?->format('d/m/Y') ?? '—',
            $d->fecha_vencimiento_renovacion?->format('d/m/Y') ?? '—',
            $d->meses_para_renovacion,
            $d->meses_para_renovacion <= 0 ? 'VENCIDO' : 'Por vencer',
        ])->toArray();

        return $this->xlsxResponse($headers, $rows, 'alertas_renovacion_'.now()->format('Ymd').'.xlsx', 'Alertas');
    }

    // ── ASIGNACIONES ACTIVAS ──────────────────────────────────────────────────
    public function asignaciones(Request $request): Response
    {
        $query = Asignacion::with(['empleado','dispositivo.bloque'])->whereNull('fecha_devolucion');
        if ($request->filled('bloque_id')) $query->whereHas('dispositivo',fn($q)=>$q->where('bloque_id',$request->bloque_id));
        if ($request->filled('departamento')) $query->whereHas('empleado',fn($q)=>$q->where('departamento',$request->departamento));
        $asignaciones = $query->orderByDesc('fecha_asignacion')->get();

        $headers = ['Empleado','Codigo','Departamento','Cargo','Dispositivo','Bloque','Gama','Costo Linea/mes (Q)','N Serie','IMEI','F. Asignacion','Asignado por','Razon'];
        $rows = $asignaciones->map(fn($a) => [
            $a->empleado?->nombre_completo ?? '—',
            $a->empleado?->codigo_empleado ?? '—',
            $a->empleado?->departamento ?? '—',
            $a->empleado?->cargo ?? '—',
            $a->dispositivo?->nombre_completo ?? '—',
            $a->dispositivo?->bloque?->nombre ?? '—',
            $a->dispositivo?->bloque?->gama_label ?? '—',
            $a->dispositivo?->bloque ? number_format($a->dispositivo->bloque->costo_mensual_linea,2) : '—',
            $a->dispositivo?->numero_serie ?? '—',
            $a->dispositivo?->imei ?? '—',
            $a->fecha_asignacion->format('d/m/Y'),
            $a->asignado_por ?? '—',
            $a->razon_asignacion ?? '—',
        ])->toArray();

        return $this->xlsxResponse($headers, $rows, 'asignaciones_'.now()->format('Ymd').'.xlsx', 'Asignaciones');
    }

    // ── HISTORIAL EMPLEADO ────────────────────────────────────────────────────
    public function historialEmpleado(Empleado $empleado): Response
    {
        $historial = Asignacion::with('dispositivo.bloque')
            ->where('empleado_id', $empleado->id)
            ->orderByDesc('fecha_asignacion')->get();

        $headers = ['Empleado','Codigo','Departamento','Dispositivo','Bloque','Gama','N Serie','IMEI','Operadora','F. Asignacion','F. Devolucion','Dias Uso','Asignado por','Devuelto por','Razon Asignacion','Razon Devolucion'];
        $rows = $historial->map(fn($a) => [
            $empleado->nombre_completo,
            $empleado->codigo_empleado,
            $empleado->departamento,
            $a->dispositivo?->nombre_completo ?? '—',
            $a->dispositivo?->bloque?->nombre ?? '—',
            $a->dispositivo?->bloque?->gama_label ?? '—',
            $a->dispositivo?->numero_serie ?? '—',
            $a->dispositivo?->imei ?? '—',
            $a->dispositivo?->operadora ?? '—',
            $a->fecha_asignacion->format('d/m/Y'),
            $a->fecha_devolucion?->format('d/m/Y') ?? 'Activo',
            $a->fecha_devolucion ? $a->fecha_asignacion->diffInDays($a->fecha_devolucion) : $a->fecha_asignacion->diffInDays(now()).' (activo)',
            $a->asignado_por ?? '—',
            $a->devuelto_por ?? '—',
            $a->razon_asignacion ?? '—',
            $a->razon_devolucion ?? '—',
        ])->toArray();

        $nombre = str_replace(' ','_',$empleado->nombre_completo);
        return $this->xlsxResponse($headers, $rows, "historial_{$nombre}_".now()->format('Ymd').'.xlsx', 'Historial');
    }

    // ── REPORTE BLOQUES ───────────────────────────────────────────────────────
    public function bloques(Request $request): Response
    {
        $bloques = Bloque::withCount([
            'dispositivos',
            'dispositivos as asignados_count' => fn($q) => $q->where('estado','asignado'),
            'dispositivos as disponibles_count' => fn($q) => $q->where('estado','disponible'),
            'dispositivos as baja_count' => fn($q) => $q->where('estado','dado_de_baja'),
        ])->when($request->filled('gama'), fn($q) => $q->where('gama',$request->gama))
          ->orderBy('gama')->orderBy('nombre')->get();

        $headers = ['Bloque','Gama','Operadora','Costo/linea (Q)','Total Disp.','Asignados','Disponibles','Dados de Baja','Costo Mensual Total (Q)','Costo Anual (Q)'];
        $rows = $bloques->map(fn($b) => [
            $b->nombre, $b->gama_label, $b->operadora ?? '—',
            number_format($b->costo_mensual_linea,2),
            $b->dispositivos_count, $b->asignados_count,
            $b->disponibles_count, $b->baja_count,
            number_format($b->costo_total_mensual,2),
            number_format($b->costo_total_mensual*12,2),
        ])->toArray();

        return $this->xlsxResponse($headers, $rows, 'bloques_'.now()->format('Ymd').'.xlsx', 'Bloques');
    }

    // ── SOLICITUDES ENTREGA ───────────────────────────────────────────────────
    public function solicitudes(Request $request): Response
    {
        $query = SolicitudEntrega::with(['empleado','dispositivoActual.bloque','dispositivo.bloque','creadoPor','atendidoPor']);
        if ($request->filled('estado')) $query->where('estado',$request->estado);
        $solicitudes = $query->orderByDesc('created_at')->get();

        $headers = ['N Solicitud','Empleado','Departamento','Disp. Cobrado','Bloque Cobrado','Disp. Entregado','Bloque Entregado','N Boleta','Estado','F. Solicitud','F. Entrega','Creado por','Atendido por','Observaciones'];
        $rows = $solicitudes->map(fn($s) => [
            $s->numero_solicitud,
            $s->empleado?->nombre_completo ?? '—',
            $s->empleado?->departamento ?? '—',
            $s->dispositivoActual?->nombre_completo ?? '—',
            $s->dispositivoActual?->bloque?->nombre ?? '—',
            $s->dispositivo?->nombre_completo ?? '—',
            $s->dispositivo?->bloque?->nombre ?? '—',
            $s->numero_boleta,
            ucfirst($s->estado),
            $s->fecha_solicitud->format('d/m/Y'),
            $s->fecha_entrega?->format('d/m/Y') ?? '—',
            $s->creadoPor?->name ?? '—',
            $s->atendidoPor?->name ?? '—',
            $s->observaciones ?? '—',
        ])->toArray();

        return $this->xlsxResponse($headers, $rows, 'solicitudes_'.now()->format('Ymd').'.xlsx', 'Solicitudes');
    }
}