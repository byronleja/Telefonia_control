@extends('layouts.app')
@section('title','Editar Renovacion')@section('page-title','Editar '.$renovacion->numero_solicitud)
@section('content')
<div class="breadcrumb"><a href="{{ route('renovaciones.index') }}">Renovaciones</a><span class="sep">/</span><a href="{{ route('renovaciones.show',$renovacion) }}">{{ $renovacion->numero_solicitud }}</a><span class="sep">/</span><span>Editar</span></div>
<div class="card">
    <form method="POST" action="{{ route('renovaciones.update',$renovacion) }}" enctype="multipart/form-data">@csrf @method('PUT')
        <div class="form-grid">
            <div class="form-group"><label>N Solicitud</label><input type="text" value="{{ $renovacion->numero_solicitud }}" disabled></div>
            <div class="form-group"><label>Estado *</label><select name="estado" required>@foreach(['pendiente'=>'Pendiente','aprobada'=>'Aprobada','rechazada'=>'Rechazada','entregada'=>'Entregada'] as $v=>$l)<option value="{{ $v }}" {{ old('estado',$renovacion->estado)===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select></div>
            <div class="form-group"><label>Empleado *</label><select name="empleado_id" required>@foreach($empleados as $e)<option value="{{ $e->id }}" {{ old('empleado_id',$renovacion->empleado_id)==$e->id?'selected':'' }}>{{ $e->nombre_completo }}</option>@endforeach</select></div>
            <div class="form-group"><label>Fecha Solicitud *</label><input type="date" name="fecha_solicitud" value="{{ old('fecha_solicitud',$renovacion->fecha_solicitud->format('Y-m-d')) }}" required></div>
            <div class="form-group"><label>Dispositivo Actual</label><select name="dispositivo_actual_id"><option value="">— Ninguno —</option>@foreach($dispositivosActuales as $d)<option value="{{ $d->id }}" {{ old('dispositivo_actual_id',$renovacion->dispositivo_actual_id)==$d->id?'selected':'' }}>{{ $d->nombre_completo }} ({{ $d->numero_serie }})</option>@endforeach</select></div>
            <div class="form-group"><label>Dispositivo Nuevo</label><select name="dispositivo_nuevo_id"><option value="">— Ninguno —</option>@foreach($dispositivosNuevos as $d)<option value="{{ $d->id }}" {{ old('dispositivo_nuevo_id',$renovacion->dispositivo_nuevo_id)==$d->id?'selected':'' }}>{{ $d->nombre_completo }} ({{ $d->meses_renovacion }}m)</option>@endforeach</select></div>
            <div class="form-group"><label>Fecha Aprobacion</label><input type="date" name="fecha_aprobacion" value="{{ old('fecha_aprobacion',$renovacion->fecha_aprobacion?->format('Y-m-d')) }}"></div>
            <div class="form-group"><label>Aprobado por</label><input type="text" name="aprobado_por" value="{{ old('aprobado_por',$renovacion->aprobado_por) }}"></div>
            <div class="form-group"><label>Fecha Entrega</label><input type="date" name="fecha_entrega" value="{{ old('fecha_entrega',$renovacion->fecha_entrega?->format('Y-m-d')) }}"></div>
            <div class="form-group"><label>Entregado por</label><input type="text" name="usuario_entrega" value="{{ old('usuario_entrega',$renovacion->usuario_entrega) }}"></div>
            <div class="form-group full"><label>Motivo *</label><input type="text" name="motivo" value="{{ old('motivo',$renovacion->motivo) }}" required></div>
            <div class="form-group full"><label>Descripcion</label><textarea name="descripcion">{{ old('descripcion',$renovacion->descripcion) }}</textarea></div>
            <div class="form-group full"><label>Observaciones aprobacion</label><textarea name="observaciones_aprobacion">{{ old('observaciones_aprobacion',$renovacion->observaciones_aprobacion) }}</textarea></div>
        </div>
        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:18px;margin-top:16px">
            <p style="font-weight:600;font-size:13px;margin-bottom:12px">Destino equipo anterior</p>
            <div style="display:flex;gap:20px;flex-wrap:wrap">@foreach(['devuelto'=>'Devuelve el equipo','comprado'=>'Lo compra','no_aplica'=>'No aplica'] as $v=>$l)<label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:400;text-transform:none;letter-spacing:0;cursor:pointer"><input type="radio" name="destino_equipo_anterior" value="{{ $v }}" {{ old('destino_equipo_anterior',$renovacion->destino_equipo_anterior??'no_aplica')===$v?'checked':'' }} style="width:auto;margin:0" onchange="togglePrecio(this)">{{ $l }}</label>@endforeach</div>
            <div id="precio-compra" style="margin-top:12px;display:{{ old('destino_equipo_anterior',$renovacion->destino_equipo_anterior)==='comprado'?'block':'none' }}"><div class="form-group" style="max-width:200px"><label>Precio de compra (Q)</label><input type="number" name="precio_compra_empleado" value="{{ old('precio_compra_empleado',$renovacion->precio_compra_empleado) }}" step="0.01" min="0"></div></div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-primary">Guardar Cambios</button><a href="{{ route('renovaciones.show',$renovacion) }}" class="btn btn-secondary">Cancelar</a></div>
    </form>
</div>
@push('scripts')<script>function togglePrecio(el){document.getElementById('precio-compra').style.display=el.value==='comprado'?'block':'none';}</script>@endpush
@endsection