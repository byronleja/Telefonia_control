@extends('layouts.app')
@section('title','Equipos Danados / Baja')@section('page-title','Equipos Danados y Dados de Baja')
@section('topbar-actions')<a href="{{ route('exportar.dispositivos',array_merge(request()->query(),['estado'=>request('estado','dado_de_baja')])) }}" class="btn btn-success btn-sm">Excel</a>@endsection
@section('content')
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);max-width:500px">
    <div class="stat-card red"><div class="stat-value">{{ $totalDadosDeBaja }}</div><div class="stat-label">Dados de Baja</div></div>
    <div class="stat-card yellow"><div class="stat-value">{{ $totalEnReparacion }}</div><div class="stat-label">En Reparacion</div></div>
    <div class="stat-card blue"><div class="stat-value">{{ $totalDadosDeBaja+$totalEnReparacion }}</div><div class="stat-label">Total</div></div>
</div>
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
    <a href="{{ route('reportes.equipos_danados') }}" class="btn {{ !request('estado')?'btn-primary':'btn-secondary' }}">Todos</a>
    <a href="{{ route('reportes.equipos_danados',['estado'=>'dado_de_baja']) }}" class="btn {{ request('estado')==='dado_de_baja'?'btn-primary':'btn-secondary' }}">Dados de baja ({{ $totalDadosDeBaja }})</a>
    <a href="{{ route('reportes.equipos_danados',['estado'=>'en_reparacion']) }}" class="btn {{ request('estado')==='en_reparacion'?'btn-primary':'btn-secondary' }}">En reparacion ({{ $totalEnReparacion }})</a>
</div>
<div class="card">
    <form method="GET" action="{{ route('reportes.equipos_danados') }}"><div class="search-bar">
        <div class="form-group"><label>Estado</label><select name="estado"><option value="">Ambos</option><option value="dado_de_baja" {{ request('estado')==='dado_de_baja'?'selected':'' }}>Dado de baja</option><option value="en_reparacion" {{ request('estado')==='en_reparacion'?'selected':'' }}>En reparacion</option></select></div>
        <div class="form-group"><label>Tipo</label><select name="tipo"><option value="">Todos</option>@foreach(['smartphone'=>'Smartphone','basico'=>'Basico','tablet'=>'Tablet'] as $v=>$l)<option value="{{ $v }}" {{ request('tipo')===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="form-group"><label>Marca</label><input type="text" name="marca" value="{{ request('marca') }}" placeholder="Samsung..."></div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="{{ route('reportes.equipos_danados') }}" class="btn btn-secondary">Limpiar</a>
    </div></form>
    <div class="table-wrap"><table>
        <thead><tr><th>N Serie</th><th>Dispositivo</th><th>Tipo</th><th>Costo (Q)</th><th>Estado</th><th>Ultimo empleado</th><th>Observaciones</th><th>Acciones</th></tr></thead>
        <tbody>
        @forelse($dispositivos as $d)
        <tr><td style="font-weight:600">{{ $d->numero_serie }}</td><td>{{ $d->nombre_completo }}</td><td>{{ ucfirst($d->tipo) }}</td><td>{{ $d->costo?'Q '.number_format($d->costo,2):'—' }}</td><td><span class="badge {{ $d->estado==='dado_de_baja'?'badge-danger':'badge-warning' }}">{{ $d->estado==='dado_de_baja'?'Dado de baja':'En reparacion' }}</span></td><td>{{ $d->empleado?->nombre_completo??'—' }}</td><td style="max-width:180px;font-size:12px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $d->observaciones?\Illuminate\Support\Str::limit($d->observaciones,60):'—' }}</td>
        <td><div style="display:flex;gap:6px"><a href="{{ route('dispositivos.show',$d) }}" class="btn btn-secondary btn-sm btn-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>@if(auth()->user()?->esAdmin())<a href="{{ route('dispositivos.confirmar_baja',$d) }}" class="btn btn-secondary btn-sm" title="Restaurar">Restaurar</a>@endif</div></td></tr>
        @empty<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:40px">No hay equipos danados con los filtros seleccionados.</td></tr>@endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $dispositivos->links() }}</div>
</div>
@endsection