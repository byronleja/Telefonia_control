@extends('layouts.app')
@section('title','Nueva Renovacion')@section('page-title','Nueva Solicitud de Renovacion')
@section('content')
<div class="breadcrumb"><a href="{{ route('renovaciones.index') }}">Renovaciones</a><span class="sep">/</span><span>Nueva</span></div>
<div class="card">
    <form method="POST" action="{{ route('renovaciones.store') }}" enctype="multipart/form-data">@csrf
        <div class="form-grid">
            <div class="form-group"><label>Empleado *</label><select name="empleado_id" required class="{{ $errors->has('empleado_id')?'is-invalid':'' }}"><option value="">— Seleccionar —</option>@foreach($empleados as $e)<option value="{{ $e->id }}" {{ old('empleado_id')==$e->id?'selected':'' }}>{{ $e->nombre_completo }} — {{ $e->departamento }}</option>@endforeach</select>@error('empleado_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label>Fecha Solicitud *</label><input type="date" name="fecha_solicitud" value="{{ old('fecha_solicitud',now()->format('Y-m-d')) }}" required></div>
            <div class="form-group"><label>Dispositivo Actual</label><select name="dispositivo_actual_id"><option value="">— Ninguno —</option>@foreach($dispositivosActuales as $d)<option value="{{ $d->id }}" {{ old('dispositivo_actual_id')==$d->id?'selected':'' }}>{{ $d->nombre_completo }} ({{ $d->numero_serie }})</option>@endforeach</select></div>
            <div class="form-group"><label>Dispositivo Nuevo</label><select name="dispositivo_nuevo_id"><option value="">— Ninguno —</option>@foreach($dispositivosNuevos as $d)<option value="{{ $d->id }}" {{ old('dispositivo_nuevo_id')==$d->id?'selected':'' }}>{{ $d->nombre_completo }} ({{ $d->meses_renovacion }}m)</option>@endforeach</select></div>
            <div class="form-group full"><label>Motivo *</label><input type="text" name="motivo" value="{{ old('motivo') }}" required placeholder="Actualizacion tecnologica, Dano fisico...">@error('motivo')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group full"><label>Descripcion</label><textarea name="descripcion">{{ old('descripcion') }}</textarea></div>
        </div>
        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:18px;margin-top:16px">
            <p style="font-weight:600;font-size:13px;margin-bottom:12px">Destino equipo anterior</p>
            <div style="display:flex;gap:20px;flex-wrap:wrap">@foreach(['devuelto'=>'Devuelve el equipo','comprado'=>'Lo compra','no_aplica'=>'No aplica'] as $v=>$l)<label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:400;text-transform:none;letter-spacing:0;cursor:pointer"><input type="radio" name="destino_equipo_anterior" value="{{ $v }}" {{ old('destino_equipo_anterior','no_aplica')===$v?'checked':'' }} style="width:auto;margin:0" onchange="togglePrecio(this)">{{ $l }}</label>@endforeach</div>
            <div id="precio-compra" style="margin-top:12px;display:{{ old('destino_equipo_anterior')==='comprado'?'block':'none' }}"><div class="form-group" style="max-width:200px"><label>Precio de compra (Q)</label><input type="number" name="precio_compra_empleado" value="{{ old('precio_compra_empleado') }}" step="0.01" min="0"></div></div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-primary">Crear Solicitud</button><a href="{{ route('renovaciones.index') }}" class="btn btn-secondary">Cancelar</a></div>
    </form>
</div>
@push('scripts')<script>function togglePrecio(el){document.getElementById('precio-compra').style.display=el.value==='comprado'?'block':'none';}</script>@endpush
@endsection