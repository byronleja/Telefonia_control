@extends('layouts.app')
@section('title','Entrega por Renovacion')
@section('page-title','Entrega de Dispositivo por Renovacion')
@section('content')
<div class="breadcrumb">
    <a href="{{ route('renovaciones.index') }}">Renovaciones</a>
    <span class="sep">/</span><span>Entrega por Renovacion</span>
</div>

@if($empleados->isEmpty() || $dispositivos->isEmpty())
<div style="max-width:580px">
    @if($empleados->isEmpty())
    <div class="alert alert-success">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M5 13l4 4L19 7"/></svg>
        <span>No hay dispositivos vencidos ni proximos a vencer (dentro de los proximos <strong>{{ $mesesAlerta }} mes(es)</strong>). No hay renovaciones pendientes.</span>
    </div>
    @endif
    @if($dispositivos->isEmpty())
    <div class="alert alert-warning">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span>No hay dispositivos disponibles para entregar. Registra un nuevo equipo primero.</span>
    </div>
    @endif
    <a href="{{ route('renovaciones.index') }}" class="btn btn-secondary">Volver a Renovaciones</a>
</div>

@else

{{-- Contador --}}
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);max-width:480px;margin-bottom:20px">
    <div class="stat-card red">
        <div class="stat-value">{{ $empleados->filter(fn($e)=>$e->meses_para_renovacion<=0)->count() }}</div>
        <div class="stat-label">Vencidos</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-value">{{ $empleados->filter(fn($e)=>$e->meses_para_renovacion>0)->count() }}</div>
        <div class="stat-label">Por vencer ({{ $mesesAlerta }}m)</div>
    </div>
    <div class="stat-card green">
        <div class="stat-value">{{ $dispositivos->count() }}</div>
        <div class="stat-label">Disponibles</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

