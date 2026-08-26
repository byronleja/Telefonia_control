@extends('layouts.app')
@section('title','Renovaciones')@section('page-title','Renovaciones')
@section('topbar-actions')
<a href="{{ route('exportar.renovaciones',request()->query()) }}" class="btn btn-success btn-sm">Excel</a>
@if(auth()->user()?->esAdmin())<a href="{{ route('renovaciones.create') }}" class="btn btn-primary"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>Nueva</a>@endif
@endsection
@section('content')
<div class="card">
    <form method="GET" action="{{ route('renovaciones.index') }}">
        <div class="search-bar">
            <div class="form-group"><label>Buscar</label><input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="N solicitud, empleado..."></div>
            <div class="form-group"><label>Estado</label><select name="estado"><option value="">Todos</option>@foreach(['pendiente'=>'Pendiente','aprobada'=>'Aprobada','rechazada'=>'Rechazada','entregada'=>'Entregada'] as $v=>$l)<option value="{{ $v }}" {{ request('estado')===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select></div>
            <div class="form-group"><label>Ciclo</label><select name="ciclo"><option value="">Todos</option>@foreach($ciclosDisponibles as $c)<option value="{{ $c }}" {{ request('ciclo')==$c?'selected':'' }}>{{ $c }} meses</option>@endforeach</select></div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('renovaciones.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
    <div class="table-wrap"><table>
        <thead><tr><th>N Solicitud</th><th>Empleado</th><th>Equipo Anterior</th><th>Equipo Nuevo</th><th>Estado</th><th>F. Solicitud</th><th>Acciones</th></tr></thead>
        <tbody>
        @forelse($renovaciones as $r)
        <tr>
            <td style="font-weight:700;color:var(--accent)"><a href="{{ route('renovaciones.show',$r) }}" style="color:var(--accent);text-decoration:none">{{ $r->numero_solicitud }}</a></td>
            <td>{{ $r->empleado?->nombre_completo ?? '—' }}</td>
            <td style="color:var(--muted)">{{ $r->dispositivoActual?->nombre_completo ?? '—' }}</td>
            <td>{{ $r->dispositivoNuevo?->nombre_completo ?? '—' }}</td>
            <td><span class="badge badge-{{ $r->estado_badge }}">{{ ucfirst($r->estado) }}</span></td>
            <td>{{ $r->fecha_solicitud->format('d/m/Y') }}</td>
            <td><div style="display:flex;gap:6px">
                <a href="{{ route('renovaciones.show',$r) }}" class="btn btn-secondary btn-sm btn-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                @if(auth()->user()?->esAdmin())<a href="{{ route('renovaciones.edit',$r) }}" class="btn btn-secondary btn-sm btn-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>@endif
            </div></td>
        </tr>
        @empty<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px">Sin renovaciones</td></tr>@endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $renovaciones->links() }}</div>
</div>
@endsection