@extends('layouts.app')
@section('title','Devolver Dispositivo')@section('page-title','Devolver Dispositivo')
@section('content')
<div class="breadcrumb"><a href="{{ route('asignaciones.index') }}">Asignaciones</a><span class="sep">/</span><span>Devolver</span></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
    <div class="card" style="margin-bottom:0"><div class="card-header"><span class="card-title">Dispositivo</span></div><div class="detail-grid"><div class="detail-item"><label>Equipo</label><p>{{ $dispositivo->nombre_completo }}</p></div><div class="detail-item"><label>N Serie</label><p>{{ $dispositivo->numero_serie }}</p></div><div class="detail-item"><label>Ciclo</label><p style="font-weight:700;color:var(--accent)">{{ $dispositivo->meses_renovacion }} meses</p></div></div></div>
    <div class="card" style="margin-bottom:0"><div class="card-header"><span class="card-title">Asignacion Actual</span></div><div class="detail-grid"><div class="detail-item"><label>Empleado</label><p style="font-weight:600">{{ $asignacion->empleado?->nombre_completo ?? '—' }}</p></div><div class="detail-item"><label>Departamento</label><p>{{ $asignacion->empleado?->departamento ?? '—' }}</p></div><div class="detail-item"><label>F. Asignacion</label><p>{{ $asignacion->fecha_asignacion->format('d/m/Y') }}</p></div><div class="detail-item"><label>Dias asignado</label><p>{{ $asignacion->fecha_asignacion->diffInDays(now()) }} dias</p></div></div></div>
</div>
<div class="card">
    <div class="card-header"><span class="card-title">Registrar Devolucion</span></div>
    <form method="POST" action="{{ route('asignaciones.procesar_devolucion',$dispositivo) }}">@csrf
        <div class="form-grid">
            <div class="form-group"><label>Fecha de Devolucion *</label><input type="date" name="fecha_devolucion" value="{{ old('fecha_devolucion',now()->format('Y-m-d')) }}" required></div>
            <div class="form-group"><label>Devuelto por</label><input type="text" name="devuelto_por" value="{{ old('devuelto_por') }}" placeholder="Responsable de TI"></div>
            <div class="form-group"><label>Razon de devolucion</label><input type="text" name="razon_devolucion" value="{{ old('razon_devolucion') }}" placeholder="Ej: Renovacion, Salida del empleado..."></div>
            <div class="form-group"><label>Nuevo estado del dispositivo *</label><select name="estado_destino" required onchange="mostrarAviso(this)"><option value="disponible" {{ old('estado_destino','disponible')==='disponible'?'selected':'' }}>Disponible (buen estado)</option><option value="en_reparacion" {{ old('estado_destino')==='en_reparacion'?'selected':'' }}>En reparacion</option><option value="dado_de_baja" {{ old('estado_destino')==='dado_de_baja'?'selected':'' }}>Dado de baja</option></select>@error('estado_destino')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group full"><label>Observaciones</label><textarea name="observaciones" placeholder="Estado fisico, accesorios entregados...">{{ old('observaciones') }}</textarea></div>
        </div>
        <div id="aviso-baja" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:14px;margin-top:4px"><p style="color:var(--danger);font-weight:600;font-size:13px">Al marcar como <strong>Dado de baja</strong>, el dispositivo pasara al reporte de Equipos Danados / Baja.</p></div>
        <div class="form-actions" style="margin-top:20px"><button type="submit" class="btn btn-primary">Registrar Devolucion</button><a href="{{ route('asignaciones.index') }}" class="btn btn-secondary">Cancelar</a></div>
    </form>
</div>
@push('scripts')<script>function mostrarAviso(s){document.getElementById('aviso-baja').style.display=s.value==='dado_de_baja'?'block':'none';}document.addEventListener('DOMContentLoaded',()=>mostrarAviso(document.querySelector('[name=estado_destino]')));</script>@endpush
@endsection