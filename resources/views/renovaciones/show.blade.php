@extends('layouts.app')
@section('title',$renovacion->numero_solicitud)@section('page-title',$renovacion->numero_solicitud)
@section('topbar-actions')
@if(auth()->user()?->esAdmin())<a href="{{ route('renovaciones.edit',$renovacion) }}" class="btn btn-secondary btn-sm">Editar</a>@endif
@if($renovacion->estado==='entregada')<a href="{{ route('renovaciones.carta',$renovacion) }}" target="_blank" class="btn btn-warning btn-sm">Carta Custodia</a>@endif
@endsection
@section('content')
<div class="breadcrumb"><a href="{{ route('renovaciones.index') }}">Renovaciones</a><span class="sep">/</span><span>{{ $renovacion->numero_solicitud }}</span></div>
@if(session('mostrar_carta'))<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:18px 22px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px"><p style="font-weight:700;color:#854d0e">Renovacion entregada. Imprime la carta de custodia para que el empleado la firme.</p><a href="{{ route('renovaciones.carta',$renovacion) }}" target="_blank" class="btn btn-warning" style="white-space:nowrap">Imprimir Carta</a></div>@endif
<div class="card">
    <div class="card-header"><span class="card-title">Informacion de la Solicitud</span><span class="badge badge-{{ $renovacion->estado_badge }}">{{ ucfirst($renovacion->estado) }}</span></div>
    <div class="detail-grid">
        <div class="detail-item"><label>N Solicitud</label><p style="font-weight:700;color:var(--accent)">{{ $renovacion->numero_solicitud }}</p></div>
        <div class="detail-item"><label>Empleado</label><p><a href="{{ route('empleados.show',$renovacion->empleado) }}" style="color:var(--accent);text-decoration:none">{{ $renovacion->empleado?->nombre_completo }}</a></p></div>
        <div class="detail-item"><label>Equipo Anterior</label><p>{{ $renovacion->dispositivoActual?->nombre_completo ?? '—' }}</p></div>
        <div class="detail-item"><label>Equipo Nuevo</label><p>{{ $renovacion->dispositivoNuevo?->nombre_completo ?? '—' }}@if($renovacion->dispositivoNuevo)<span style="color:var(--muted);font-size:12px"> ({{ $renovacion->dispositivoNuevo->meses_renovacion }}m)</span>@endif</p></div>
        <div class="detail-item"><label>F. Solicitud</label><p>{{ $renovacion->fecha_solicitud->format('d/m/Y') }}</p></div>
        <div class="detail-item"><label>F. Aprobacion</label><p>{{ $renovacion->fecha_aprobacion?->format('d/m/Y') ?? '—' }}</p></div>
        <div class="detail-item"><label>F. Entrega</label><p>{{ $renovacion->fecha_entrega?->format('d/m/Y') ?? '—' }}</p></div>
        <div class="detail-item"><label>Aprobado por</label><p>{{ $renovacion->aprobado_por ?? '—' }}</p></div>
        @php $dm=['devuelto'=>'Devuelto','comprado'=>'Comprado por empleado','no_aplica'=>'No aplica']; @endphp
        <div class="detail-item"><label>Destino Equipo Ant.</label><p>{{ $dm[$renovacion->destino_equipo_anterior??'no_aplica'] }}</p></div>
        @if($renovacion->precio_compra_empleado)<div class="detail-item"><label>Precio Compra</label><p>Q {{ number_format($renovacion->precio_compra_empleado,2) }}</p></div>@endif
        @if($renovacion->motivo)<div class="detail-item full"><label>Motivo</label><p>{{ $renovacion->motivo }}</p></div>@endif
        @if($renovacion->descripcion)<div class="detail-item full"><label>Descripcion</label><p>{{ $renovacion->descripcion }}</p></div>@endif
        @if($renovacion->observaciones_aprobacion)<div class="detail-item full"><label>Observaciones</label><p>{{ $renovacion->observaciones_aprobacion }}</p></div>@endif
    </div>
</div>
@if($renovacion->adjuntos->isNotEmpty())
<div class="card"><div class="card-header"><span class="card-title">Adjuntos ({{ $renovacion->adjuntos->count() }})</span></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px">
@foreach($renovacion->adjuntos as $a)
<div style="border:1px solid var(--border);border-radius:8px;padding:14px;display:flex;align-items:center;gap:12px">
    <svg width="22" height="22" fill="none" stroke="var(--muted)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    <div style="flex:1;min-width:0"><p style="font-weight:500;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $a->nombre_original }}</p><p style="color:var(--muted);font-size:11px">{{ $a->tamanio_formateado }}</p></div>
    <div style="display:flex;gap:6px"><a href="{{ $a->url }}" target="_blank" class="btn btn-secondary btn-sm btn-icon">Ver</a>@if(auth()->user()?->esAdmin())<form method="POST" action="{{ route('adjuntos.destroy',$a) }}" onsubmit="return confirm('Eliminar?')">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm btn-icon">X</button></form>@endif</div>
</div>
@endforeach
</div></div>
@endif
@endsection