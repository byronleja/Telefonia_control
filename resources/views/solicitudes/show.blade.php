@extends('layouts.app')
@section('title', $solicitude->numero_solicitud)
@section('page-title', 'Solicitud ' . $solicitude->numero_solicitud)
@section('topbar-actions')
@if($solicitude->estado==='entregado' && $solicitude->dispositivo)
<a href="{{ route('dispositivos.carta',$solicitude->dispositivo) }}" target="_blank" class="btn btn-warning btn-sm">Carta de Custodia</a>
@endif
@endsection
@section('content')
<div class="breadcrumb">
    <a href="{{ route('solicitudes.index') }}">Solicitudes</a>
    <span class="sep">/</span><span>{{ $solicitude->numero_solicitud }}</span>
</div>

@if(session('mostrar_carta'))
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:18px 22px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px">
    <div>
        <p style="font-weight:700;color:#854d0e">Dispositivo entregado a {{ $solicitude->empleado?->nombre_completo }}</p>
        <p style="color:#92400e;font-size:12px;margin-top:2px">Imprime la carta de custodia para que el empleado la firme.</p>
    </div>
    <a href="{{ route('dispositivos.carta',$solicitude->dispositivo) }}" target="_blank" class="btn btn-warning" style="white-space:nowrap">Imprimir Carta</a>
</div>
@endif

