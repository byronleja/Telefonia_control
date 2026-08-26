@extends('layouts.app')
@section('title','Historial de Asignaciones')@section('page-title','Historial de Asignaciones')
@section('topbar-actions')<a href="{{ route('dispositivos.show',$dispositivo) }}" class="btn btn-secondary btn-sm">Volver al dispositivo</a>@endsection
@section('content')
<div class="breadcrumb"><a href="{{ route('dispositivos.index') }}">Dispositivos</a><span class="sep">/</span><a href="{{ route('dispositivos.show',$dispositivo) }}">{{ $dispositivo->nombre_completo }}</a><span class="sep">/</span><span>Historial</span></div>
<div class="card">
    <div class="card-header"><span class="card-title">{{ $dispositivo->nombre_completo }}</span><span class="badge badge-info">{{ $asignaciones->total() }} asignaciones</span></div>
    <div class="detail-grid"><div class="detail-item"><label>N Serie</label><p>{{ $dispositivo->numero_serie }}</p></div><div class="detail-item"><label>Ciclo</label><p style="font-weight:700;color:var(--accent)">{{ $dispositivo->meses_renovacion }} meses</p></div></div>
</div>
<div class="card">
    <div class="card-header"><span class="card-title">Historial completo</span><span class="badge badge-info">{{ $asignaciones->firstItem() }}–{{ $asignaciones->lastItem() }} de {{ $asignaciones->total() }}</span></div>
    @forelse($asignaciones as $a)
    <div style="display:flex;gap:16px;padding:16px 0;border-bottom:1px solid var(--border)">
        <div style="display:flex;flex-direction:column;align-items:center;min-width:40px"><div style="width:14px;height:14px;border-radius:50%;flex-shrink:0;margin-top:4px;background:{{ $a->estaActiva()?'var(--success)':'var(--muted)' }};border:2px solid {{ $a->estaActiva()?'var(--success)':'var(--border)' }}"></div>@if(!$loop->last)<div style="width:2px;flex:1;background:var(--border);margin-top:4px;min-height:30px"></div>@endif</div>
        <div style="flex:1">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap">
                <span style="font-weight:700;font-size:15px">{{ $a->empleado?->nombre_completo ?? '—' }}</span>
                <span style="color:var(--muted);font-size:12px">{{ $a->empleado?->cargo??'' }}@if($a->empleado?->departamento) — {{ $a->empleado->departamento }}@endif</span>
                <span class="badge badge-{{ $a->estaActiva()?'success':'secondary' }}">{{ $a->estaActiva()?'Activa':'Finalizada' }}</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;margin-bottom:8px">
                <div><span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Asignado el</span><p style="font-weight:600;font-size:13px">{{ $a->fecha_asignacion->format('d/m/Y') }}</p></div>
                @if($a->fecha_devolucion)<div><span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Devuelto el</span><p style="font-weight:600;font-size:13px">{{ $a->fecha_devolucion->format('d/m/Y') }}</p></div><div><span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Duracion</span><p style="font-weight:600;font-size:13px">{{ $a->fecha_asignacion->diffInDays($a->fecha_devolucion) }} dias</p></div>@else<div><span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Duracion (hasta hoy)</span><p style="font-weight:600;font-size:13px;color:var(--success)">{{ $a->fecha_asignacion->diffInDays(now()) }} dias</p></div>@endif
                @if($a->asignado_por)<div><span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Asignado por</span><p style="font-size:13px">{{ $a->asignado_por }}</p></div>@endif
                @if($a->devuelto_por)<div><span style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Devuelto por</span><p style="font-size:13px">{{ $a->devuelto_por }}</p></div>@endif
            </div>
            @if($a->razon_asignacion)<p style="font-size:12px;color:var(--text-2);margin-bottom:3px"><span style="color:var(--muted)">Razon asignacion:</span> {{ $a->razon_asignacion }}</p>@endif
            @if($a->razon_devolucion)<p style="font-size:12px;color:var(--text-2);margin-bottom:3px"><span style="color:var(--muted)">Razon devolucion:</span> {{ $a->razon_devolucion }}</p>@endif
            @if($a->observaciones)<p style="font-size:12px;color:var(--muted)">{{ $a->observaciones }}</p>@endif
        </div>
    </div>
    @empty<div style="text-align:center;color:var(--muted);padding:40px">Sin historial de asignaciones.</div>@endforelse
    @if($asignaciones->hasPages())<div class="pagination" style="margin-top:20px">{{ $asignaciones->links() }}</div>@endif
</div>
@endsection