<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;
class Renovacion extends Model {
    use SoftDeletes;
    protected $table = 'renovaciones';
    protected $fillable = ['numero_solicitud','empleado_id','dispositivo_actual_id','dispositivo_nuevo_id','estado','fecha_solicitud','fecha_aprobacion','fecha_entrega','usuario_entrega','motivo','descripcion','observaciones_aprobacion','aprobado_por','destino_equipo_anterior','precio_compra_empleado','notas_destino_equipo'];
    protected $casts = ['fecha_solicitud'=>'date','fecha_aprobacion'=>'date','fecha_entrega'=>'date','precio_compra_empleado'=>'decimal:2'];
    protected static function boot(): void {
        parent::boot();
        static::creating(function(self $r) { $r->numero_solicitud ??= self::generarNumeroSolicitud(); });
    }
    public static function generarNumeroSolicitud(): string {
        $anio = now()->format('Y'); $prefix = "REN-{$anio}-";
        $ultimo = self::withTrashed()->where('numero_solicitud','like',"${prefix}%")->orderByDesc('numero_solicitud')->value('numero_solicitud');
        $sig = $ultimo ? ((int)substr($ultimo,strlen($prefix)))+1 : 1;
        return $prefix.str_pad($sig,4,'0',STR_PAD_LEFT);
    }
    public function empleado(): BelongsTo { return $this->belongsTo(Empleado::class); }
    public function dispositivoActual(): BelongsTo { return $this->belongsTo(Dispositivo::class,'dispositivo_actual_id'); }
    public function dispositivoNuevo(): BelongsTo { return $this->belongsTo(Dispositivo::class,'dispositivo_nuevo_id'); }
    public function adjuntos(): HasMany { return $this->hasMany(Adjunto::class); }
    public function getEstadoBadgeAttribute(): string {
        return match($this->estado) { 'pendiente'=>'warning','aprobada'=>'success','rechazada'=>'danger','entregada'=>'info',default=>'secondary' };
    }
}