@extends('layouts.app')
@section('title','Dispositivos Comprados')@section('page-title','Reporte de Dispositivos Comprados por Empleados')
@section('topbar-actions')<a href="{{ route('exportar.dispositivos',array_merge(request()->query(),['tipo_baja'=>'comprado'])) }}" class="btn btn-success btn-sm">Excel</a>@endsection
@section('content')
<div style="display:flex;gap:14px;margin-bottom:20px;flex-wrap:wrap">
    <div class="stat-card blue" style="flex:0 0 160px">
        <div style="font-size:36px;font-weight:800;color:var(--text);line-height:1;margin-bottom:5px">{{ $total }}</div>
        <div class="stat-label">Total Comprados</div>
    </div>
    <div class="stat-card green" style="flex:0 0 240px">
        <div style="font-size:13px;font-weight:600;color:var(--muted);margin-bottom:4px">Valor Total</div>
        <div style="font-size:20px;font-weight:800;color:var(--success);line-height:1">Q {{ number_format($valorTotal,2) }}</div>
    </div>
    <div class="stat-card purple" style="flex:0 0 180px">
        <div style="font-size:13px;font-weight:600;color:var(--muted);margin-bottom:4px">Filtro Ano</div>
        <div style="font-size:22px;font-weight:800;color:var(--text);line-height:1">{{ $anio }}</div>
    </div>
</div>
<div class="card">
    <form method="GET" action="{{ route('reportes.comprados') }}">
        <div class="search-bar">
            <div class="form-group"><label>Buscar</label><input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Empleado, serie, boleta..."></div>
            <div class="form-group"><label>Bloque</label>
                <select name="bloque_id">
                    <option value="">Todos</option>
                    <option value="sin_bloque" {{ request('bloque_id')==='sin_bloque'?'selected':'' }}>Sin bloque</option>
                    @foreach($bloques as $b)<option value="{{ $b->id }}" {{ request('bloque_id')==$b->id?'selected':'' }}>{{ $b->nombre }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>Ano</label>
                <select name="anio">
                    <option value="">Todos</option>
                    @foreach($anios as $a)<option value="{{ $a }}" {{ request('anio')==$a?'selected':'' }}>{{ $a }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>Marca</label><input type="text" name="marca" value="{{ request('marca') }}" placeholder="Samsung..."></div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('reportes.comprados') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
    <div class="table-wrap"><table>
        <thead><tr><th>N Serie</th><th>Dispositivo</th><th>Bloque</th><th>IMEI</th><th>Empleado</th><th>N Boleta</th><th>Costo (Q)</th><th>Fecha</th></tr></thead>
        <tbody>
        @forelse($dispositivos as $d)
        @php
            preg_match('/Empleado: ([^|]+)/', $d->observaciones??'', $mEmp);
            preg_match('/Boleta: ([^|]+)/',   $d->observaciones??'', $mBol);
            preg_match('/Fecha: ([^|]+)/',    $d->observaciones??'', $mFec);
            $empNombre = isset($mEmp[1])?trim($mEmp[1]):'—';
            $boleta    = isset($mBol[1])?trim($mBol[1]):'—';
            $fecha     = isset($mFec[1])?trim($mFec[1]):'—';
        @endphp
        <tr>
            <td style="font-weight:600">{{ $d->numero_serie }}</td>
            <td>{{ $d->nombre_completo }}</td>
            <td>
                @if($d->bloque)
                <span style="display:inline-flex;align-items:center;gap:4px;background:{{ $d->bloque->color_etiqueta }}18;border:1px solid {{ $d->bloque->color_etiqueta }};border-radius:6px;padding:2px 8px;font-size:11px;font-weight:600;color:{{ $d->bloque->color_etiqueta }}">
                    {{ $d->bloque->nombre }}
                </span>
                @else<span style="color:var(--muted);font-size:12px">—</span>@endif
            </td>
            <td style="color:var(--muted);font-size:12px">{{ $d->imei??'—' }}</td>
            <td style="font-weight:600">{{ $empNombre }}</td>
            <td style="color:var(--accent);font-weight:600">{{ $boleta }}</td>
            <td>{{ $d->costo?'Q '.number_format($d->costo,2):'—' }}</td>
            <td>{{ $fecha }}</td>
        </tr>
        @empty<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:40px">No hay dispositivos comprados con los filtros seleccionados.</td></tr>@endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $dispositivos->links() }}</div>
</div>
@endsection