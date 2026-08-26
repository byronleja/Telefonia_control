<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class Empleado extends Model {
    use SoftDeletes;
    protected $table = 'empleados';
    protected $fillable = ['nombre','apellido','codigo_empleado','departamento','cargo','email','telefono','estado'];
    public function getNombreCompletoAttribute(): string { return "{$this->nombre} {$this->apellido}"; }
    public function dispositivos(): HasMany { return $this->hasMany(Dispositivo::class); }
    public function renovaciones(): HasMany { return $this->hasMany(Renovacion::class); }
    public function asignaciones(): HasMany { return $this->hasMany(Asignacion::class); }
    public function scopeActivos($q) { return $q->where('estado','activo'); }
}