<?php
namespace App\Http\Controllers;

use App\Models\{Bloque, Configuracion, Dispositivo};
use Illuminate\Http\{RedirectResponse, Request, Response};
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(): View
    {
        $bloques = Bloque::activos()->get();
        return view('importar.index', compact('bloques'));
    }

    public function plantilla(): Response
    {
        return class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)
            ? $this->xlsx()
            : $this->csvPlantilla();
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt,xlsx|max:5120',
        ]);

        $archivo = $request->file('archivo');
        $ext     = strtolower($archivo->getClientOriginalExtension());

        try {
            $filas = $ext === 'xlsx'
                ? $this->leerXlsx($archivo->getPathname())
                : $this->leerCsv($archivo->getPathname());
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo leer el archivo: ' . $e->getMessage());
        }

        if (empty($filas)) return back()->with('error', 'El archivo esta vacio.');

        [$importados, $duplicados, $errores] = $this->procesar($filas);

        return redirect()->route('importar.index')
            ->with('success', "Dispositivos importados: {$importados}. Duplicados omitidos: {$duplicados}.")
            ->with('errores_importacion', $errores);
    }

    private function procesar(array $filas): array
    {
        $imp = 0; $dup = 0; $err = [];
        $mesesDefault = Configuracion::where('clave','ciclo_renovacion_meses')->value('valor') ?? 18;

        // Cargar bloques en memoria (nombre => id)
        $bloquesMap = Bloque::activos()->get()->keyBy(fn($b) => strtolower(trim($b->nombre)));

        DB::transaction(function() use ($filas, &$imp, &$dup, &$err, $mesesDefault, $bloquesMap) {
            foreach ($filas as $i => $fila) {
                if (empty(array_filter($fila))) continue;
                $resultado = $this->procesarFila($fila, $i + 2, $mesesDefault, $bloquesMap);
                if ($resultado === 'dup') {
                    $dup++;
                    $err[] = 'Fila ' . ($i + 2) . ': numero_serie duplicado, omitido.';
                } elseif (is_string($resultado)) {
                    $err[] = $resultado;
                } else {
                    $imp++;
                    array_push($err, ...$resultado);
                }
            }
        });

        return [$imp, $dup, $err];
    }

    private function procesarFila(array $f, int $linea, int $mesesDefault, $bloquesMap): mixed
    {
        $advertencias = [];

        $serie  = trim($this->val($f,['numero_serie','serie','n_serie']) ?? '');
        $marca  = trim($this->val($f,['marca']) ?? '');
        $modelo = trim($this->val($f,['modelo']) ?? '');
        $tipo   = strtolower(trim($this->val($f,['tipo']) ?? 'smartphone'));

        if (empty($serie))  return "Fila {$linea}: numero_serie es obligatorio.";
        if (empty($marca))  return "Fila {$linea}: marca es obligatoria.";
        if (empty($modelo)) return "Fila {$linea}: modelo es obligatorio.";

        if (Dispositivo::where('numero_serie',$serie)->exists()) return 'dup';

        if (!in_array($tipo,['smartphone','basico','tablet'])) {
            $tipo = 'smartphone';
            $advertencias[] = "Fila {$linea}: tipo invalido, se uso 'smartphone'.";
        }

        // Bloque
        $nombreBloque = strtolower(trim($this->val($f,['bloque','nombre_bloque','bloque_id']) ?? ''));
        $bloqueId     = null;

        if (!empty($nombreBloque)) {
            if (isset($bloquesMap[$nombreBloque])) {
                $bloqueId = $bloquesMap[$nombreBloque]->id;
            } else {
                return "Fila {$linea}: bloque '{$nombreBloque}' no existe en el sistema. Dispositivo NO importado. Verifica el nombre exacto del bloque.";
            }
        } else {
            return "Fila {$linea}: la columna 'bloque' es obligatoria. Dispositivo NO importado.";
        }

        $meses  = (int)($this->val($f,['meses_renovacion','meses','ciclo']) ?? $mesesDefault);
        if ($meses < 1 || $meses > 120) $meses = $mesesDefault;

        $estado = strtolower(trim($this->val($f,['estado']) ?? 'disponible'));
        if (!in_array($estado,['disponible','asignado','en_reparacion','dado_de_baja'])) $estado = 'disponible';

        $imei = trim($this->val($f,['imei']) ?? '') ?: null;
        if ($imei && Dispositivo::where('imei',$imei)->exists()) {
            $imei = null;
            $advertencias[] = "Fila {$linea}: IMEI duplicado, se registro sin IMEI.";
        }

        Dispositivo::create([
            'numero_serie'      => $serie,
            'marca'             => $marca,
            'modelo'            => $modelo,
            'tipo'              => $tipo,
            'bloque_id'         => $bloqueId,
            'imei'              => $imei,
            'numero_telefonico' => trim($this->val($f,['numero_telefonico','numero','telefono']) ?? '') ?: null,
            'operadora'         => trim($this->val($f,['operadora']) ?? '') ?: null,
            'color'             => trim($this->val($f,['color']) ?? '') ?: null,
            'fecha_compra'      => $this->parsearFecha(trim($this->val($f,['fecha_compra','fecha']) ?? '')),
            'costo'             => is_numeric($this->val($f,['costo','precio'])) ? (float)$this->val($f,['costo','precio']) : null,
            'meses_renovacion'  => $meses,
            'estado'            => $estado,
        ]);

        return $advertencias;
    }

    private function val(array $fila, array $claves): ?string
    {
        foreach ($claves as $clave) {
            if (isset($fila[$clave]) && $fila[$clave] !== '') return $fila[$clave];
        }
        return null;
    }

    private function leerCsv(string $path): array
    {
        $fp  = fopen($path,'r');
        $raw = fread($fp,4096); rewind($fp);
        $del = substr_count($raw,';') >= substr_count($raw,',') ? ';' : ',';
        $bom = fread($fp,3);
        if ($bom !== "\xEF\xBB\xBF") rewind($fp);
        $rawHeaders = fgetcsv($fp,0,$del);
        if (!$rawHeaders) { fclose($fp); return []; }
        $headers = array_map(fn($h) => mb_strtolower(trim(mb_convert_encoding($h,'UTF-8','UTF-8,ISO-8859-1,Windows-1252'))), $rawHeaders);
        $filas = [];
        while (($row = fgetcsv($fp,0,$del)) !== false) {
            $fila = [];
            foreach ($headers as $i => $k) $fila[$k] = isset($row[$i]) ? mb_convert_encoding($row[$i],'UTF-8','UTF-8,ISO-8859-1,Windows-1252') : '';
            $filas[] = $fila;
        }
        fclose($fp);
        return $filas;
    }

    private function leerXlsx(string $path): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) return $this->leerCsv($path);
        $filas = \PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet()->toArray();
        if (empty($filas)) return [];
        $headers = array_map(fn($h) => mb_strtolower(trim((string)$h)), array_shift($filas));
        return array_map(fn($row) => array_combine($headers, array_map(fn($v) => (string)($v ?? ''), $row)), $filas);
    }

    private function csvPlantilla(): Response
    {
        $bloques = Bloque::activos()->pluck('nombre')->implode(', ');
        $cols    = ['numero_serie','marca','modelo','tipo','bloque','imei','numero_telefonico','operadora','color','fecha_compra','costo','meses_renovacion','estado'];
        $ejemplo = ['SN-001','Samsung','Galaxy S24','smartphone','Bloque A','352999001234567','5555-1234','Claro','Negro','2026-01-15','3500','18','disponible'];
        $fp = fopen('php://temp','r+');
        fprintf($fp,chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($fp,$cols,';');
        fputcsv($fp,$ejemplo,';');
        rewind($fp);
        $content = stream_get_contents($fp);
        fclose($fp);
        return response($content,200,[
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_dispositivos.csv"',
        ]);
    }

    private function xlsx(): Response
    {
        $sp = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s  = $sp->getActiveSheet()->setTitle('Dispositivos');
        $estilo = ['font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],'fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'1E3A5F']],'alignment'=>['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];
        $cols = [['numero_serie','N Serie *',18],['marca','Marca *',16],['modelo','Modelo *',20],['tipo','Tipo * (smartphone/basico/tablet)',24],['bloque','Bloque *',22],['imei','IMEI',18],['numero_telefonico','N Telefonico',16],['operadora','Operadora',14],['color','Color',12],['fecha_compra','F. Compra (YYYY-MM-DD)',20],['costo','Costo (Q)',12],['meses_renovacion','Meses Renovacion',18],['estado','Estado',16]];
        foreach ($cols as $i => [$campo,$lbl,$ancho]) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i+1);
            $s->setCellValue("{$col}1",$lbl);
            $s->getStyle("{$col}1")->applyFromArray($estilo);
            $s->getColumnDimension($col)->setWidth($ancho);
        }
        $ejemplo = ['SN-001','Samsung','Galaxy S24','smartphone','Bloque A','352999001234567','5555-1234','Claro','Negro','2026-01-15','3500','18','disponible'];
        foreach ($ejemplo as $ci => $v) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci+1);
            $s->setCellValue("{$col}2",$v);
        }
        // Hoja bloques
        $bloques = Bloque::activos()->get();
        if ($bloques->isNotEmpty()) {
            $h2 = $sp->createSheet()->setTitle('Bloques');
            $h2->setCellValue('A1','Nombre del Bloque');
            $h2->setCellValue('B1','Gama');
            $h2->setCellValue('C1','Costo mensual/linea');
            $h2->getStyle('A1:C1')->applyFromArray($estilo);
            foreach ($bloques as $j => $b) {
                $h2->setCellValue('A'.($j+2),$b->nombre);
                $h2->setCellValue('B'.($j+2),$b->gama_label);
                $h2->setCellValue('C'.($j+2),'Q '.number_format($b->costo_mensual_linea,2));
            }
            $h2->getColumnDimension('A')->setWidth(24);
            $h2->getColumnDimension('B')->setWidth(16);
            $h2->getColumnDimension('C')->setWidth(20);
        }
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sp);
        ob_start(); $writer->save('php://output'); $content = ob_get_clean();
        return response($content,200,[
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plantilla_dispositivos.xlsx"',
        ]);
    }

    /**
     * Parsea fechas en multiples formatos: Y-m-d, d/m/Y, d-m-Y, m/d/Y
     */
    private function parsearFecha(string $valor): ?string
    {
        if (empty($valor)) return null;

        // Intentar formatos comunes
        $formatos = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y', 'd/m/y', 'd-m-y'];
        foreach ($formatos as $formato) {
            $fecha = \DateTime::createFromFormat($formato, trim($valor));
            if ($fecha && $fecha->format($formato === 'Y-m-d' ? 'Y-m-d' : $formato) === trim($valor)) {
                return $fecha->format('Y-m-d');
            }
        }

        // Ultimo intento con strtotime
        $ts = strtotime($valor);
        if ($ts !== false) return date('Y-m-d', $ts);

        return null; // Si no se pudo parsear, ignorar la fecha
    }

}