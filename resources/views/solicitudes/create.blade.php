@extends('layouts.app')
@section('title','Nueva Solicitud de Entrega')
@section('page-title','Nueva Solicitud de Entrega')
@section('content')
<div class="breadcrumb">
    <a href="{{ route('solicitudes.index') }}">Solicitudes</a>
    <span class="sep">/</span><span>Nueva</span>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

{{-- Formulario --}}
<div class="card" style="margin-bottom:0">
    <div class="card-header"><span class="card-title">Datos de la Solicitud</span></div>

    <form method="POST" action="{{ route('solicitudes.store') }}" enctype="multipart/form-data" id="form-solicitud">
        @csrf

        {{-- 1. EMPLEADO --}}
        <div class="form-group" style="margin-bottom:20px">
            <label>Empleado que realiza el pago *</label>
            <select name="empleado_id" id="sel-empleado" required
                    class="{{ $errors->has('empleado_id')?'is-invalid':'' }}"
                    onchange="cargarDispositivo(this)">
                <option value="">— Seleccionar empleado —</option>
                @foreach($empleados as $e)
                <option value="{{ $e->id }}"
                    data-tiene="{{ $e->dispositivoActual ? '1' : '0' }}"
                    data-disp-id="{{ $e->dispositivoActual?->id ?? '' }}"
                    data-disp-nombre="{{ $e->dispositivoActual?->nombre_completo ?? '' }}"
                    data-disp-serie="{{ $e->dispositivoActual?->numero_serie ?? '' }}"
                    data-disp-imei="{{ $e->dispositivoActual?->imei ?? '' }}"
                    {{ old('empleado_id')==$e->id?'selected':'' }}>
                    {{ $e->nombre_completo }} — {{ $e->departamento }}
                </option>
                @endforeach
            </select>
            @error('empleado_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- 2. DISPOSITIVO ACTUAL — solo lectura, lo muestra el sistema --}}
        <div id="bloque-dispositivo-actual" style="display:none;margin-bottom:20px">
            <div style="background:var(--surface-2);border:1px solid var(--border-2);border-radius:10px;padding:16px 18px">
                <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);margin-bottom:10px">
                    Dispositivo actual del empleado (el que pago)
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                    <div>
                        <p style="font-size:11px;color:var(--muted);margin-bottom:3px">Equipo</p>
                        <p id="info-disp-nombre" style="font-weight:700;font-size:14px;color:var(--text)">—</p>
                    </div>
                    <div>
                        <p style="font-size:11px;color:var(--muted);margin-bottom:3px">N Serie</p>
                        <p id="info-disp-serie" style="font-weight:600;font-size:13px;color:var(--text-2)">—</p>
                    </div>
                    <div>
                        <p style="font-size:11px;color:var(--muted);margin-bottom:3px">IMEI</p>
                        <p id="info-disp-imei" style="font-size:13px;color:var(--muted)">—</p>
                    </div>
                </div>
                {{-- campo oculto para enviar al controller --}}
                <input type="hidden" name="dispositivo_actual_id" id="dispositivo-actual-id">
            </div>
            <p style="font-size:12px;color:var(--muted);margin-top:8px">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:3px"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                El departamento de Sistemas se encargara de seleccionar y entregar el dispositivo nuevo.
            </p>
        </div>

        {{-- Aviso: empleado sin dispositivo --}}
        <div id="bloque-sin-dispositivo" style="display:none;margin-bottom:20px">
            <div class="alert alert-warning" style="margin-bottom:0">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Este empleado no tiene ningun dispositivo asignado actualmente. No se puede generar una solicitud de cobro.</span>
            </div>
        </div>

        {{-- 3. BOLETA (visible solo si hay dispositivo) --}}
        <div id="bloque-boleta" style="display:none">
            <div style="border-top:1px solid var(--border);padding-top:20px;margin-top:4px">
                <p style="font-weight:600;font-size:13px;color:var(--text);margin-bottom:14px">Datos del pago</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Numero de Boleta *</label>
                        <input type="text" name="numero_boleta" value="{{ old('numero_boleta') }}"
                               required placeholder="Ej: 2026-00145"
                               class="{{ $errors->has('numero_boleta')?'is-invalid':'' }}">
                        @error('numero_boleta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Fecha de Solicitud *</label>
                        <input type="date" name="fecha_solicitud"
                               value="{{ old('fecha_solicitud', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="form-group full">
                        <label>Imagen / PDF de la Boleta *</label>
                        <input type="file" name="boleta" accept=".jpg,.jpeg,.png,.pdf"
                               class="{{ $errors->has('boleta')?'is-invalid':'' }}"
                               onchange="previewBoleta(this)">
                        @error('boleta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small>Formatos: JPG, PNG, PDF. Max 5 MB.</small>
                        <div id="preview-boleta" style="display:none;margin-top:10px">
                            <img id="preview-img" src="" alt="Vista previa"
                                 style="max-width:100%;max-height:180px;border-radius:8px;border:1px solid var(--border)">
                            <p id="preview-nombre" style="font-size:12px;color:var(--muted);margin-top:5px"></p>
                        </div>
                    </div>
                    <div class="form-group full">
                        <label>Observaciones</label>
                        <textarea name="observaciones" placeholder="Informacion adicional para Sistemas...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Enviar Solicitud a Sistemas
                    </button>
                    <a href="{{ route('solicitudes.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>

        <div id="bloque-cancelar">
            <a href="{{ route('solicitudes.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>

    </form>
</div>

{{-- Panel lateral --}}
<div style="display:flex;flex-direction:column;gap:16px">
    <div class="card" style="margin-bottom:0;background:#fffbeb;border-color:#fde68a">
        <div class="card-header"><span class="card-title" style="color:#854d0e">Como funciona</span></div>
        <ol style="padding-left:18px;font-size:13px;color:#78350f;line-height:2.2">
            <li>Selecciona el <strong>empleado</strong> que realizo el pago.</li>
            <li>El sistema muestra su <strong>equipo actual</strong> (el que pago).</li>
            <li>Adjunta la <strong>boleta de pago</strong> e ingresa el numero.</li>
            <li>Envia la solicitud a <strong>Sistemas</strong>.</li>
            <li>Sistemas elegira el dispositivo nuevo y confirmara la <strong>entrega</strong>.</li>
        </ol>
    </div>
</div>

</div>

@push('scripts')
<script>
function cargarDispositivo(sel) {
    const opt    = sel.options[sel.selectedIndex];
    const tiene  = opt && opt.dataset.tiene === '1';
    const bActual = document.getElementById('bloque-dispositivo-actual');
    const bSin    = document.getElementById('bloque-sin-dispositivo');
    const bBoleta = document.getElementById('bloque-boleta');
    const bCan    = document.getElementById('bloque-cancelar');

    bActual.style.display = 'none';
    bSin.style.display    = 'none';
    bBoleta.style.display = 'none';
    bCan.style.display    = 'block';

    if (!opt || !opt.value) return;

    if (tiene) {
        document.getElementById('info-disp-nombre').textContent = opt.dataset.dispNombre || '—';
        document.getElementById('info-disp-serie').textContent  = opt.dataset.dispSerie  || '—';
        document.getElementById('info-disp-imei').textContent   = opt.dataset.dispImei || '—';
        document.getElementById('dispositivo-actual-id').value  = opt.dataset.dispId   || '';
        bActual.style.display = 'block';
        bBoleta.style.display = 'block';
        bCan.style.display    = 'none';
    } else {
        bSin.style.display = 'block';
    }
}

function previewBoleta(input) {
    const prev = document.getElementById('preview-boleta');
    const img  = document.getElementById('preview-img');
    const nom  = document.getElementById('preview-nombre');
    if (!input.files || !input.files[0]) { prev.style.display = 'none'; return; }
    const file = input.files[0];
    nom.textContent = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; img.style.display = 'block'; };
        reader.readAsDataURL(file);
    } else {
        img.style.display = 'none';
    }
    prev.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('sel-empleado');
    if (sel && sel.value) cargarDispositivo(sel);
});
</script>
@endpush
@endsection