{{-- Fila datos solicitud + boleta --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span class="card-title">Datos de la Solicitud</span>
            <span class="badge badge-{{ $solicitude->estado_badge }}">{{ ucfirst($solicitude->estado) }}</span>
        </div>
        <div class="detail-grid">
            <div class="detail-item"><label>N Solicitud</label><p style="font-weight:700;color:var(--accent)">{{ $solicitude->numero_solicitud }}</p></div>
            <div class="detail-item"><label>F. Solicitud</label><p>{{ $solicitude->fecha_solicitud->format('d/m/Y') }}</p></div>
            <div class="detail-item"><label>Creado por</label><p>{{ $solicitude->creadoPor?->name ?? '—' }}</p></div>
            <div class="detail-item"><label>Area</label><p>Contabilidad</p></div>
            @if($solicitude->fecha_entrega)
            <div class="detail-item"><label>F. Entrega</label><p>{{ $solicitude->fecha_entrega->format('d/m/Y') }}</p></div>
            <div class="detail-item"><label>Entregado por</label><p>{{ $solicitude->atendidoPor?->name ?? '—' }}</p></div>
            @endif
            @if($solicitude->observaciones)
            <div class="detail-item" style="grid-column:1/-1"><label>Observaciones</label><p>{{ $solicitude->observaciones }}</p></div>
            @endif
            @if($solicitude->motivo_rechazo)
            <div class="detail-item" style="grid-column:1/-1">
                <label style="color:var(--danger)">Motivo de Rechazo</label>
                <p style="color:var(--danger)">{{ $solicitude->motivo_rechazo }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="card-header"><span class="card-title">Boleta de Pago</span></div>
        <div class="detail-item" style="margin-bottom:12px">
            <label>Numero de Boleta</label>
            <p style="font-size:22px;font-weight:800;color:var(--accent)">{{ $solicitude->numero_boleta }}</p>
        </div>
        @if($solicitude->url_boleta)
        <a href="{{ $solicitude->url_boleta }}" target="_blank" class="btn btn-secondary btn-sm" style="margin-bottom:12px">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            Ver: {{ $solicitude->nombre_boleta }}
        </a>
        @php $ext = strtolower(pathinfo($solicitude->nombre_boleta ?? '', PATHINFO_EXTENSION)); @endphp
        @if(in_array($ext, ['jpg','jpeg','png']))
        <img src="{{ $solicitude->url_boleta }}" alt="Boleta"
             style="width:100%;max-height:200px;object-fit:contain;border-radius:8px;border:1px solid var(--border);background:var(--surface-2)">
        @endif
        @endif
    </div>

</div>

{{-- Empleado + dispositivo cobrado --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

    <div class="card" style="margin-bottom:0">
        <div class="card-header"><span class="card-title">Empleado</span></div>
        <div class="detail-grid">
            <div class="detail-item"><label>Nombre</label>
                <p><a href="{{ route('empleados.show',$solicitude->empleado) }}" style="color:var(--accent);text-decoration:none;font-weight:600">{{ $solicitude->empleado?->nombre_completo }}</a></p>
            </div>
            <div class="detail-item"><label>Departamento</label><p>{{ $solicitude->empleado?->departamento }}</p></div>
            <div class="detail-item"><label>Cargo</label><p>{{ $solicitude->empleado?->cargo }}</p></div>
        </div>
    </div>

    <div class="card" style="margin-bottom:0;border-color:var(--warning)">
        <div class="card-header">
            <span class="card-title">Dispositivo que Pago</span>
            <span class="badge badge-warning">Sera liberado</span>
        </div>
        @if($solicitude->dispositivoActual)
        <div class="detail-grid">
            <div class="detail-item"><label>Equipo</label>
                <p style="font-weight:700">{{ $solicitude->dispositivoActual->nombre_completo }}</p>
            </div>
            <div class="detail-item"><label>N Serie</label><p>{{ $solicitude->dispositivoActual->numero_serie }}</p></div>
            @if($solicitude->dispositivoActual->imei)
            <div class="detail-item"><label>IMEI</label><p>{{ $solicitude->dispositivoActual->imei }}</p></div>
            @endif
            <div class="detail-item"><label>Estado</label>
                @php $em=['disponible'=>'success','asignado'=>'info','en_reparacion'=>'warning','dado_de_baja'=>'danger']; @endphp
                <p><span class="badge badge-{{ $em[$solicitude->dispositivoActual->estado]??'secondary' }}">{{ str_replace('_',' ',ucfirst($solicitude->dispositivoActual->estado)) }}</span></p>
            </div>
        </div>
        @else
        <p style="color:var(--muted);font-size:13px;padding:8px 0">Sin dispositivo anterior registrado.</p>
        @endif
    </div>

</div>

{{-- Dispositivo entregado (solo si ya fue procesada) --}}
@if($solicitude->estado === 'entregado' && $solicitude->dispositivo)
<div class="card" style="border-color:var(--success);margin-bottom:20px">
    <div class="card-header">
        <span class="card-title" style="color:var(--success)">Dispositivo Entregado por Sistemas</span>
        <span class="badge badge-success">Entregado</span>
    </div>
    <div class="detail-grid">
        <div class="detail-item"><label>Equipo</label>
            <p><a href="{{ route('dispositivos.show',$solicitude->dispositivo) }}" style="color:var(--accent);text-decoration:none;font-weight:600">{{ $solicitude->dispositivo->nombre_completo }}</a></p>
        </div>
        <div class="detail-item"><label>N Serie</label><p>{{ $solicitude->dispositivo->numero_serie }}</p></div>
        @if($solicitude->dispositivo->imei)<div class="detail-item"><label>IMEI</label><p>{{ $solicitude->dispositivo->imei }}</p></div>@endif
        <div class="detail-item"><label>Ciclo</label><p style="font-weight:700;color:var(--accent)">{{ $solicitude->dispositivo->meses_renovacion }} meses</p></div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════
     PANEL SISTEMAS (admin) — solo si pendiente
════════════════════════════════════════════ --}}
@if($solicitude->estado === 'pendiente' && auth()->user()->esAdmin())

@if($solicitude->dispositivoActual)
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px 20px;margin-bottom:20px;display:flex;gap:14px;align-items:center">
    <svg width="20" height="20" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <p style="font-size:13px;color:#1e40af">
        Al confirmar la entrega, el equipo <strong>{{ $solicitude->dispositivoActual->nombre_completo }}</strong>
        (S/N: {{ $solicitude->dispositivoActual->numero_serie }}) quedara en estado <strong>Disponible</strong>.
    </p>
</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

    {{-- Confirmar entrega --}}
    <div class="card" style="margin-bottom:0;border-color:var(--success)">
        <div class="card-header"><span class="card-title" style="color:var(--success)">Confirmar Entrega — Seleccionar Dispositivo Nuevo</span></div>
        <form method="POST" action="{{ route('solicitudes.entregar',$solicitude) }}">
            @csrf
            <div class="form-grid">
                <div class="form-group full">
                    <label>Dispositivo a entregar *</label>
                    <select name="dispositivo_id" required class="{{ $errors->has('dispositivo_id')?'is-invalid':'' }}">
                        <option value="">— Seleccionar dispositivo disponible —</option>
                        @forelse($dispositivosDisponibles as $d)
                        <option value="{{ $d->id }}">
                            {{ $d->nombre_completo }} — S/N: {{ $d->numero_serie }}
                            @if($d->meses_renovacion) ({{ $d->meses_renovacion }}m)@endif
                        </option>
                        @empty
                        @endforelse
                    </select>
                    @error('dispositivo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($dispositivosDisponibles->isEmpty())
                    <small style="color:var(--danger)">No hay dispositivos disponibles en este momento.</small>
                    @endif
                </div>
                <div class="form-group">
                    <label>Fecha de entrega *</label>
                    <input type="date" name="fecha_entrega" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="form-group full">
                    <label>Observaciones</label>
                    <textarea name="observaciones" placeholder="Notas internas de la entrega..."></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-success"
                    onclick="return confirm('Confirmar entrega del dispositivo?\n\nEsta accion asignara el equipo al empleado y liberara el anterior.')">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    Confirmar Entrega
                </button>
            </div>
        </form>
    </div>

    {{-- Rechazar --}}
    <div class="card" style="margin-bottom:0;border-color:var(--danger)">
        <div class="card-header"><span class="card-title" style="color:var(--danger)">Rechazar</span></div>
        <form method="POST" action="{{ route('solicitudes.rechazar',$solicitude) }}">
            @csrf
            <div class="form-group" style="margin-bottom:16px">
                <label>Motivo *</label>
                <textarea name="motivo_rechazo" required rows="4"
                    placeholder="Ej: Boleta ilegible, numero incorrecto..."></textarea>
                @error('motivo_rechazo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Rechazar esta solicitud?')">
                    Rechazar Solicitud
                </button>
            </div>
        </form>
    </div>

</div>
@endif

@endsection
