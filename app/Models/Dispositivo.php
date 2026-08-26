<?php
namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany,HasOne};
use Illuminate\Database\Eloquent\SoftDeletes;
class Dispositivo extends Model {
    use SoftDeletes;
    protected $table = 'dispositivos';
    protected $fillable = ['numero_serie','marca','modelo','imei','numero_telefonico','operadora','tipo','estado','fecha_compra','fecha_asignacion','meses_renovacion','costo','color','observaciones','empleado_id'];
    protected $casts = ['fecha_compra'=>'date','fecha_asignacion'=>'date','costo'=>'decimal:2','meses_renovacion'=>'integer'];
    public function getNombreCompletoAttribute(): string { return "{$this->marca} {$this->modelo}"; }
    public function getFechaVencimientoRenovacionAttribute(): ?Carbon { return $this->fecha_asignacion?->copy()->addMonths($this->meses_renovacion??Configuracion::cicloRenovacionMeses()); }
    public function getMesesParaRenovacionAttribute(): ?int { if (!$this->fecha_vencimiento_renovacion) return null; return (int)now()->diffInMonths($this->fecha_vencimiento_renovacion,false); }
    public function getEstadoRenovacionAttribute(): string {
        if (!$this->fecha_asignacion) return 'Sin fecha';
        $mr=$this->meses_para_renovacion;
        if ($mr<=0) return 'Vencido';
        if ($mr<=Configuracion::alertaAnticipacionMeses()) return 'Proximo';
        return 'Vigente';
    }
    public function cicloVencido(): bool { return $this->fecha_asignacion && $this->meses_para_renovacion<=0; }
    public function empleado(): BelongsTo { return $this->belongsTo(Empleado::class); }
    public function asignaciones(): HasMany { return $this->hasMany(Asignacion::class)->orderByDesc('fecha_asignacion'); }
    public function asignacionActiva(): HasOne { return $this->hasOne(Asignacion::class)->whereNull('fecha_devolucion')->orderByDesc('fecha_asignacion'); }
    public function renovacionesComoActual(): HasMany { return $this->hasMany(Renovacion::class,'dispositivo_actual_id'); }
    public function renovacionesComoNuevo(): HasMany { return $this->hasMany(Renovacion::class,'dispositivo_nuevo_id'); }
    public function scopeDisponibles($q) { return $q->where('estado','disponible'); }
}