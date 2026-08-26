<?php
namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Empleado;
use Illuminate\Http\{RedirectResponse, Request, Response};
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmpleadoImportController extends Controller
{
    public function index(): View
    {
        $departamentos = Departamento::activos()->orderBy('nombre')->get();
        return view('importar.empleados', compact('departamentos'));
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

        if (empty($filas)) {
            return back()->with('error', 'El archivo esta vacio o no tiene datos.');
        }

        [$importados, $duplicados, $errores] = $this->procesar($filas);

        return redirect()->route('importar.empleados')
            ->with('success', "Empleados importados: {$importados}. Duplicados omitidos: {$duplicados}.")
            ->with('errores_importacion', $errores);
    }

    private function procesar(array $filas): array
    {
        $imp = 0; $dup = 0; $err = [];
        $deptosValidos = Departamento::activos()->pluck('nombre')->toArray();

        DB::transaction(function () use ($filas, &$imp, &$dup, &$err, $deptosValidos) {
            foreach ($filas as $i => $fila) {
                if (empty(array_filter($fila))) continue;
                $resultado = $this->procesarFila($fila, $i + 2, $deptosValidos);
                if ($resultado === 'dup_codigo') {
                    $dup++;
                    $err[] = 'Fila ' . ($i + 2) . ': codigo_empleado duplicado, omitido.';
                } elseif ($resultado === 'dup_email') {
                    $dup++;
                    $err[] = 'Fila ' . ($i + 2) . ': email duplicado, omitido.';
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

    private function procesarFila(array $f, int $linea, array $deptosValidos): mixed
    {
        $advertencias = [];

        // Campos obligatorios
        $nombre   = trim($this->val($f, ['nombre', 'nombre *']) ?? '');
        $apellido = trim($this->val($f, ['apellido', 'apellido *']) ?? '');
        $codigo   = trim($this->val($f, ['codigo_empleado', 'codigo *', 'codigo']) ?? '');
        $email    = trim($this->val($f, ['email', 'email *', 'correo']) ?? '');
        $depto    = trim($this->val($f, ['departamento', 'departamento *']) ?? '');
        $cargo    = trim($this->val($f, ['cargo', 'cargo *']) ?? '');

        if (empty($nombre))   return "Fila {$linea}: nombre es obligatorio.";
        if (empty($apellido)) return "Fila {$linea}: apellido es obligatorio.";
        if (empty($codigo))   return "Fila {$linea}: codigo_empleado es obligatorio.";
        if (empty($email))    return "Fila {$linea}: email es obligatorio.";
        if (empty($cargo))    return "Fila {$linea}: cargo es obligatorio.";

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Fila {$linea}: email '{$email}' no es valido.";
        }

        // Verificar duplicados
        if (Empleado::where('codigo_empleado', $codigo)->exists()) return 'dup_codigo';
        if (Empleado::where('email', $email)->exists())            return 'dup_email';

        // Departamento
        if (empty($depto)) {
            $depto = $deptosValidos[0] ?? 'General';
            $advertencias[] = "Fila {$linea}: departamento vacio, se asigno '{$depto}'.";
        } elseif (!in_array($depto, $deptosValidos)) {
            // Crear departamento si no existe
            Departamento::firstOrCreate(['nombre' => $depto], ['activo' => true]);
            $advertencias[] = "Fila {$linea}: departamento '{$depto}' no existia, fue creado.";
        }

        // Estado
        $estado = strtolower(trim($this->val($f, ['estado', 'estado *']) ?? 'activo'));
        if (!in_array($estado, ['activo', 'inactivo'])) {
            $estado = 'activo';
            $advertencias[] = "Fila {$linea}: estado invalido, se uso 'activo'.";
        }

        $telefono = trim($this->val($f, ['telefono', 'telefono *']) ?? '') ?: null;

        Empleado::create([
            'nombre'           => $nombre,
            'apellido'         => $apellido,
            'codigo_empleado'  => $codigo,
            'email'            => $email,
            'departamento'     => $depto,
            'cargo'            => $cargo,
            'telefono'         => $telefono,
            'estado'           => $estado,
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
        $fp  = fopen($path, 'r');
        $raw = fread($fp, 4096); rewind($fp);
        $delimitador = substr_count($raw, ';') >= substr_count($raw, ',') ? ';' : ',';
        $bom = fread($fp, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($fp);

        $rawHeaders = fgetcsv($fp, 0, $delimitador);
        if (!$rawHeaders) { fclose($fp); return []; }

        $headers = array_map(
            fn($h) => mb_strtolower(trim(mb_convert_encoding($h, 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252'))),
            $rawHeaders
        );

        $filas = [];
        while (($row = fgetcsv($fp, 0, $delimitador)) !== false) {
            $fila = [];
            foreach ($headers as $i => $k) {
                $fila[$k] = isset($row[$i])
                    ? mb_convert_encoding($row[$i], 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252')
                    : '';
            }
            $filas[] = $fila;
        }
        fclose($fp);
        return $filas;
    }

    private function leerXlsx(string $path): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            return $this->leerCsv($path);
        }
        $filas = \PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet()->toArray();
        if (empty($filas)) return [];
        $headers = array_map(fn($h) => mb_strtolower(trim((string)$h)), array_shift($filas));
        return array_map(
            fn($row) => array_combine($headers, array_map(fn($v) => (string)($v ?? ''), $row)),
            $filas
        );
    }

    private function csvPlantilla(): Response
    {
        $cols = ['nombre', 'apellido', 'codigo_empleado', 'email', 'departamento', 'cargo', 'telefono', 'estado'];
        $ejemplos = [
            ['Ana', 'Garcia', 'EMP-010', 'ana.garcia@empresa.com', 'Tecnologia e Informatica', 'Analista', '5555-1234', 'activo'],
            ['Luis', 'Perez',  'EMP-011', 'luis.perez@empresa.com',  'Ventas',                   'Ejecutivo', '5555-5678', 'activo'],
        ];
        $fp = fopen('php://temp', 'r+');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fp, $cols, ';');
        foreach ($ejemplos as $row) fputcsv($fp, $row, ';');
        rewind($fp);
        $content = stream_get_contents($fp);
        fclose($fp);

        return response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_empleados.csv"',
        ]);
    }

    private function xlsx(): Response
    {
        $sp = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s  = $sp->getActiveSheet()->setTitle('Empleados');

        $cols = [
            ['nombre',           'Nombre *',            18],
            ['apellido',         'Apellido *',           18],
            ['codigo_empleado',  'Codigo Empleado *',    20],
            ['email',            'Email *',              26],
            ['departamento',     'Departamento *',       24],
            ['cargo',            'Cargo *',              22],
            ['telefono',         'Telefono',             16],
            ['estado',           'Estado (activo/inactivo)', 22],
        ];

        $estilo = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];

        foreach ($cols as $i => [, $lbl, $ancho]) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $s->setCellValue("{$col}1", $lbl);
            $s->getStyle("{$col}1")->applyFromArray($estilo);
            $s->getColumnDimension($col)->setWidth($ancho);
        }

        $ejemplos = [
            ['Ana',  'Garcia', 'EMP-010', 'ana.garcia@empresa.com',  'Tecnologia e Informatica', 'Analista',  '5555-1234', 'activo'],
            ['Luis', 'Perez',  'EMP-011', 'luis.perez@empresa.com',   'Ventas',                   'Ejecutivo', '5555-5678', 'activo'],
        ];

        foreach ($ejemplos as $ri => $fila) {
            foreach ($fila as $ci => $v) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
                $s->setCellValue("{$col}" . ($ri + 2), $v);
            }
        }

        // Hoja auxiliar con departamentos disponibles
        $depts = Departamento::activos()->orderBy('nombre')->pluck('nombre');
        if ($depts->isNotEmpty()) {
            $hoja2 = $sp->createSheet()->setTitle('Departamentos');
            $hoja2->setCellValue('A1', 'Departamentos disponibles');
            $hoja2->getStyle('A1')->applyFromArray($estilo);
            foreach ($depts as $j => $d) {
                $hoja2->setCellValue('A' . ($j + 2), $d);
            }
            $hoja2->getColumnDimension('A')->setWidth(30);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sp);
        ob_start(); $writer->save('php://output'); $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plantilla_empleados.xlsx"',
        ]);
    }
}