<div class="card" style="margin-bottom:0">
    <div class="card-header"><span class="card-title">Datos de la Entrega</span></div>
    <form method="POST" action="{{ route('renovaciones.entregar.store') }}" id="form-entrega">
        @csrf

        {{-- 1. EMPLEADO — solo con ciclo vencido/por vencer --}}
        <div class="form-group" style="margin-bottom:20px">
            <label>Empleado con renovacion pendiente *</label>
            <select name="empleado_id" id="sel-empleado" required
                    class="{{ $errors->has('empleado_id')?'is-invalid':'' }}"
                    onchange="cargarDatos(this)">
                <option value="">— Seleccionar empleado —</option>
                @foreach($empleados as $e)
                @php
                    $mr   = $e->meses_para_renovacion;
                    $tag  = $mr <= 0 ? '⚠ VENCIDO' : 'Vence en '.$mr.'m';
                @endphp
                <option value="{{ $e->id }}"
                    data-disp-id="{{ $e->dispositivoActual?->id ?? '' }}"
                    data-disp-nombre="{{ $e->dispositivoActual?->nombre_completo ?? '' }}"
                    data-disp-serie="{{ $e->dispositivoActual?->numero_serie ?? '' }}"
                    data-disp-imei="{{ $e->dispositivoActual?->imei ?? '' }}"
                    data-disp-numero="{{ $e->dispositivoActual?->numero_telefonico ?? '' }}"
                    data-disp-meses="{{ $e->dispositivoActual?->meses_renovacion ?? '' }}"
                    data-disp-vence="{{ $e->fecha_vencimiento?->format('d/m/Y') ?? '' }}"
                    data-mr="{{ $mr }}"
                    {{ old('empleado_id')==$e->id?'selected':'' }}>
                    [{{ $tag }}] {{ $e->nombre_completo }} — {{ $e->departamento }}
                </option>
                @endforeach
            </select>
            @error('empleado_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- 2. DISPOSITIVO ACTUAL — auto --}}
        <div id="bloque-actual" style="display:none;margin-bottom:20px">
            <div id="alerta-vencido" style="display:none;margin-bottom:10px"></div>
            <div style="background:var(--surface-2);border:1px solid var(--border-2);border-radius:10px;padding:16px 18px">
                <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);margin-bottom:12px">
                    Dispositivo actual del empleado
                </p>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
                    <div><p style="font-size:11px;color:var(--muted);margin-bottom:3px">Equipo</p><p id="a-nombre" style="font-weight:700;font-size:14px">—</p></div>
                    <div><p style="font-size:11px;color:var(--muted);margin-bottom:3px">N Serie</p><p id="a-serie" style="font-weight:600;font-size:13px;color:var(--text-2)">—</p></div>
                    <div><p style="font-size:11px;color:var(--muted);margin-bottom:3px">N Telefonico</p><p id="a-numero" style="font-size:13px;color:var(--muted)">—</p></div>
                    <div><p style="font-size:11px;color:var(--muted);margin-bottom:3px">IMEI</p><p id="a-imei" style="font-size:12px;color:var(--muted)">—</p></div>
                    <div><p style="font-size:11px;color:var(--muted);margin-bottom:3px">Ciclo</p><p id="a-meses" style="font-weight:700;color:var(--accent)">—</p></div>
                    <div><p style="font-size:11px;color:var(--muted);margin-bottom:3px">Vencio / Vence</p><p id="a-vence" style="font-size:13px;font-weight:600">—</p></div>
                </div>
                <input type="hidden" name="dispositivo_actual_id" id="disp-actual-id">
            </div>
        </div>

        {{-- 3. DISPOSITIVO NUEVO --}}
        <div id="bloque-nuevo" style="display:none;margin-bottom:20px">
            <div class="form-group">
                <label>Dispositivo nuevo a entregar *</label>
                <select name="dispositivo_nuevo_id" id="sel-nuevo" required
                        class="{{ $errors->has('dispositivo_nuevo_id')?'is-invalid':'' }}"
                        onchange="mostrarInfoNuevo(this)">
                    <option value="">— Seleccionar dispositivo disponible —</option>
                    @foreach($dispositivos as $d)
                    <option value="{{ $d->id }}"
                        data-nombre="{{ $d->nombre_completo }}"
                        data-serie="{{ $d->numero_serie }}"
                        data-imei="{{ $d->imei ?? '' }}"
                        data-numero="{{ $d->numero_telefonico ?? '' }}"
                        data-meses="{{ $d->meses_renovacion }}"
                        {{ old('dispositivo_nuevo_id')==$d->id?'selected':'' }}>
                        {{ $d->nombre_completo }} — S/N: {{ $d->numero_serie }}
                        @if($d->numero_telefonico) | Tel: {{ $d->numero_telefonico }}@endif
                        ({{ $d->meses_renovacion }}m)
                    </option>
                    @endforeach
                </select>
                @error('dispositivo_nuevo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror

                <div id="info-nuevo" style="display:none;margin-top:10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 14px">
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px">
                        <div><p style="font-size:11px;color:#166534;margin-bottom:2px">Equipo</p><p id="n-nombre" style="font-weight:700;font-size:13px;color:#14532d">—</p></div>
                        <div><p style="font-size:11px;color:#166534;margin-bottom:2px">N Serie</p><p id="n-serie" style="font-size:13px;color:#14532d">—</p></div>
                        <div><p style="font-size:11px;color:#166534;margin-bottom:2px">N Telefonico</p><p id="n-numero" style="font-size:13px;color:#14532d">—</p></div>
                        <div><p style="font-size:11px;color:#166534;margin-bottom:2px">Ciclo</p><p id="n-meses" style="font-weight:700;font-size:13px;color:var(--accent)">—</p></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. DATOS --}}
        <div id="bloque-datos" style="display:none">
            <div style="border-top:1px solid var(--border);padding-top:20px;margin-top:4px">
                <p style="font-weight:600;font-size:13px;margin-bottom:14px">Datos de la entrega</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Fecha de entrega *</label>
                        <input type="date" name="fecha_entrega" value="{{ old('fecha_entrega',now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Destino equipo anterior *</label>
                        <select name="destino_anterior" required>
                            <option value="devuelto" {{ old('destino_anterior')==='devuelto'?'selected':'' }}>Devuelto — queda disponible</option>
                            <option value="baja"     {{ old('destino_anterior')==='baja'?'selected':'' }}>Dado de baja</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Observaciones</label>
                        <textarea name="observaciones" placeholder="Estado del equipo devuelto, motivo de renovacion...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        Registrar Entrega
                    </button>
                    <a href="{{ route('renovaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>

        <div id="bloque-cancelar">
            <a href="{{ route('renovaciones.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

