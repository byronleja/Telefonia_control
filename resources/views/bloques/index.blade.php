@extends('layouts.app')
@section('title','Bloques')
@section('page-title','Bloques de Dispositivos')
@section('topbar-actions')
<a href="{{ route('bloques.reporte') }}" class="btn btn-secondary btn-sm">Reporte</a>
@if(auth()->user()?->esAdmin())
<a href="{{ route('bloques.create') }}" class="btn btn-primary">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
    Nuevo Bloque
</a>
@endif
@endsection
@section('content')

<div class="info-box">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Los bloques agrupan dispositivos por gama y costo mensual con linea. Costo total mensual activo:
    <strong style="color:var(--accent);margin-left:4px">Q {{ number_format($totalCostoMensual,2) }}</strong>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:20px">
@forelse($bloques as $b)
<div class="card" style="margin-bottom:0;border-top:4px solid {{ $b->color_etiqueta }}">
    <div class="card-header">
        <div>
            <span class="card-title">{{ $b->nombre }}</span>
            <div style="margin-top:4px;display:flex;gap:6px;flex-wrap:wrap">
                <span class="badge badge-{{ $b->gama_badge }}">{{ $b->gama_label }}</span>
                @if(!$b->activo)<span class="badge badge-secondary">Inactivo</span>@endif
            </div>
        </div>
        <div style="display:flex;gap:6px">
            <a href="{{ route('bloques.show',$b) }}" class="btn btn-secondary btn-sm btn-icon">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </a>
            @if(auth()->user()?->esAdmin())
            <a href="{{ route('bloques.edit',$b) }}" class="btn btn-secondary btn-sm btn-icon">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </a>
            @endif
        </div>
    </div>
    @if($b->descripcion)<p style="font-size:12px;color:var(--muted);margin-bottom:12px">{{ $b->descripcion }}</p>@endif
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
        <div style="background:var(--surface-2);border-radius:8px;padding:10px;text-align:center">
            <p style="font-size:22px;font-weight:800;color:var(--accent)">{{ $b->dispositivos_count }}</p>
            <p style="font-size:11px;color:var(--muted)">Total</p>
        </div>
        <div style="background:var(--surface-2);border-radius:8px;padding:10px;text-align:center">
            <p style="font-size:22px;font-weight:800;color:var(--success)">{{ $b->asignados_count }}</p>
            <p style="font-size:11px;color:var(--muted)">Asignados</p>
        </div>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--border);padding-top:12px">
        <div>
            <p style="font-size:11px;color:var(--muted)">Costo mensual/linea</p>
            <p style="font-weight:700;color:var(--accent)">Q {{ number_format($b->costo_mensual_linea,2) }}</p>
        </div>
        <div style="text-align:right">
            <p style="font-size:11px;color:var(--muted)">Total mensual activo</p>
            <p style="font-weight:700;color:var(--success)">Q {{ number_format($b->costo_total_mensual,2) }}</p>
        </div>
    </div>
    @if($b->operadora)
    <p style="font-size:11px;color:var(--muted);margin-top:8px">Operadora: <strong>{{ $b->operadora }}</strong></p>
    @endif
</div>
@empty
<div style="grid-column:1/-1;text-align:center;color:var(--muted);padding:60px 20px">
    <p style="font-size:15px;font-weight:600;margin-bottom:8px">No hay bloques creados</p>
    @if(auth()->user()?->esAdmin())<a href="{{ route('bloques.create') }}" class="btn btn-primary">Crear el primero</a>@endif
</div>
@endforelse
</div>
{{ $bloques->links() }}
@endsection