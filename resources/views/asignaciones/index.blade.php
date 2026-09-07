@extends('layouts.app')
@section('title','Asignaciones Activas')@section('page-title','Asignaciones Activas')
@section('topbar-actions')
<a href="{{ route('exportar.asignaciones',request()->query()) }}" class="btn btn-success btn-sm">Excel</a>
@if(auth()->user()?->esAdmin())<a href="{{ route('asignaciones.create') }}" class="btn btn-primary"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>Nueva Asignacion</a>@endif
@endsection
@section('content')
<div class="card">
    <form method="GET" action="{{ route('asignaciones.index') }}">
        <div class="search-bar">
            <div class="form-group"><label>Buscar</label><input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Empleado, codigo, serie..."></div>
            <div class="form-group"><label>Bloque</label>
                <select name="bloque_id">
                    <option value="">Todos los bloques</option>
                    @foreach($bloques as $b)
                    <option value="{{ $b->id }}" {{ request('bloque_id')==$b->id?'selected':'' }}>{{ $b->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Departamento</label>
                <select name="departamento">
                    <option value="">Todos</option>
                    @foreach($departamentos as $d)
                    <option value="{{ $d }}" {{ request('departamento')===$d?'selected':'' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Buscar</button>
            <a href="{{ route('asignaciones.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
    <div class="table-wrap"><table>
        <thead><tr><th>Empleado</th><th>Departamento</th><th>Dispositivo</th><th>Bloque</th><th>N Serie</th><th>F. Asignacion</th><th>Acciones</th></tr></thead>
        <tbody>
        @forelse($asignaciones as $a)
        <tr>
            <td><a href="{{ route('empleados.show',$a->empleado) }}" style="color:var(--accent);text-decoration:none;font-weight:600">{{ $a->empleado?->nombre_completo ?? '—' }}</a></td>
            <td style="color:var(--muted)">{{ $a->empleado?->departamento ?? '—' }}</td>
            <td><a href="{{ route('dispositivos.show',$a->dispositivo) }}" style="color:var(--text);text-decoration:none">{{ $a->dispositivo?->nombre_completo ?? '—' }}</a></td>
            <td>
                @if($a->dispositivo?->bloque)
                <span style="display:inline-flex;align-items:center;gap:5px;background:{{ $a->dispositivo->bloque->color_etiqueta }}18;border:1px solid {{ $a->dispositivo->bloque->color_etiqueta }};border-radius:6px;padding:2px 9px;font-size:11px;font-weight:600;color:{{ $a->dispositivo->bloque->color_etiqueta }}">
                    {{ $a->dispositivo->bloque->nombre }}
                </span>
                @else<span style="color:var(--muted);font-size:12px">—</span>@endif
            </td>
            <td style="color:var(--muted);font-size:12px">{{ $a->dispositivo?->numero_serie ?? '—' }}</td>
            <td>{{ $a->fecha_asignacion->format('d/m/Y') }}</td>
            <td><div style="display:flex;gap:6px">
                <a href="{{ route('dispositivos.show',$a->dispositivo) }}" class="btn btn-secondary btn-sm btn-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                <a href="{{ route('dispositivos.carta',$a->dispositivo) }}" target="_blank" class="btn btn-warning btn-sm btn-icon" title="Carta Custodia"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></a>
                @if(auth()->user()?->esAdmin())<a href="{{ route('asignaciones.devolver',$a->dispositivo) }}" class="btn btn-danger btn-sm">Devolver</a>@endif
            </div></td>
        </tr>
        @empty<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px">No hay asignaciones activas.</td></tr>@endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $asignaciones->links() }}</div>
</div>
@endsection