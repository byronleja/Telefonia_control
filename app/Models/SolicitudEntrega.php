<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SolicitudEntrega extends Model
{
    protected $table = 'solicitudes_entrega';

    protected $fillable = [
        'numero_solicitud',
        'empleado_id',
        'dispositivo_id',
        'dispositivo_actual_id',
        'creado_por',
        'atendido_por',
        'estado',
        'numero_boleta',
        'ruta_boleta',
        'nombre_boleta',
        'fecha_solicitud',
        'fecha_entrega',
        'observaciones',
        'motivo_rechazo',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_entrega'   => 'date',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn($s) => $s->numero_solicitud ??= self::generarNumero());
    }

    public static function generarNumero(): string
    {
        $prefix = 'SE-' . now()->format('Y') . '-';
        $ultimo = self::where('numero_solicitud', 'like', "{$prefix}%")
            ->orderByDesc('numero_solicitud')->value('numero_solicitud');
        $sig = $ultimo ? ((int) substr($ultimo, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($sig, 4, '0', STR_PAD_LEFT);
    }

    public function empleado(): BelongsTo      { return $this->belongsTo(Empleado::class); }
    public function dispositivo(): BelongsTo    { return $this->belongsTo(Dispositivo::class); }
    public function dispositivoActual(): BelongsTo { return $this->belongsTo(Dispositivo::class, 'dispositivo_actual_id'); }
    public function creadoPor(): BelongsTo     { return $this->belongsTo(User::class, 'creado_por'); }
    public function atendidoPor(): BelongsTo   { return $this->belongsTo(User::class, 'atendido_por'); }

    public function getUrlBoletaAttribute(): ?string
    {
        return $this->ruta_boleta ? Storage::url($this->ruta_boleta) : null;
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'pendiente'  => 'warning',
            'entregado'  => 'success',
            'rechazado'  => 'danger',
            default      => 'secondary',
        };
    }
}
