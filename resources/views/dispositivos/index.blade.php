@extends('layouts.app')
@section('title','Dispositivos')@section('page-title','Dispositivos')
@section('topbar-actions')
<a href="{{ route('exportar.dispositivos',request()->query()) }}" class="btn btn-success btn-sm">Excel</a>
<a href="{{ route('importar.index') }}" class="btn btn-secondary btn-sm">Importar</a>
@if(auth()->user()?->esAdmin())<a href="{{ route('dispositivos.create') }}" class="btn btn-primary"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>Nuevo</a>@endif
@endsection
@section('content')
<div class="info-box"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
Los equipos <strong>dados de baja</strong> y <strong>en reparacion</strong> aparecen en <a href="{{ route('reportes.equipos_danados') }}" style="color:var(--accent);font-weight:600">Equipos Danados / Baja</a>.</div>
<div class="card">
    <form method="GET" action="{{ route('dispositivos.index') }}">
        <div class="search-bar">
            <div class="form-group"><label>Buscar</label><input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Serie, marca, IMEI..."></div>
            <div class="form-group"><label>Estado</label><select name="estado"><option value="">Disponibles y Asignados</option><option value="disponible" {{ request('estado')==='disponible'?'selected':'' }}>Disponible</option><option value="asignado" {{ request('estado')==='asignado'?'selected':'' }}>Asignado</option></select></div>
            <div class="form-group"><label>Tipo</label><select name="tipo"><option value="">Todos</option>@foreach(['smartphone'=>'Smartphone','basico'=>'Basico','tablet'=>'Tablet'] as $v=>$l)<option value="{{ $v }}" {{ request('tipo')===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select></div>
            <button type="submit" class="btn btn-primary">Buscar</button>
            <a href="{{ route('dispositivos.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
    <div class="table-wrap"><table>
        <thead><tr><th>N Serie</th><th>Dispositivo</th><th>IMEI</th><th>Tipo</th><th>Estado</th><th>Empleado</th><th>Acciones</th></tr></thead>
        <tbody>
        @forelse($dispositivos as $d)
        @php $em=['disponible'=>'success','asignado'=>'info']; @endphp
        <tr>
            <td style="font-weight:600">{{ $d->numero_serie }}</td><td>{{ $d->nombre_completo }}</td>
            <td style="color:var(--muted)">{{ $d->imei ?? '—' }}</td><td>{{ ucfirst($d->tipo) }}</td>
            <td><span class="badge badge-{{ $em[$d->estado]??'secondary' }}">{{ ucfirst($d->estado) }}</span></td>
            <td>{{ $d->empleado?->nombre_completo ?? '—' }}</td>
            <td><div style="display:flex;gap:6px">
                <a href="{{ route('dispositivos.show',$d) }}" class="btn btn-secondary btn-sm btn-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                @if(auth()->user()?->esAdmin())
                <a href="{{ route('dispositivos.edit',$d) }}" class="btn btn-secondary btn-sm btn-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                @if($d->estado==='disponible')<a href="{{ route('asignaciones.create',['dispositivo_id'=>$d->id]) }}" class="btn btn-primary btn-sm">Asignar</a>
                @elseif($d->estado==='asignado')<a href="{{ route('asignaciones.devolver',$d) }}" class="btn btn-warning btn-sm">Devolver</a>@endif
                @endif
            </div></td>
        </tr>
        @empty<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px">No hay dispositivos</td></tr>@endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $dispositivos->links() }}</div>
</div>
@endsection