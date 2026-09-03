<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispositivo extends Model
{
    protected $fillable = [
        'numero_serie', 'marca', 'modelo', 'imei', 'tipo',
        'bloque_id',
        'estado', 'numero_telefonico', 'operadora', 'color',
        'fecha_compra', 'fecha_asignacion', 'costo',
        'meses_renovacion', 'empleado_id', 'observaciones',
    ];

    protected $casts = [
        'fecha_compra'    => 'date',
        'fecha_asignacion'=> 'date',
        'costo'           => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function bloque(): BelongsTo
    {
        return $this->belongsTo(Bloque::class);
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class)->orderByDesc('fecha_asignacion');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public static function disponibles()
    {
        return static::where('estado', 'disponible');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->marca . ' ' . $this->modelo);
    }

    public function getFechaVencimientoRenovacionAttribute(): ?\Carbon\Carbon
    {
        if (!$this->fecha_asignacion || !$this->meses_renovacion) return null;
        return $this->fecha_asignacion->addMonths($this->meses_renovacion);
    }

    public function getMesesParaRenovacionAttribute(): int
    {
        if (!$this->fecha_vencimiento_renovacion) return $this->meses_renovacion ?? 0;
        return (int) now()->diffInMonths($this->fecha_vencimiento_renovacion, false);
    }

    public function getEstadoRenovacionAttribute(): string
    {
        $mr = $this->meses_para_renovacion;
        if ($mr <= 0)  return 'Vencido';
        if ($mr <= 2)  return 'Por vencer';
        return 'Vigente';
    }
}
