@extends('layouts.app')
@section('title',$empleado->nombre_completo)@section('page-title',$empleado->nombre_completo)
@section('topbar-actions')
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
    <div class="card-header"><span class="card-title">Dispositivos ({{ $empleado->dispositivos->count() }})</span>
        @if(auth()->user()?->esAdmin())<a href="{{ route('asignaciones.create',['empleado_id'=>$empleado->id]) }}" class="btn btn-primary btn-sm">Nueva Asignacion</a>@endif
    </div>
    <div class="table-wrap"><table><thead><tr><th>Serie</th><th>Dispositivo</th><th>Estado</th><th></th></tr></thead><tbody>
    @forelse($empleado->dispositivos as $d)
    <tr><td>{{ $d->numero_serie }}</td><td><a href="{{ route('dispositivos.show',$d) }}" style="color:var(--accent);text-decoration:none">{{ $d->nombre_completo }}</a></td><td><span class="badge badge-{{ $d->estado==='asignado'?'info':'secondary' }}">{{ ucfirst($d->estado) }}</span></td><td><a href="{{ route('dispositivos.show',$d) }}" class="btn btn-secondary btn-sm">Ver</a></td></tr>
    @empty<tr><td colspan="4" style="text-align:center;color:var(--muted);padding:20px">Sin dispositivos</td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection