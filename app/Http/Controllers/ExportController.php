<?php
namespace App\Http\Controllers;
use App\Models\{Configuracion,Dispositivo,Empleado,Renovacion};
use Illuminate\Http\{Request,Response};
use Illuminate\Support\Facades\Schema;
class ExportController extends Controller {
    private function csv(array $h,array $r,string $f):Response{ $fp=fopen('php://temp','r+');fprintf($fp,chr(0xEF).chr(0xBB).chr(0xBF));fputcsv($fp,$h,';');foreach($r as $row)fputcsv($fp,$row,';');rewind($fp);$c=stream_get_contents($fp);fclose($fp);return response($c,200,['Content-Type'=>'text/csv; charset=UTF-8','Content-Disposition'=>"attachment; filename=\"{$f}\"",'Cache-Control'=>'max-age=0']); }
    private function excel(array $h,array $r,string $nb):Response{
        if(!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class))return $this->csv($h,$r,"{$nb}.csv");
        $sp=new \PhpOffice\PhpSpreadsheet\Spreadsheet();$s=$sp->getActiveSheet();
        foreach($h as $i=>$t){$c=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i+1);$s->setCellValue("{$c}1",$t);$s->getStyle("{$c}1")->applyFromArray(['font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],'fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'1E3A5F']]]);$s->getColumnDimensionByColumn($i+1)->setAutoSize(true);}
        foreach($r as $ri=>$row){$nr=$ri+2;foreach($row as $ci=>$v){$c=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci+1);$s->setCellValue("{$c}{$nr}",$v);}}
        $w=new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sp);ob_start();$w->save('php://output');$ct=ob_get_clean();
        return response($ct,200,['Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','Content-Disposition'=>"attachment; filename=\"{$nb}.xlsx\"",'Cache-Control'=>'max-age=0']);
    }
    public function empleados():Response{ $h=['Codigo','Nombre','Apellido','Departamento','Cargo','Email','Telefono','Estado'];$rows=Empleado::orderBy('apellido')->get()->map(fn($e)=>[$e->codigo_empleado,$e->nombre,$e->apellido,$e->departamento,$e->cargo,$e->email,$e->telefono??'',ucfirst($e->estado)])->toArray();return $this->excel($h,$rows,'empleados_'.now()->format('Ymd_His')); }
    public function dispositivos(Request $r):Response{ $q=Dispositivo::with('empleado');if($r->filled('estado'))$q->where('estado',$r->estado);$h=['N Serie','Marca','Modelo','IMEI','N Telefonico','Operadora','Tipo','Color','F. Compra','Meses','Costo (Q)','Estado','Empleado'];$rows=$q->orderBy('marca')->get()->map(fn($d)=>[$d->numero_serie,$d->marca,$d->modelo,$d->imei??'',$d->numero_telefonico??'',$d->operadora??'',ucfirst($d->tipo),$d->color??'',$d->fecha_compra?->format('d/m/Y')??'',$d->meses_renovacion,$d->costo??'',str_replace('_',' ',ucfirst($d->estado)),$d->empleado?->nombre_completo??''])->toArray();return $this->excel($h,$rows,'dispositivos_'.now()->format('Ymd_His')); }
    public function renovaciones(Request $r):Response{ $q=Renovacion::with(['empleado','dispositivoActual','dispositivoNuevo']);if($r->filled('estado'))$q->where('estado',$r->estado);$dm=['devuelto'=>'Devuelto','comprado'=>'Comprado','no_aplica'=>'No aplica'];$h=['N Solicitud','Empleado','Departamento','Equipo Anterior','Equipo Nuevo','Estado','F. Solicitud','F. Entrega','Destino Ant.','Motivo'];$rows=$q->orderByDesc('fecha_solicitud')->get()->map(fn($x)=>[$x->numero_solicitud,$x->empleado?->nombre_completo??'',$x->empleado?->departamento??'',$x->dispositivoActual?->nombre_completo??'',$x->dispositivoNuevo?->nombre_completo??'',ucfirst($x->estado),$x->fecha_solicitud?->format('d/m/Y')??'',$x->fecha_entrega?->format('d/m/Y')??'',$dm[$x->destino_equipo_anterior??'no_aplica']??'',$x->motivo??''])->toArray();return $this->excel($h,$rows,'renovaciones_'.now()->format('Ymd_His')); }
    public function alertasRenovacion():Response{
        if(!Schema::hasColumn('dispositivos','fecha_asignacion'))return response('Ejecuta php artisan migrate.',500);
        $u=Configuracion::cicloRenovacionMeses()-Configuracion::alertaAnticipacionMeses();
        $d=Dispositivo::with('empleado')->where('estado','asignado')->whereNotNull('fecha_asignacion')->get()->filter(fn($x)=>$x->fecha_asignacion->addMonths($u)->lte(now()))->sortBy(fn($x)=>$x->meses_para_renovacion)->values();
        $h=['N Serie','Dispositivo','Empleado','Departamento','Meses Ciclo','F. Asignacion','Meses Rest.','Estado'];
        $rows=$d->map(fn($x)=>[$x->numero_serie,$x->nombre_completo,$x->empleado?->nombre_completo??'',$x->empleado?->departamento??'',$x->meses_renovacion,$x->fecha_asignacion->format('d/m/Y'),$x->meses_para_renovacion??0,$x->estado_renovacion])->toArray();
        return $this->excel($h,$rows,'alertas_'.now()->format('Ymd_His'));
    }
}