<?php
namespace App\Http\Controllers;
use App\Models\{Configuracion,Dispositivo,Empleado};
use Illuminate\Http\{RedirectResponse,Request,Response};
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class ImportController extends Controller {
    private const TIPOS=['smartphone','basico','tablet'];
    private const ESTADOS=['disponible','asignado','en_reparacion','dado_de_baja'];
    public function index():View{ return view('importar.index'); }
    public function plantilla():Response{ return class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)?$this->xlsx():$this->csvPlantilla(); }
    public function store(Request $request):RedirectResponse{
        $request->validate(['archivo'=>'required|file|mimes:csv,txt,xlsx|max:5120']);
        $a=$request->file('archivo');$ext=strtolower($a->getClientOriginalExtension());
        try{$filas=$ext==='xlsx'?$this->leerXlsx($a->getPathname()):$this->leerCsv($a->getPathname());}
        catch(\Exception $e){return back()->with('error',"No se pudo leer: {$e->getMessage()}");}
        if(empty($filas))return back()->with('error','Archivo vacio.');
        [$imp,$dup,$err]=$this->procesar($filas);
        return redirect()->route('importar.index')->with('success',"Importados: {$imp}. Duplicados: {$dup}.")->with('errores_importacion',$err);
    }
    private function procesar(array $filas):array{$imp=0;$dup=0;$err=[];DB::transaction(function()use($filas,&$imp,&$dup,&$err){foreach($filas as $i=>$f){if(empty(array_filter($f)))continue;$r=$this->procesarFila($f,$i+2);if($r==='dup'){$dup++;$err[]="Fila ".($i+2).": serie duplicada.";}elseif(is_string($r))$err[]=$r;else{$imp++;array_push($err,...$r);}}});return[$imp,$dup,$err];}
    private function procesarFila(array $f,int $l):mixed{
        $s=trim($this->val($f,['numero_serie','n serie *'])??'');
        if(empty($s))return"Fila {$l}: serie vacia.";
        if(Dispositivo::where('numero_serie',$s)->exists())return'dup';
        $adv=[];
        [$tipo,$wt]=$this->norm($f,['tipo','tipo *'],self::TIPOS,'smartphone',$l,'tipo');[$est,$we]=$this->norm($f,['estado','estado *'],self::ESTADOS,'disponible',$l,'estado');
        if($wt)$adv[]=$wt;if($we)$adv[]=$we;
        $rm=trim($this->val($f,['meses_renovacion','meses renovacion *'])??'');
        $mr=is_numeric($rm)&&(int)$rm>0?(int)$rm:Configuracion::cicloRenovacionMeses();
        if(empty($rm)||!is_numeric($rm))$adv[]="Fila {$l}: meses_renovacion invalido, se uso global ({$mr}).";
        [$eid,$we]=$this->empleado($f,$l);if($we)$adv[]=$we;
        [$fc,$wf]=$this->fecha($f,$l);if($wf)$adv[]=$wf;
        [$imei,$wi]=$this->imei($f,$l);if($wi)$adv[]=$wi;
        Dispositivo::create(['numero_serie'=>$s,'marca'=>trim($this->val($f,['marca','marca *'])??''),'modelo'=>trim($this->val($f,['modelo','modelo *'])??''),'imei'=>$imei,'numero_telefonico'=>trim($this->val($f,['numero_telefonico','n telefonico'])??'')?:null,'operadora'=>trim($f['operadora']??'')?:null,'tipo'=>$tipo,'color'=>trim($f['color']??'')?:null,'fecha_compra'=>$fc,'meses_renovacion'=>$mr,'costo'=>is_numeric($this->val($f,['costo','costo (q)']))?(float)$this->val($f,['costo','costo (q)']):null,'estado'=>$est,'empleado_id'=>$eid,'observaciones'=>trim($f['observaciones']??'')?:null]);
        return $adv;
    }
    private function norm(array $f,array $k,array $v,string $d,int $l,string $n):array{$val=strtolower(trim($this->val($f,$k)??$d));return in_array($val,$v)?[$val,null]:[$d,"Fila {$l}: {$n} invalido, se uso '{$d}'."]; }
    private function empleado(array $f,int $l):array{$c=trim($this->val($f,['codigo_empleado','codigo empleado'])??'');if(empty($c))return[null,null];$e=Empleado::where('codigo_empleado',$c)->first();return $e?[$e->id,null]:[null,"Fila {$l}: codigo '{$c}' no encontrado."];}
    private function fecha(array $f,int $l):array{$r=trim($this->val($f,['fecha_compra','fecha compra (yyyy-mm-dd)'])??'');if(empty($r))return[null,null];try{return[\Carbon\Carbon::parse($r)->format('Y-m-d'),null];}catch(\Exception){return[null,"Fila {$l}: fecha '{$r}' invalida."];}}
    private function imei(array $f,int $l):array{$i=trim($f['imei']??'')?:null;if(!$i)return[null,null];if(Dispositivo::where('imei',$i)->exists())return[null,"Fila {$l}: IMEI '{$i}' duplicado."];return[$i,null];}
    private function val(array $f,array $k):?string{foreach($k as $c){if(isset($f[$c])&&$f[$c]!=='')return $f[$c];}return null;}
    private function leerCsv(string $p):array{$fp=fopen($p,'r');$m=fread($fp,4096);rewind($fp);$d=substr_count($m,';')>=substr_count($m,',');$bom=fread($fp,3);if($bom!=="\xEF\xBB\xBF")rewind($fp);$rh=fgetcsv($fp,0,$d?';':',');if(!$rh){fclose($fp);return[];}$h=array_map(fn($x)=>mb_strtolower(trim(mb_convert_encoding($x,'UTF-8','UTF-8,ISO-8859-1,Windows-1252'))),$rh);$rows=[];while(($r=fgetcsv($fp,0,$d?';':','))!==false){$row=[];foreach($h as $i=>$k)$row[$k]=isset($r[$i])?mb_convert_encoding($r[$i],'UTF-8','UTF-8,ISO-8859-1,Windows-1252'):'';$rows[]=$row;}fclose($fp);return $rows;}
    private function leerXlsx(string $p):array{if(!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class))return $this->leerCsv($p);$rows=\PhpOffice\PhpSpreadsheet\IOFactory::load($p)->getActiveSheet()->toArray();if(empty($rows))return[];$h=array_map(fn($x)=>mb_strtolower(trim((string)$x)),array_shift($rows));return array_map(fn($r)=>array_combine($h,array_map(fn($v)=>(string)($v??''),$r)),$rows);}
    private function csvPlantilla():Response{$cols=['numero_serie','marca','modelo','imei','numero_telefonico','operadora','tipo','color','fecha_compra','costo','estado','codigo_empleado','meses_renovacion','observaciones'];$fp=fopen('php://temp','r+');fprintf($fp,chr(0xEF).chr(0xBB).chr(0xBF));fputcsv($fp,$cols,';');fputcsv($fp,['SN-EJ-001','Samsung','Galaxy A55','352999001','5555-1234','Claro','smartphone','Negro','2024-03-15','2800','disponible','',18,''],';');rewind($fp);$c=stream_get_contents($fp);fclose($fp);return response($c,200,['Content-Type'=>'text/csv; charset=UTF-8','Content-Disposition'=>'attachment; filename="plantilla_dispositivos.csv"']);}
    private function xlsx():Response{$sp=new \PhpOffice\PhpSpreadsheet\Spreadsheet();$s=$sp->getActiveSheet();$s->setTitle('Dispositivos');$cols=[['numero_serie','N Serie *',20],['marca','Marca *',14],['modelo','Modelo *',18],['imei','IMEI',20],['numero_telefonico','N Telefonico',16],['operadora','Operadora',14],['tipo','Tipo *',14],['color','Color',12],['fecha_compra','Fecha Compra (YYYY-MM-DD)',24],['costo','Costo (Q)',12],['estado','Estado *',18],['codigo_empleado','Codigo Empleado',18],['meses_renovacion','Meses Renovacion *',20],['observaciones','Observaciones',30]];$est=['font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],'fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'1E3A5F']],'alignment'=>['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];foreach($cols as $i=>[,$lbl,$ancho]){$c=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i+1);$s->setCellValue("{$c}1",$lbl);$s->getStyle("{$c}1")->applyFromArray($est);$s->getColumnDimension($c)->setWidth($ancho);}$w=new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sp);ob_start();$w->save('php://output');$ct=ob_get_clean();return response($ct,200,['Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','Content-Disposition'=>'attachment; filename="plantilla_dispositivos.xlsx"']);}
}