{{-- Panel lateral --}}
<div style="display:flex;flex-direction:column;gap:16px">
    <div class="card" style="margin-bottom:0;background:#eff6ff;border-color:#bfdbfe">
        <div class="card-header"><span class="card-title" style="color:#1e40af">Flujo</span></div>
        <ol style="padding-left:18px;font-size:13px;color:#1e40af;line-height:2.2">
            <li>Selecciona el <strong>empleado</strong> con ciclo vencido.</li>
            <li>Verifica los datos de su <strong>equipo actual</strong>.</li>
            <li>Elige el <strong>dispositivo nuevo</strong> disponible.</li>
            <li>Define el <strong>destino</strong> del equipo anterior.</li>
            <li>Registra la entrega.</li>
        </ol>
    </div>
    <div class="card" style="margin-bottom:0">
        <div class="card-header"><span class="card-title">Con renovacion pendiente</span></div>
        @foreach($empleados->take(5) as $e)
        @php $mr = $e->meses_para_renovacion; @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border)">
            <span style="font-size:12px;font-weight:500">{{ $e->nombre_completo }}</span>
            <span class="badge badge-{{ $mr<=0?'danger':'warning' }}" style="font-size:10px">
                {{ $mr<=0?'Vencido':'+'.$mr.'m' }}
            </span>
        </div>
        @endforeach
        @if($empleados->count() > 5)
        <p style="font-size:11px;color:var(--muted);margin-top:8px">y {{ $empleados->count()-5 }} mas...</p>
        @endif
    </div>
</div>

</div>
@endif

@push('scripts')
<script>
function cargarDatos(sel) {
    const opt = sel.options[sel.selectedIndex];
    ['bloque-actual','bloque-nuevo','bloque-datos'].forEach(id =>
        document.getElementById(id).style.display = 'none');
    document.getElementById('bloque-cancelar').style.display = 'block';
    document.getElementById('alerta-vencido').style.display  = 'none';

    if (!opt || !opt.value) return;

    // Datos dispositivo actual
    document.getElementById('a-nombre').textContent = opt.dataset.dispNombre || '—';
    document.getElementById('a-serie').textContent  = opt.dataset.dispSerie  || '—';
    document.getElementById('a-imei').textContent   = opt.dataset.dispImei   || '—';
    document.getElementById('a-numero').textContent = opt.dataset.dispNumero || '—';
    document.getElementById('a-meses').textContent  = opt.dataset.dispMeses ? opt.dataset.dispMeses+' meses' : '—';
    document.getElementById('a-vence').textContent  = opt.dataset.dispVence  || '—';
    document.getElementById('disp-actual-id').value = opt.dataset.dispId     || '';

    // Badge vencido o por vencer
    const mr = parseInt(opt.dataset.mr || '0');
    const alerta = document.getElementById('alerta-vencido');
    if (mr <= 0) {
        alerta.innerHTML = '<div class="alert alert-danger" style="margin-bottom:0"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Ciclo <strong>vencido</strong> hace '+Math.abs(mr)+' mes(es). Renovacion urgente.</span></div>';
        alerta.style.display = 'block';
    } else {
        alerta.innerHTML = '<div class="alert alert-warning" style="margin-bottom:0"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Ciclo vence en <strong>'+mr+' mes(es)</strong>.</span></div>';
        alerta.style.display = 'block';
    }

    document.getElementById('bloque-actual').style.display  = 'block';
    document.getElementById('bloque-nuevo').style.display   = 'block';
    document.getElementById('bloque-datos').style.display   = 'block';
    document.getElementById('bloque-cancelar').style.display = 'none';
}

function mostrarInfoNuevo(sel) {
    const opt  = sel.options[sel.selectedIndex];
    const info = document.getElementById('info-nuevo');
    if (!opt || !opt.value) { info.style.display='none'; return; }
    document.getElementById('n-nombre').textContent = opt.dataset.nombre || '—';
    document.getElementById('n-serie').textContent  = opt.dataset.serie  || '—';
    document.getElementById('n-numero').textContent = opt.dataset.numero || '—';
    document.getElementById('n-meses').textContent  = opt.dataset.meses ? opt.dataset.meses+' meses' : '—';
    info.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('sel-empleado');
    if (sel && sel.value) cargarDatos(sel);
    const selN = document.getElementById('sel-nuevo');
    if (selN && selN.value) mostrarInfoNuevo(selN);
});
</script>
@endpush
@endsection
