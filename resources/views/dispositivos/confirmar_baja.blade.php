@extends('layouts.app')
@section('title','Confirmar Baja')@section('page-title','Dar de Baja Dispositivo')
@section('content')
<div class="breadcrumb"><a href="{{ route('dispositivos.index') }}">Dispositivos</a><span class="sep">/</span><a href="{{ route('dispositivos.show',$dispositivo) }}">{{ $dispositivo->nombre_completo }}</a><span class="sep">/</span><span>Dar de Baja</span></div>
<div style="background:#fef2f2;border:2px solid #fecaca;border-radius:12px;padding:20px 24px;margin-bottom:24px;display:flex;gap:16px">
    <svg width="28" height="28" fill="none" stroke="var(--danger)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <div><p style="font-weight:700;color:var(--danger);font-size:15px;margin-bottom:6px">Estas a punto de dar de baja este dispositivo</p><p style="color:#7f1d1d;font-size:13px;line-height:1.6">Al darlo de baja dejara de aparecer en el listado principal y pasara al reporte de Equipos Danados / Baja. Un administrador puede restaurarlo si fue un error.</p></div>
</div>
<div class="card">
    <div class="card-header"><span class="card-title">{{ $dispositivo->nombre_completo }}</span><span class="badge badge-{{ $dispositivo->estado==='disponible'?'success':'info' }}">{{ ucfirst($dispositivo->estado) }}</span></div>
    <div class="detail-grid">
        <div class="detail-item"><label>N Serie</label><p>{{ $dispositivo->numero_serie }}</p></div>
        <div class="detail-item"><label>IMEI</label><p>{{ $dispositivo->imei ?? '—' }}</p></div>
        <div class="detail-item"><label>Costo</label><p>{{ $dispositivo->costo ? 'Q '.number_format($dispositivo->costo,2) : '—' }}</p></div>
    </div>
</div>
<div class="card">
    <div class="card-header"><span class="card-title">Completar informacion de baja</span></div>
    <form method="POST" action="{{ route('dispositivos.procesar_baja',$dispositivo) }}">@csrf
        <div class="form-grid">
            <div class="form-group"><label>Fecha de baja *</label><input type="date" name="fecha_baja" value="{{ old('fecha_baja',now()->format('Y-m-d')) }}" required>@error('fecha_baja')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label>Tipo de baja *</label><select name="tipo_baja" required class="{{ $errors->has('tipo_baja')?'is-invalid':'' }}"><option value="">— Seleccionar —</option><option value="danio_fisico" {{ old('tipo_baja')==='danio_fisico'?'selected':'' }}>Dano fisico</option><option value="danio_irreparable" {{ old('tipo_baja')==='danio_irreparable'?'selected':'' }}>Dano irreparable</option><option value="robo_extravio" {{ old('tipo_baja')==='robo_extravio'?'selected':'' }}>Robo o extravio</option><option value="fin_vida_util" {{ old('tipo_baja')==='fin_vida_util'?'selected':'' }}>Fin de vida util</option><option value="otro" {{ old('tipo_baja')==='otro'?'selected':'' }}>Otro</option></select>@error('tipo_baja')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group full"><label>Motivo detallado *</label><input type="text" name="motivo_baja" value="{{ old('motivo_baja') }}" required placeholder="Describe el motivo..." class="{{ $errors->has('motivo_baja')?'is-invalid':'' }}">@error('motivo_baja')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:16px;margin-top:16px">
            <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer;font-size:13px;font-weight:400;letter-spacing:0;text-transform:none;color:var(--text)"><input type="checkbox" name="confirmacion" value="1" style="width:auto;margin-top:2px;flex-shrink:0" class="{{ $errors->has('confirmacion')?'is-invalid':'' }}"><span>Confirmo que el dispositivo <strong>{{ $dispositivo->nombre_completo }} (S/N: {{ $dispositivo->numero_serie }})</strong> debe ser dado de baja.</span></label>
            @error('confirmacion')<div class="invalid-feedback" style="margin-top:8px">{{ $message }}</div>@enderror
        </div>
        <div class="form-actions" style="margin-top:20px"><button type="submit" class="btn btn-danger">Confirmar Dar de Baja</button><a href="{{ route('dispositivos.show',$dispositivo) }}" class="btn btn-secondary">Cancelar</a></div>
    </form>
</div>
@if($dispositivo->estado==='dado_de_baja')
<div class="card" style="background:var(--surface-2)">
    <div class="card-header"><span class="card-title">Restaurar dispositivo</span></div>
    <form method="POST" action="{{ route('dispositivos.restaurar',$dispositivo) }}">@csrf
        <div style="display:flex;gap:10px;align-items:flex-end">
            <div class="form-group" style="flex:1"><label>Motivo de restauracion *</label><input type="text" name="motivo_restauracion" required placeholder="Ej: Error al registrar, equipo recuperado..."></div>
            <button type="submit" class="btn btn-success" onclick="return confirm('Restaurar este dispositivo a disponible?')">Restaurar</button>
        </div>
    </form>
</div>
@endif
@endsection