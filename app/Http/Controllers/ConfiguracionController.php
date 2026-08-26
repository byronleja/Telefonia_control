<?php
namespace App\Http\Controllers;
use App\Models\Configuracion;
use Illuminate\Http\{RedirectResponse,Request};
use Illuminate\View\View;
class ConfiguracionController extends Controller {
    private array $claves=['ciclo_renovacion_meses','alerta_anticipacion_meses','nombre_empresa','departamento_sistemas'];
    public function index(): View { $configs=Configuracion::whereIn('clave',$this->claves)->get()->keyBy('clave'); return view('configuracion.index',compact('configs')); }
    public function update(Request $request): RedirectResponse {
        $request->validate(['ciclo_renovacion_meses'=>'required|integer|min:1|max:120','alerta_anticipacion_meses'=>'required|integer|min:1|max:24','nombre_empresa'=>'required|string|max:150','departamento_sistemas'=>'required|string|max:150']);
        foreach($this->claves as $c) Configuracion::set($c,$request->input($c));
        return back()->with('success','Configuracion guardada correctamente.');
    }
}