<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bloque extends Model
{
    protected $fillable = [
        'nombre', 'descripcion', 'gama', 'costo_mensual_linea',
        'operadora', 'color_etiqueta', 'activo',
    ];

    protected $casts = [
        'activo'              => 'boolean',
        'costo_mensual_linea' => 'decimal:2',
    ];

    public function dispositivos(): HasMany
    {
        return $this->hasMany(Dispositivo::class);
    }

    public function getGamaLabelAttribute(): string
    {
        return match($this->gama) {
            'alta'   => 'Alta Gama',
            'media'  => 'Media Gama',
            'basica' => 'Basica',
            default  => ucfirst($this->gama),
        };
    }

    public function getGamaBadgeAttribute(): string
    {
        return match($this->gama) {
            'alta'   => 'purple',
            'media'  => 'info',
            'basica' => 'secondary',
            default  => 'secondary',
        };
    }

    public function getCostoTotalMensualAttribute(): float
    {
        return $this->costo_mensual_linea * $this->dispositivos()->where('estado','asignado')->count();
    }

    public static function activos()
    {
        return static::where('activo', true)->orderBy('nombre');
    }
}