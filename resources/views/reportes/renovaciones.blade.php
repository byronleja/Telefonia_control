@extends('layouts.app')
@section('title','Reporte Renovaciones')@section('page-title','Reporte de Renovaciones')
@section('topbar-actions')
<a href="{{ route('exportar.renovaciones',request()->query()) }}" class="btn btn-success btn-sm">Excel</a>
<a href="{{ route('reportes.equipos_danados') }}" class="btn btn-danger btn-sm">Equipos Danados ({{ $totalBaja }})</a>
@endsection
@section('content')
<div class="card">
    <form method="GET" action="{{ route('reportes.renovaciones') }}">
        <div class="search-bar">
            <div class="form-group"><label>Estado</label><select name="estado"><option value="">Todos</option>@foreach(['pendiente'=>'Pendiente','aprobada'=>'Aprobada','rechazada'=>'Rechazada','entregada'=>'Entregada'] as $v=>$l)<option value="{{ $v }}" {{ request('estado')===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select></div>
            <div class="form-group"><label>Ciclo</label><select name="ciclo"><option value="">Todos</option>@foreach($ciclosDisponibles as $c)<option value="{{ $c }}" {{ request('ciclo')==$c?'selected':'' }}>{{ $c }} meses</option>@endforeach</select></div>
            <div class="form-group"><label>Destino ant.</label><select name="destino"><option value="">Todos</option><option value="devuelto" {{ request('destino')==='devuelto'?'selected':'' }}>Devuelto</option><option value="comprado" {{ request('destino')==='comprado'?'selected':'' }}>Comprado</option><option value="no_aplica" {{ request('destino')==='no_aplica'?'selected':'' }}>No aplica</option></select></div>
            <div class="form-group"><label>Ano</label><select name="anio"><option value="">Todos</option>@foreach($anios as $a)<option value="{{ $a }}" {{ request('anio')==$a?'selected':'' }}>{{ $a }}</option>@endforeach</select></div>
            <div class="form-group"><label>Desde</label><input type="date" name="desde" value="{{ request('desde') }}"></div>
            <div class="form-group"><label>Hasta</label><input type="date" name="hasta" value="{{ request('hasta') }}"></div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('reportes.renovaciones') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
    <div class="table-wrap"><table>
        <thead><tr><th>N Solicitud</th><th>Empleado</th><th>Equipo Anterior</th><th>Equipo Nuevo</th><th>Ciclo</th><th>Destino Ant.</th><th>Estado</th><th>F. Solicitud</th><th>F. Entrega</th></tr></thead>
        <tbody>
        @forelse($renovaciones as $r)
        @php $dm=['devuelto'=>'Devuelto','comprado'=>'Comprado','no_aplica'=>'No aplica'];$dest=$r->destino_equipo_anterior??'no_aplica'; @endphp
        <tr>
            <td><a href="{{ route('renovaciones.show',$r) }}" style="color:var(--accent);font-weight:700;text-decoration:none">{{ $r->numero_solicitud }}</a></td>
            <td>{{ $r->empleado?->nombre_completo??'—' }}</td>
            <td style="color:var(--muted)">{{ $r->dispositivoActual?->nombre_completo??'—' }}</td>
            <td>{{ $r->dispositivoNuevo?->nombre_completo??'—' }}</td>
            <td style="text-align:center;font-weight:700;color:var(--accent)">{{ $r->dispositivoNuevo?->meses_renovacion?$r->dispositivoNuevo->meses_renovacion.'m':'—' }}</td>
            <td><span class="badge badge-secondary">{{ $dm[$dest] }}</span></td>
            <td><span class="badge badge-{{ $r->estado_badge }}">{{ ucfirst($r->estado) }}</span></td>
            <td>{{ $r->fecha_solicitud->format('d/m/Y') }}</td>
            <td>{{ $r->fecha_entrega?->format('d/m/Y')??'—' }}</td>
        </tr>
        @empty<tr><td colspan="9" style="text-align:center;color:var(--muted);padding:30px">Sin registros</td></tr>@endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $renovaciones->links() }}</div>
</div>
@endsection