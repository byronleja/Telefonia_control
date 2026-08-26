@extends('layouts.app')
@section('title','Solicitudes de Entrega')
@section('page-title','Solicitudes de Entrega')
@section('topbar-actions')
@if(auth()->user()->esContabilidad())
<a href="{{ route('solicitudes.create') }}" class="btn btn-primary">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
    Nueva Solicitud
</a>
@endif
@endsection
@section('content')

@if($pendientes > 0 && !auth()->user()->esContabilidad())
<div class="alert alert-warning">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <span>Hay <strong>{{ $pendientes }}</strong> solicitud(es) de Contabilidad pendientes de entrega.</span>
</div>
@endif

<div class="card">
    <form method="GET" action="{{ route('solicitudes.index') }}">
        <div class="search-bar">
            <div class="form-group"><label>Buscar</label><input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="N solicitud, boleta, empleado..."></div>
            <div class="form-group"><label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="pendiente"  {{ request('estado')==='pendiente'?'selected':'' }}>Pendiente</option>
                    <option value="entregado"  {{ request('estado')==='entregado'?'selected':'' }}>Entregado</option>
                    <option value="rechazado"  {{ request('estado')==='rechazado'?'selected':'' }}>Rechazado</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Buscar</button>
            <a href="{{ route('solicitudes.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>

    <div class="table-wrap"><table>
        <thead><tr>
            <th>N Solicitud</th><th>Empleado</th><th>Dispositivo</th>
            <th>N Boleta</th><th>Boleta</th><th>Estado</th>
            <th>F. Solicitud</th><th>Creado por</th><th>Acciones</th>
        </tr></thead>
        <tbody>
        @forelse($solicitudes as $s)
        <tr>
            <td style="font-weight:700;color:var(--accent)">
                <a href="{{ route('solicitudes.show',$s) }}" style="color:var(--accent);text-decoration:none">{{ $s->numero_solicitud }}</a>
            </td>
            <td style="font-weight:600">{{ $s->empleado?->nombre_completo ?? '—' }}</td>
            <td>{{ $s->dispositivo?->nombre_completo ?? '—' }}<br><small style="color:var(--muted)">{{ $s->dispositivo?->numero_serie }}</small></td>
            <td style="font-weight:600;color:var(--text-2)">{{ $s->numero_boleta }}</td>
            <td>
                @if($s->url_boleta)
                <a href="{{ $s->url_boleta }}" target="_blank" class="btn btn-secondary btn-sm btn-icon" title="Ver boleta">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                @else<span style="color:var(--muted)">—</span>@endif
            </td>
            <td>
                <span class="badge badge-{{ $s->estado_badge }}">
                    {{ ucfirst($s->estado) }}
                </span>
            </td>
            <td>{{ $s->fecha_solicitud->format('d/m/Y') }}</td>
            <td style="color:var(--muted);font-size:12px">{{ $s->creadoPor?->name ?? '—' }}</td>
            <td>
                <div style="display:flex;gap:6px">
                    <a href="{{ route('solicitudes.show',$s) }}" class="btn btn-secondary btn-sm btn-icon">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    @if($s->estado==='pendiente' && auth()->user()->esAdmin())
                    <a href="{{ route('solicitudes.show',$s) }}#entregar" class="btn btn-success btn-sm">Entregar</a>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:40px">
            @if(auth()->user()->esContabilidad())
                No tienes solicitudes. <a href="{{ route('solicitudes.create') }}" style="color:var(--accent)">Crear la primera</a>
            @else
                No hay solicitudes de entrega.
            @endif
        </td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $solicitudes->links() }}</div>
</div>
@endsection