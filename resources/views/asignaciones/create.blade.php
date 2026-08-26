@extends('layouts.app')
@section('title','Nueva Asignacion')@section('page-title','Nueva Asignacion de Dispositivo')
@section('content')
<div class="breadcrumb"><a href="{{ route('asignaciones.index') }}">Asignaciones</a><span class="sep">/</span><span>Nueva</span></div>
<div class="card">
    <form method="POST" action="{{ route('asignaciones.store') }}">@csrf
        <div class="form-grid">
            <div class="form-group"><label>Dispositivo *</label><select name="dispositivo_id" required class="{{ $errors->has('dispositivo_id')?'is-invalid':'' }}"><option value="">— Seleccionar dispositivo disponible —</option>@foreach($dispositivos as $d)<option value="{{ $d->id }}" {{ (old('dispositivo_id',$dispositivoSeleccionado?->id)==$d->id)?'selected':'' }}>{{ $d->nombre_completo }} — {{ $d->numero_serie }} ({{ $d->meses_renovacion }}m)</option>@endforeach</select>@error('dispositivo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror<small>Solo dispositivos en estado Disponible.</small></div>
            <div class="form-group"><label>Empleado *</label><select name="empleado_id" id="sel-empleado" required onchange="verificarEmpleado(this)" class="{{ $errors->has('empleado_id')?'is-invalid':'' }}"><option value="">— Seleccionar empleado —</option>@foreach($empleados as $emp)<option value="{{ $emp->id }}" data-tiene="{{ $emp->dispositivos()->where('estado','asignado')->exists()?'1':'0' }}" data-disp="{{ $emp->dispositivos()->where('estado','asignado')->first()?->nombre_completo??'' }}" data-serie="{{ $emp->dispositivos()->where('estado','asignado')->first()?->numero_serie??'' }}" {{ old('empleado_id')==$emp->id?'selected':'' }}>{{ $emp->nombre_completo }} ({{ $emp->codigo_empleado }}) — {{ $emp->departamento }}</option>@endforeach</select>@error('empleado_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label>Fecha de Asignacion *</label><input type="date" name="fecha_asignacion" value="{{ old('fecha_asignacion',now()->format('Y-m-d')) }}" required></div>
            <div class="form-group"><label>Asignado por</label><input type="text" name="asignado_por" value="{{ old('asignado_por') }}" placeholder="Nombre del responsable de TI"></div>
            <div class="form-group full"><label>Razon de asignacion</label><input type="text" name="razon_asignacion" value="{{ old('razon_asignacion') }}" placeholder="Ej: Ingreso de empleado, Reposicion..."></div>
        </div>
        <div id="alerta-excepcion" style="display:none;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:16px;margin-top:16px">
            <p style="font-weight:700;color:#854d0e;margin-bottom:4px">Este empleado ya tiene un dispositivo asignado:</p>
            <p id="desc-disp-actual" style="color:#92400e;font-size:13px;margin-bottom:14px"></p>
            <div class="form-group" style="max-width:380px"><label>Razon de excepcion *</label><select name="razon_excepcion" class="{{ $errors->has('razon_excepcion')?'is-invalid':'' }}"><option value="">— Seleccionar razon —</option>@foreach($razonesExcepcion as $val=>$lbl)<option value="{{ $val }}" {{ old('razon_excepcion')===$val?'selected':'' }}>{{ $lbl }}</option>@endforeach</select>@error('razon_excepcion')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
        <div class="form-group full" style="margin-top:16px"><label>Observaciones</label><textarea name="observaciones">{{ old('observaciones') }}</textarea></div>
        <div class="form-actions"><button type="submit" class="btn btn-primary">Registrar Asignacion</button><a href="{{ route('asignaciones.index') }}" class="btn btn-secondary">Cancelar</a></div>
    </form>
</div>
@push('scripts')
<script>
function verificarEmpleado(select){
    const opt=select.options[select.selectedIndex];
    const tiene=opt&&opt.dataset.tiene==='1';
    const alerta=document.getElementById('alerta-excepcion');
    const desc=document.getElementById('desc-disp-actual');
    if(tiene){desc.textContent=(opt.dataset.disp||'Dispositivo desconocido')+(opt.dataset.serie?' — S/N: '+opt.dataset.serie:'');alerta.style.display='block';}
    else{alerta.style.display='none';}
}
document.addEventListener('DOMContentLoaded',()=>verificarEmpleado(document.getElementById('sel-empleado')));
</script>
@endpush
@endsection