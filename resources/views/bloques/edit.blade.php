@extends('layouts.app')
@section('title','Editar Bloque')
@section('page-title','Editar Bloque')
@section('content')
<div class="breadcrumb"><a href="{{ route('bloques.index') }}">Bloques</a><span class="sep">/</span><span>{{ $bloque->nombre }}</span></div>
@if($totalDispositivos > 0)
<div class="alert alert-info">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>Este bloque tiene <strong>{{ $totalDispositivos }}</strong> dispositivo(s). Los cambios de gama y costo afectaran el reporte inmediatamente.</span>
</div>
@endif
<div class="card" style="max-width:580px">
    <form method="POST" action="{{ route('bloques.update',$bloque) }}">@csrf @method('PUT')
        <div class="form-grid">
            <div class="form-group full">
                <label>Nombre del Bloque *</label>
                <input type="text" name="nombre" value="{{ old('nombre',$bloque->nombre) }}" required
                       class="{{ $errors->has('nombre')?'is-invalid':'' }}">
                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group full">
                <label>Descripcion</label>
                <input type="text" name="descripcion" value="{{ old('descripcion',$bloque->descripcion) }}">
            </div>
            <div class="form-group">
                <label>Gama *</label>
                <select name="gama" required>
                    @foreach(['alta'=>'Alta Gama','media'=>'Media Gama','basica'=>'Basica'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('gama',$bloque->gama)===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Costo mensual con linea (Q) *</label>
                <input type="number" name="costo_mensual_linea"
                       value="{{ old('costo_mensual_linea',$bloque->costo_mensual_linea) }}"
                       step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label>Operadora</label>
                <input type="text" name="operadora" value="{{ old('operadora',$bloque->operadora) }}">
            </div>
            <div class="form-group">
                <label>Color de etiqueta</label>
                <div style="display:flex;gap:10px;align-items:center">
                    <input type="color" name="color_etiqueta"
                           value="{{ old('color_etiqueta',$bloque->color_etiqueta) }}"
                           style="width:48px;height:38px;padding:2px;cursor:pointer">
                    <span style="font-size:12px;color:var(--muted)">Color del bloque en la UI</span>
                </div>
            </div>
            <div class="form-group full">
                <label style="display:flex;align-items:center;gap:10px;font-size:13px;font-weight:400;text-transform:none;letter-spacing:0;cursor:pointer">
                    <input type="checkbox" name="activo" value="1"
                           {{ old('activo',$bloque->activo?'1':'0')==='1'?'checked':'' }} style="width:auto;margin:0">
                    <span>Bloque activo</span>
                </label>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="{{ route('bloques.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@if($totalDispositivos === 0)
<div class="card" style="max-width:580px;background:var(--surface-2);margin-top:0">
    <p style="font-size:13px;color:var(--muted);margin-bottom:14px">Este bloque no tiene dispositivos. Puede eliminarlo.</p>
    <form method="POST" action="{{ route('bloques.destroy',$bloque) }}" onsubmit="return confirm('Eliminar este bloque?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">Eliminar Bloque</button>
    </form>
</div>
@endif
@endsection