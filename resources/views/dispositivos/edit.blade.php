@extends('layouts.app')
@section('title','Editar Dispositivo')@section('page-title','Editar Dispositivo')
@section('content')
<div class="breadcrumb"><a href="{{ route('dispositivos.index') }}">Dispositivos</a><span class="sep">/</span><a href="{{ route('dispositivos.show',$dispositivo) }}">{{ $dispositivo->nombre_completo }}</a><span class="sep">/</span><span>Editar</span></div>
<div class="card">
    <form method="POST" action="{{ route('dispositivos.update',$dispositivo) }}">@csrf @method('PUT')
        <div class="form-grid">
            <div class="form-group"><label>N Serie *</label><input type="text" name="numero_serie" value="{{ old('numero_serie',$dispositivo->numero_serie) }}" required class="{{ $errors->has('numero_serie')?'is-invalid':'' }}">@error('numero_serie')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label>Marca *</label><input type="text" name="marca" value="{{ old('marca',$dispositivo->marca) }}" required></div>
            <div class="form-group"><label>Modelo *</label><input type="text" name="modelo" value="{{ old('modelo',$dispositivo->modelo) }}" required></div>
            <div class="form-group"><label>IMEI</label><input type="text" name="imei" value="{{ old('imei',$dispositivo->imei) }}" class="{{ $errors->has('imei')?'is-invalid':'' }}">@error('imei')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label>Tipo *</label><select name="tipo" required>@foreach(['smartphone'=>'Smartphone','basico'=>'Basico','tablet'=>'Tablet'] as $v=>$l)<option value="{{ $v }}" {{ old('tipo',$dispositivo->tipo)===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select></div>
            <div class="form-group"><label>Estado *</label><select name="estado" required>@foreach(['disponible'=>'Disponible','asignado'=>'Asignado','en_reparacion'=>'En reparacion','dado_de_baja'=>'Dado de baja'] as $v=>$l)<option value="{{ $v }}" {{ old('estado',$dispositivo->estado)===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select><small>Para dar de baja usa el <a href="{{ route('dispositivos.confirmar_baja',$dispositivo) }}" style="color:var(--danger)">formulario de baja</a>.</small></div>
            <div class="form-group"><label>N Telefonico</label><input type="text" name="numero_telefonico" value="{{ old('numero_telefonico',$dispositivo->numero_telefonico) }}"></div>
            <div class="form-group"><label>Operadora</label><input type="text" name="operadora" value="{{ old('operadora',$dispositivo->operadora) }}"></div>
            <div class="form-group"><label>Color</label><input type="text" name="color" value="{{ old('color',$dispositivo->color) }}"></div>
            <div class="form-group"><label>Fecha de Compra</label><input type="date" name="fecha_compra" value="{{ old('fecha_compra',$dispositivo->fecha_compra?->format('Y-m-d')) }}"></div>
            <div class="form-group"><label>Costo (Q)</label><input type="number" name="costo" value="{{ old('costo',$dispositivo->costo) }}" step="0.01" min="0"></div>
        </div>
        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:18px;margin-top:16px">
            <p style="font-weight:600;font-size:13px;margin-bottom:14px">Ciclo de renovacion *</p>
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                <div class="form-group" style="max-width:160px"><label>Meses *</label><input type="number" name="meses_renovacion" value="{{ old('meses_renovacion',$dispositivo->meses_renovacion) }}" min="1" max="120" required style="font-size:16px;font-weight:700;color:var(--accent);text-align:center"></div>
                <div><label>Presets</label><div style="display:flex;gap:8px;margin-top:6px">@foreach([12=>'1 ano',18=>'18 m',24=>'2 anos',36=>'3 anos'] as $m=>$lbl)<button type="button" class="btn btn-secondary btn-sm" onclick="document.querySelector('[name=meses_renovacion]').value={{ $m }}">{{ $lbl }}</button>@endforeach</div></div>
            </div>
        </div>
        <div class="form-group full" style="margin-top:16px"><label>Observaciones</label><textarea name="observaciones">{{ old('observaciones',$dispositivo->observaciones) }}</textarea></div>
        <div class="form-actions"><button type="submit" class="btn btn-primary">Actualizar Dispositivo</button><a href="{{ route('dispositivos.show',$dispositivo) }}" class="btn btn-secondary">Cancelar</a></div>
    </form>
</div>
@endsection