<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
class Configuracion extends Model {
    protected $table = 'configuraciones';
    protected $fillable = ['clave','valor','descripcion','tipo'];
    private const CACHE_TTL = 600;
    public static function get(string $clave, mixed $default=null): mixed {
        return Cache::remember("config_{$clave}", self::CACHE_TTL, function() use ($clave,$default) {
            $c = static::where('clave',$clave)->first();
            if (!$c) return $default;
            return match($c->tipo) { 'integer'=>(int)$c->valor, 'boolean'=>filter_var($c->valor,FILTER_VALIDATE_BOOLEAN), default=>$c->valor };
        });
    }
    public static function set(string $clave, mixed $valor): void {
        static::updateOrCreate(['clave'=>$clave],['valor'=>(string)$valor]);
        Cache::forget("config_{$clave}");
    }
    public static function cicloRenovacionMeses(): int { return static::get('ciclo_renovacion_meses',18); }
    public static function alertaAnticipacionMeses(): int { return static::get('alerta_anticipacion_meses',2); }
}