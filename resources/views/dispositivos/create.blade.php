@extends('layouts.app')
@section('title','Nuevo Dispositivo')@section('page-title','Nuevo Dispositivo')
@section('content')
<div class="breadcrumb"><a href="{{ route('dispositivos.index') }}">Dispositivos</a><span class="sep">/</span><span>Nuevo</span></div>
<div class="info-box"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
Solo registro del dispositivo. Para asignarlo usa <a href="{{ route('asignaciones.create') }}" style="color:var(--accent);font-weight:600">Asignaciones</a>.</div>
<div class="card">
    <form method="POST" action="{{ route('dispositivos.store') }}">@csrf
        <div class="form-grid">
            <div class="form-group"><label>N Serie *</label><input type="text" name="numero_serie" value="{{ old('numero_serie') }}" required class="{{ $errors->has('numero_serie')?'is-invalid':'' }}">@error('numero_serie')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label>Marca *</label><input type="text" name="marca" value="{{ old('marca') }}" required></div>
            <div class="form-group"><label>Modelo *</label><input type="text" name="modelo" value="{{ old('modelo') }}" required></div>
            <div class="form-group"><label>IMEI</label><input type="text" name="imei" value="{{ old('imei') }}" class="{{ $errors->has('imei')?'is-invalid':'' }}">@error('imei')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label>Tipo *</label><select name="tipo" required>@foreach(['smartphone'=>'Smartphone','basico'=>'Basico','tablet'=>'Tablet'] as $v=>$l)<option value="{{ $v }}" {{ old('tipo','smartphone')===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select></div>
            <div class="form-group"><label>N Telefonico</label><input type="text" name="numero_telefonico" value="{{ old('numero_telefonico') }}"></div>
            <div class="form-group"><label>Operadora</label><input type="text" name="operadora" value="{{ old('operadora') }}" placeholder="Claro, Tigo..."></div>
            <div class="form-group"><label>Color</label><input type="text" name="color" value="{{ old('color') }}"></div>
            <div class="form-group"><label>Fecha de Compra</label><input type="date" name="fecha_compra" value="{{ old('fecha_compra') }}"></div>
            <div class="form-group"><label>Costo (Q)</label><input type="number" name="costo" value="{{ old('costo') }}" step="0.01" min="0"></div>
        </div>
        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:18px;margin-top:16px">
            <p style="font-weight:600;font-size:13px;margin-bottom:4px">Ciclo de renovacion *</p>
            <p style="color:var(--muted);font-size:12px;margin-bottom:14px">Valor global configurado: <strong>{{ $mesesDefault }} meses</strong></p>
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                <div class="form-group" style="max-width:160px"><label>Meses *</label><input type="number" name="meses_renovacion" value="{{ old('meses_renovacion',$mesesDefault) }}" min="1" max="120" required style="font-size:16px;font-weight:700;color:var(--accent);text-align:center" class="{{ $errors->has('meses_renovacion')?'is-invalid':'' }}">@error('meses_renovacion')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div><label>Presets</label><div style="display:flex;gap:8px;margin-top:6px">@foreach([12=>'1 ano',18=>'18 m',24=>'2 anos',36=>'3 anos'] as $m=>$lbl)<button type="button" class="btn btn-secondary btn-sm" onclick="document.querySelector('[name=meses_renovacion]').value={{ $m }}">{{ $lbl }}</button>@endforeach</div></div>
            </div>
        </div>
        <div class="form-group full" style="margin-top:16px"><label>Observaciones</label><textarea name="observaciones">{{ old('observaciones') }}</textarea></div>
        <div class="form-actions"><button type="submit" class="btn btn-primary">Registrar Dispositivo</button><a href="{{ route('dispositivos.index') }}" class="btn btn-secondary">Cancelar</a></div>
    </form>
</div>
@endsection