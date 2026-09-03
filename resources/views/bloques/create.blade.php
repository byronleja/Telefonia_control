@extends('layouts.app')
@section('title','Nuevo Bloque')
@section('page-title','Nuevo Bloque')
@section('content')
<div class="breadcrumb"><a href="{{ route('bloques.index') }}">Bloques</a><span class="sep">/</span><span>Nuevo</span></div>
<div class="card" style="max-width:580px">
    <form method="POST" action="{{ route('bloques.store') }}">@csrf
        <div class="form-grid">
            <div class="form-group full">
                <label>Nombre del Bloque *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required
                       placeholder="Ej: Bloque A, Ejecutivo, Gerencia..."
                       class="{{ $errors->has('nombre')?'is-invalid':'' }}">
                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group full">
                <label>Descripcion</label>
                <input type="text" name="descripcion" value="{{ old('descripcion') }}"
                       placeholder="Breve descripcion del bloque">
            </div>
            <div class="form-group">
                <label>Gama *</label>
                <select name="gama" required>
                    <option value="">— Seleccionar —</option>
                    <option value="alta"   {{ old('gama')==='alta'?'selected':'' }}>Alta Gama</option>
                    <option value="media"  {{ old('gama')==='media'?'selected':'' }}>Media Gama</option>
                    <option value="basica" {{ old('gama')==='basica'?'selected':'' }}>Basica</option>
                </select>
            </div>
            <div class="form-group">
                <label>Costo mensual con linea (Q) *</label>
                <input type="number" name="costo_mensual_linea"
                       value="{{ old('costo_mensual_linea','0') }}"
                       step="0.01" min="0" required>
                <small>Costo mensual por dispositivo incluyendo linea telefonica.</small>
            </div>
            <div class="form-group">
                <label>Operadora</label>
                <input type="text" name="operadora" value="{{ old('operadora') }}"
                       placeholder="Claro, Tigo, Movistar...">
            </div>
            <div class="form-group">
                <label>Color de etiqueta</label>
                <div style="display:flex;gap:10px;align-items:center">
                    <input type="color" name="color_etiqueta"
                           value="{{ old('color_etiqueta','#2563eb') }}"
                           style="width:48px;height:38px;padding:2px;cursor:pointer">
                    <span style="font-size:12px;color:var(--muted)">Color para identificar el bloque en la UI</span>
                </div>
            </div>
            <div class="form-group full">
                <label style="display:flex;align-items:center;gap:10px;font-size:13px;font-weight:400;text-transform:none;letter-spacing:0;cursor:pointer">
                    <input type="checkbox" name="activo" value="1"
                           {{ old('activo','1')==='1'?'checked':'' }} style="width:auto;margin:0">
                    <span>Bloque activo</span>
                </label>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Crear Bloque</button>
            <a href="{{ route('bloques.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection