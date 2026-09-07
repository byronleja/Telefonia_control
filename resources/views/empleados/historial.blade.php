@extends('layouts.app')
@section('title','Historial — '.$empleado->nombre_completo)
@section('page-title','Historial de Dispositivos')
@section('topbar-actions')
<a href="{{ route('exportar.historial.empleado',$empleado) }}" class="btn btn-success btn-sm">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Exportar Excel
</a>
<a href="{{ route('empleados.show',$empleado) }}" class="btn btn-secondary btn-sm">Volver al empleado</a>
@endsection
@section('content')
<div class="breadcrumb">
    <a href="{{ route('empleados.index') }}">Empleados</a>
    <span class="sep">/</span>
    <a href="{{ route('empleados.show',$empleado) }}">{{ $empleado->nombre_completo }}</a>
    <span class="sep">/</span>
    <span>Historial de Dispositivos</span>
</div>

{{-- Datos del empleado --}}
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <span class="card-title">{{ $empleado->nombre_completo }}</span>
        <span class="badge badge-{{ $empleado->estado==='activo'?'success':'secondary' }}">{{ ucfirst($empleado->estado) }}</span>
    </div>
    <div class="detail-grid">
        <div class="detail-item"><label>Codigo</label><p>{{ $empleado->codigo_empleado }}</p></div>
        <div class="detail-item"><label>Departamento</label><p>{{ $empleado->departamento }}</p></div>
        <div class="detail-item"><label>Cargo</label><p>{{ $empleado->cargo }}</p></div>
        <div class="detail-item"><label>Total dispositivos usados</label>
            <p style="font-size:22px;font-weight:800;color:var(--accent)">{{ $asignaciones->total() }}</p>
        </div>
    </div>
</div>

{{-- Dispositivo actual --}}
@php $actual = $empleado->dispositivos->first(); @endphp
@if($actual)
<div class="card" style="border-color:var(--success);margin-bottom:20px">
    <div class="card-header">
        <span class="card-title" style="color:var(--success)">Dispositivo Actual</span>
        <span class="badge badge-success">Asignado</span>
    </div>
    <div class="detail-grid">
        <div class="detail-item"><label>Equipo</label>
            <p><a href="{{ route('dispositivos.show',$actual) }}" style="color:var(--accent);text-decoration:none;font-weight:700">{{ $actual->nombre_completo }}</a></p>
        </div>
        <div class="detail-item"><label>N Serie</label><p>{{ $actual->numero_serie }}</p></div>
        <div class="detail-item"><label>Bloque</label>
            @if($actual->bloque)
            <p>
                <span style="display:inline-flex;align-items:center;gap:4px;background:{{ $actual->bloque->color_etiqueta }}18;border:1px solid {{ $actual->bloque->color_etiqueta }};border-radius:6px;padding:3px 10px;font-size:12px;font-weight:600;color:{{ $actual->bloque->color_etiqueta }}">
                    {{ $actual->bloque->nombre }}
                </span>
                <span style="font-size:11px;color:var(--muted);margin-left:6px">{{ $actual->bloque->gama_label }}</span>
            </p>
            @else<p style="color:var(--muted)">Sin bloque</p>@endif
        </div>
        <div class="detail-item"><label>Costo mensual/linea</label>
            <p style="font-weight:700;color:var(--accent)">{{ $actual->bloque ? 'Q '.number_format($actual->bloque->costo_mensual_linea,2) : '—' }}</p>
        </div>
        <div class="detail-item"><label>F. Asignacion</label><p>{{ $actual->fecha_asignacion?->format('d/m/Y') ?? '—' }}</p></div>
        <div class="detail-item"><label>Ciclo / Vence</label>
            @php $mr = $actual->meses_para_renovacion; @endphp
            <p><span class="badge badge-{{ $mr<=0?'danger':($mr<=2?'warning':'success') }}">
                {{ $mr<=0?'Vencido hace '.abs($mr).'m':$mr.' m restantes' }}
            </span></p>
        </div>
    </div>
</div>
@endif

{{-- Timeline historial --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Historial completo de dispositivos</span>
        <span class="badge badge-info">{{ $asignaciones->total() }} registros</span>
    </div>

    @forelse($asignaciones as $a)
    <div style="display:flex;gap:16px;padding:18px 0;border-bottom:1px solid var(--border)">
        {{-- Timeline dot --}}
        <div style="display:flex;flex-direction:column;align-items:center;min-width:40px">
            <div style="width:14px;height:14px;border-radius:50%;flex-shrink:0;margin-top:4px;
                background:{{ $a->estaActiva()?'var(--success)':'var(--muted)' }};
                border:2px solid {{ $a->estaActiva()?'var(--success)':'var(--border)' }}"></div>
            @if(!$loop->last)<div style="width:2px;flex:1;background:var(--border);margin-top:4px;min-height:30px"></div>@endif
        </div>

        {{-- Contenido --}}
        <div style="flex:1">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:10px">
                <div>
                    <a href="{{ route('dispositivos.show',$a->dispositivo) }}" style="font-weight:700;font-size:15px;color:var(--accent);text-decoration:none">
                        {{ $a->dispositivo?->nombre_completo ?? '—' }}
                    </a>
                    <span style="color:var(--muted);font-size:12px;margin-left:8px">S/N: {{ $a->dispositivo?->numero_serie ?? '—' }}</span>
                </div>
                <span class="badge badge-{{ $a->estaActiva()?'success':'secondary' }}">
                    {{ $a->estaActiva()?'Activo':'Finalizado' }}
                </span>
            </div>

            {{-- Bloque y gama --}}
            @if($a->dispositivo?->bloque)
            <div style="margin-bottom:10px">
                <span style="display:inline-flex;align-items:center;gap:4px;background:{{ $a->dispositivo->bloque->color_etiqueta }}18;border:1px solid {{ $a->dispositivo->bloque->color_etiqueta }};border-radius:6px;padding:2px 10px;font-size:11px;font-weight:600;color:{{ $a->dispositivo->bloque->color_etiqueta }}">
                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $a->dispositivo->bloque->color_etiqueta }}"></span>
                    {{ $a->dispositivo->bloque->nombre }}
                </span>
                <span style="font-size:11px;color:var(--muted);margin-left:6px">{{ $a->dispositivo->bloque->gama_label }} · Q {{ number_format($a->dispositivo->bloque->costo_mensual_linea,2) }}/mes</span>
            </div>
            @endif

            {{-- Datos de asignacion --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin-bottom:8px">
                <div>
                    <span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Asignado el</span>
                    <p style="font-weight:600;font-size:13px">{{ $a->fecha_asignacion->format('d/m/Y') }}</p>
                </div>
                @if($a->fecha_devolucion)
                <div>
                    <span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Devuelto el</span>
                    <p style="font-weight:600;font-size:13px">{{ $a->fecha_devolucion->format('d/m/Y') }}</p>
                </div>
                <div>
                    <span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Duracion</span>
                    <p style="font-weight:600;font-size:13px">{{ $a->fecha_asignacion->diffInDays($a->fecha_devolucion) }} dias</p>
                </div>
                @else
                <div>
                    <span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Tiempo de uso</span>
                    <p style="font-weight:600;font-size:13px;color:var(--success)">{{ $a->fecha_asignacion->diffInDays(now()) }} dias (activo)</p>
                </div>
                @endif
                @if($a->asignado_por)
                <div>
                    <span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Asignado por</span>
                    <p style="font-size:13px">{{ $a->asignado_por }}</p>
                </div>
                @endif
                @if($a->devuelto_por)
                <div>
                    <span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Devuelto por</span>
                    <p style="font-size:13px">{{ $a->devuelto_por }}</p>
                </div>
                @endif
            </div>

            @if($a->razon_asignacion)
            <p style="font-size:12px;color:var(--text-2);margin-bottom:2px"><span style="color:var(--muted)">Razon:</span> {{ $a->razon_asignacion }}</p>
            @endif
            @if($a->razon_devolucion)
            <p style="font-size:12px;color:var(--text-2);margin-bottom:2px"><span style="color:var(--muted)">Devolucion:</span> {{ $a->razon_devolucion }}</p>
            @endif
            @if($a->observaciones)
            <p style="font-size:12px;color:var(--muted)">{{ $a->observaciones }}</p>
            @endif
        </div>
    </div>
    @empty
    <div style="text-align:center;color:var(--muted);padding:40px">
        <p style="font-size:15px;margin-bottom:8px">Este empleado no tiene historial de dispositivos.</p>
    </div>
    @endforelse

    @if($asignaciones->hasPages())
    <div class="pagination" style="margin-top:20px">{{ $asignaciones->links() }}</div>
    @endif
</div>
@endsection