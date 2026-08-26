<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
class Adjunto extends Model {
    protected $table = 'adjuntos';
    protected $fillable = ['renovacion_id','nombre_original','nombre_archivo','ruta','tipo_mime','tamanio','tipo_adjunto'];
    public function renovacion(): BelongsTo { return $this->belongsTo(Renovacion::class); }
    public function getUrlAttribute(): string { return Storage::url($this->ruta); }
    public function getTamanioFormateadoAttribute(): string {
        return match(true) { $this->tamanio>=1048576=>round($this->tamanio/1048576,2).' MB', $this->tamanio>=1024=>round($this->tamanio/1024,2).' KB', default=>$this->tamanio.' B' };
    }
    public function esImagen(): bool { return str_starts_with($this->tipo_mime,'image/'); }
}