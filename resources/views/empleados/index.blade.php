@extends('layouts.app')
@section('title','Empleados')@section('page-title','Empleados')
@section('topbar-actions')
<a href="{{ route('exportar.empleados',request()->query()) }}" class="btn btn-success btn-sm">Excel</a>
@if(auth()->user()?->esAdmin())<a href="{{ route('empleados.create') }}" class="btn btn-primary"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>Nuevo</a>@endif
@endsection
@section('content')
<div class="card">
    <form method="GET" action="{{ route('empleados.index') }}">
        <div class="search-bar">
            <div class="form-group"><label>Buscar</label><input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, codigo, email..."></div>
            <div class="form-group"><label>Departamento</label><select name="departamento"><option value="">Todos</option>@foreach($departamentos as $d)<option value="{{ $d }}" {{ request('departamento')===$d?'selected':'' }}>{{ $d }}</option>@endforeach</select></div>
            <div class="form-group"><label>Estado</label><select name="estado"><option value="">Todos</option><option value="activo" {{ request('estado')==='activo'?'selected':'' }}>Activo</option><option value="inactivo" {{ request('estado')==='inactivo'?'selected':'' }}>Inactivo</option></select></div>
            <button type="submit" class="btn btn-primary">Buscar</button>
            <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
    <div class="table-wrap"><table>
        <thead><tr><th>Codigo</th><th>Nombre</th><th>Cargo</th><th>Departamento</th><th>Email</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        @forelse($empleados as $e)
        <tr>
            <td style="font-weight:600;color:var(--accent)">{{ $e->codigo_empleado }}</td>
            <td style="font-weight:600">{{ $e->nombre_completo }}</td>
            <td>{{ $e->cargo }}</td><td style="color:var(--muted)">{{ $e->departamento }}</td>
            <td style="color:var(--muted)">{{ $e->email }}</td>
            <td><span class="badge badge-{{ $e->estado==='activo'?'success':'secondary' }}">{{ ucfirst($e->estado) }}</span></td>
            <td><div style="display:flex;gap:6px">
                <a href="{{ route('empleados.show',$e) }}" class="btn btn-secondary btn-sm btn-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                @if(auth()->user()?->esAdmin())
                <a href="{{ route('empleados.edit',$e) }}" class="btn btn-secondary btn-sm btn-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                <form method="POST" action="{{ route('empleados.destroy',$e) }}" onsubmit="return confirm('Eliminar?')">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm btn-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                @endif
            </div></td>
        </tr>
        @empty<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px">No hay empleados</td></tr>@endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $empleados->links() }}</div>
</div>
@endsection