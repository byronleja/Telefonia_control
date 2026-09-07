@extends('layouts.app')
@section('title',$empleado->nombre_completo)@section('page-title',$empleado->nombre_completo)
@section('topbar-actions')
<a href="{{ route('empleados.historial',$empleado) }}" class="btn btn-secondary btn-sm">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Historial
</a>
@if(auth()->user()?->esAdmin())<a href="{{ route('empleados.edit',$empleado) }}" class="btn btn-secondary btn-sm">Editar</a>@endif
@endsection
@section('content')
<div class="breadcrumb"><a href="{{ route('empleados.index') }}">Empleados</a><span class="sep">/</span><span>{{ $empleado->nombre_completo }}</span></div>
<div class="card">
    <div class="card-header"><span class="card-title">Informacion del Empleado</span><span class="badge badge-{{ $empleado->estado==='activo'?'success':'secondary' }}">{{ ucfirst($empleado->estado) }}</span></div>
    <div class="detail-grid">
        <div class="detail-item"><label>Codigo</label><p>{{ $empleado->codigo_empleado }}</p></div>
        <div class="detail-item"><label>Nombre Completo</label><p>{{ $empleado->nombre_completo }}</p></div>
        <div class="detail-item"><label>Email</label><p>{{ $empleado->email }}</p></div>
        <div class="detail-item"><label>Telefono</label><p>{{ $empleado->telefono ?? '—' }}</p></div>
        <div class="detail-item"><label>Departamento</label><p>{{ $empleado->departamento }}</p></div>
        <div class="detail-item"><label>Cargo</label><p>{{ $empleado->cargo }}</p></div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <span class="card-title">Dispositivos Asignados ({{ $empleado->dispositivos->count() }})</span>
        @if(auth()->user()?->esAdmin())<a href="{{ route('asignaciones.create',['empleado_id'=>$empleado->id]) }}" class="btn btn-primary btn-sm">Nueva Asignacion</a>@endif
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>Serie</th><th>Dispositivo</th><th>Bloque</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse($empleado->dispositivos as $d)
        <tr>
            <td>{{ $d->numero_serie }}</td>
            <td><a href="{{ route('dispositivos.show',$d) }}" style="color:var(--accent);text-decoration:none">{{ $d->nombre_completo }}</a></td>
            <td>
                @if($d->bloque)
                <span style="display:inline-flex;align-items:center;gap:4px;background:{{ $d->bloque->color_etiqueta }}18;border:1px solid {{ $d->bloque->color_etiqueta }};border-radius:6px;padding:2px 8px;font-size:11px;font-weight:600;color:{{ $d->bloque->color_etiqueta }}">
                    {{ $d->bloque->nombre }}
                </span>
                @else<span style="color:var(--muted);font-size:12px">Sin bloque</span>@endif
            </td>
            <td><span class="badge badge-{{ $d->estado==='asignado'?'info':'secondary' }}">{{ ucfirst($d->estado) }}</span></td>
            <td><a href="{{ route('dispositivos.show',$d) }}" class="btn btn-secondary btn-sm">Ver</a></td>
        </tr>
        @empty<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:20px">Sin dispositivos</td></tr>@endforelse
        </tbody>
    </table></div>
    <div style="margin-top:14px"><a href="{{ route('empleados.historial',$empleado) }}" class="btn btn-secondary btn-sm">Ver historial completo de dispositivos</a></div>
</div>
@endsection