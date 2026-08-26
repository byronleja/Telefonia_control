@extends('layouts.app')
@section('title','Usuarios del Sistema')@section('page-title','Usuarios del Sistema')
@section('topbar-actions')<a href="{{ route('usuarios.create') }}" class="btn btn-primary"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>Nuevo Usuario</a>@endsection
@section('content')
<div class="card">
    <div class="table-wrap"><table>
        <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Registrado</th><th>Acciones</th></tr></thead>
        <tbody>
        @forelse($usuarios as $u)
        <tr>
            <td style="font-weight:600">{{ $u->name }}@if($u->id===auth()->id())<span class="badge badge-info" style="margin-left:6px">Tu</span>@endif</td>
            <td style="color:var(--muted)">{{ $u->email }}</td>
            <td><span class="badge {{ $u->esAdmin()?'badge-purple':'badge-secondary' }}">{{ $u->esAdmin()?'Administrador':'Visualizador' }}</span></td>
            <td style="color:var(--muted)">{{ $u->created_at->format('d/m/Y') }}</td>
            <td><div style="display:flex;gap:6px">
                <a href="{{ route('usuarios.edit',$u) }}" class="btn btn-secondary btn-sm btn-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                @if($u->id!==auth()->id())<form method="POST" action="{{ route('usuarios.destroy',$u) }}" onsubmit="return confirm('Eliminar usuario {{ $u->name }}?')">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm btn-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>@endif
            </div></td>
        </tr>
        @empty<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:40px">No hay usuarios</td></tr>@endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $usuarios->links() }}</div>
</div>
@endsection