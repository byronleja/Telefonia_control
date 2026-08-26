<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Departamento extends Model {
    protected $table = 'departamentos';
    protected $fillable = ['nombre','descripcion','activo'];
    protected $casts = ['activo'=>'boolean'];
    public function empleados(): HasMany { return $this->hasMany(Empleado::class,'departamento','nombre'); }
    public function scopeActivos($q) { return $q->where('activo',true); }
    public function getTotalEmpleadosAttribute(): int { return Empleado::where('departamento',$this->nombre)->count(); }
}