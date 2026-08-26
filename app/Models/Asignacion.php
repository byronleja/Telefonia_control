<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Asignacion extends Model {
    protected $table = 'asignaciones';
    protected $fillable = ['dispositivo_id','empleado_id','fecha_asignacion','fecha_devolucion','razon_asignacion','razon_devolucion','asignado_por','devuelto_por','observaciones'];
    protected $casts = ['fecha_asignacion'=>'date','fecha_devolucion'=>'date'];
    public function dispositivo(): BelongsTo { return $this->belongsTo(Dispositivo::class); }
    public function empleado(): BelongsTo { return $this->belongsTo(Empleado::class); }
    public function estaActiva(): bool { return is_null($this->fecha_devolucion); }
}