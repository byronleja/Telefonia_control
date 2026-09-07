@extends('layouts.app')
@section('title','Reporte Dispositivos')@section('page-title','Reporte de Dispositivos')
@section('topbar-actions')<a href="{{ route('exportar.dispositivos',request()->query()) }}" class="btn btn-success btn-sm">Excel</a>@endsection
@section('content')
<div class="card">
    <form method="GET" action="{{ route('reportes.dispositivos') }}"><div class="search-bar">
        <div class="form-group"><label>Bloque</label>
            <select name="bloque_id">
                <option value="">Todos</option>
                <option value="sin_bloque" {{ request('bloque_id')==='sin_bloque'?'selected':'' }}>Sin bloque</option>
                @foreach($bloques as $b)
                <option value="{{ $b->id }}" {{ request('bloque_id')==$b->id?'selected':'' }}>{{ $b->nombre }} ({{ $b->gama_label }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group"><label>Estado</label>
            <select name="estado">
                <option value="">Todos</option>
                @foreach(['disponible'=>'Disponible','asignado'=>'Asignado','en_reparacion'=>'En reparacion','dado_de_baja'=>'Dado de baja'] as $v=>$l)
                <option value="{{ $v }}" {{ request('estado')===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group"><label>Tipo</label>
            <select name="tipo">
                <option value="">Todos</option>
                @foreach(['smartphone'=>'Smartphone','basico'=>'Basico','tablet'=>'Tablet'] as $v=>$l)
                <option value="{{ $v }}" {{ request('tipo')===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group"><label>Marca</label><input type="text" name="marca" value="{{ request('marca') }}" placeholder="Samsung..."></div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="{{ route('reportes.dispositivos') }}" class="btn btn-secondary">Limpiar</a>
    </div></form>
    <div class="table-wrap"><table>
        <thead><tr><th>N Serie</th><th>Dispositivo</th><th>Bloque</th><th>Gama</th><th>Tipo</th><th>Estado</th><th>Empleado</th><th>Ciclo</th>@if($tieneAsignacion)<th>Vence</th>@endif<th></th></tr></thead>
        <tbody>
        @forelse($dispositivos as $d)
        @php $em=['disponible'=>'success','asignado'=>'info','en_reparacion'=>'warning','dado_de_baja'=>'danger']; @endphp
        <tr>
            <td style="font-weight:600">{{ $d->numero_serie }}</td>
            <td>{{ $d->nombre_completo }}</td>
            <td>
                @if($d->bloque)
                <span style="display:inline-flex;align-items:center;gap:4px;background:{{ $d->bloque->color_etiqueta }}18;border:1px solid {{ $d->bloque->color_etiqueta }};border-radius:6px;padding:2px 8px;font-size:11px;font-weight:600;color:{{ $d->bloque->color_etiqueta }}">
                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $d->bloque->color_etiqueta }}"></span>
                    {{ $d->bloque->nombre }}
                </span>
                @else<span style="color:var(--muted);font-size:12px">—</span>@endif
            </td>
            <td><span class="badge badge-{{ $d->bloque?->gama_badge??'secondary' }}">{{ $d->bloque?->gama_label??'—' }}</span></td>
            <td>{{ ucfirst($d->tipo) }}</td>
            <td><span class="badge badge-{{ $em[$d->estado]??'secondary' }}">{{ str_replace('_',' ',ucfirst($d->estado)) }}</span></td>
            <td>{{ $d->empleado?->nombre_completo??'—' }}</td>
            <td style="text-align:center;font-weight:700;color:var(--accent)">{{ $d->meses_renovacion }}m</td>
            @if($tieneAsignacion)<td>{{ $d->fecha_vencimiento_renovacion?->format('d/m/Y')??'—' }}</td>@endif
            <td><a href="{{ route('dispositivos.show',$d) }}" class="btn btn-secondary btn-sm">Ver</a></td>
        </tr>
        @empty<tr><td colspan="10" style="text-align:center;color:var(--muted);padding:30px">Sin registros</td></tr>@endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $dispositivos->links() }}</div>
</div>
@endsection