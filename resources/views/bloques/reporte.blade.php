@extends('layouts.app')
@section('title','Reporte por Bloques')@section('page-title','Reporte por Bloques')
@section('topbar-actions')
<a href="{{ route('exportar.bloques',request()->query()) }}" class="btn btn-success btn-sm">Excel</a>
@endsection
@section('content')
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;max-width:560px">
    <div class="stat-card blue"><div class="stat-value">{{ $bloques->count() }}</div><div class="stat-label">Bloques</div></div>
    <div class="stat-card purple"><div class="stat-value">{{ $totalAsignados }}</div><div class="stat-label">Asignados</div></div>
    <div class="stat-card green" style="max-width:none">
        <div style="font-size:13px;font-weight:600;color:var(--muted);margin-bottom:4px">Costo mensual total</div>
        <div style="font-size:20px;font-weight:800;color:var(--success)">Q {{ number_format($totalMensual,2) }}</div>
    </div>
</div>
<div class="card" style="margin-bottom:20px">
    <form method="GET" action="{{ route('bloques.reporte') }}"><div class="search-bar">
        <div class="form-group"><label>Gama</label>
            <select name="gama">
                <option value="">Todas</option>
                <option value="alta"   {{ request('gama')==='alta'?'selected':'' }}>Alta Gama</option>
                <option value="media"  {{ request('gama')==='media'?'selected':'' }}>Media Gama</option>
                <option value="basica" {{ request('gama')==='basica'?'selected':'' }}>Basica</option>
            </select>
        </div>
        <div class="form-group"><label>Estado</label>
            <select name="activo">
                <option value="">Todos</option>
                <option value="1" {{ request('activo')==='1'?'selected':'' }}>Activos</option>
                <option value="0" {{ request('activo')==='0'?'selected':'' }}>Inactivos</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="{{ route('bloques.reporte') }}" class="btn btn-secondary">Limpiar</a>
    </div></form>
</div>
@foreach($bloques->groupBy('gama') as $gama => $grupo)
@php
    $gamaLabels=['alta'=>'Alta Gama','media'=>'Media Gama','basica'=>'Basica'];
    $gamaBadges=['alta'=>'badge-purple','media'=>'badge-info','basica'=>'badge-secondary'];
    $subtotalMensual=$grupo->sum('costo_total_mensual');
    $subtotalDisp=$grupo->sum('dispositivos_count');
    $subtotalAsig=$grupo->sum('asignados_count');
@endphp
<div style="margin-bottom:28px">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;padding-bottom:10px;border-bottom:2px solid var(--border)">
        <span class="badge {{ $gamaBadges[$gama]??'badge-secondary' }}" style="font-size:13px;padding:5px 14px">{{ $gamaLabels[$gama]??ucfirst($gama) }}</span>
        <span style="font-size:13px;color:var(--muted)">{{ $grupo->count() }} bloque(s)</span>
        <span style="margin-left:auto;font-size:13px;font-weight:600;color:var(--accent)">Q {{ number_format($subtotalMensual,2) }}/mes</span>
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>Bloque</th><th>Operadora</th><th>Costo/linea</th><th style="text-align:center">Total</th><th style="text-align:center">Asignados</th><th style="text-align:center">Disponibles</th><th style="text-align:center">Baja</th><th>Costo mensual</th><th>Costo anual</th><th></th></tr></thead>
        <tbody>
        @foreach($grupo as $b)
        <tr>
            <td><div style="display:flex;align-items:center;gap:8px"><div style="width:10px;height:10px;border-radius:50%;background:{{ $b->color_etiqueta }};flex-shrink:0"></div><span style="font-weight:600">{{ $b->nombre }}</span>@if(!$b->activo)<span class="badge badge-secondary" style="font-size:10px">Inactivo</span>@endif</div>@if($b->descripcion)<p style="font-size:11px;color:var(--muted);margin-top:2px">{{ $b->descripcion }}</p>@endif</td>
            <td style="color:var(--muted)">{{ $b->operadora??'—' }}</td>
            <td style="font-weight:600;color:var(--accent)">Q {{ number_format($b->costo_mensual_linea,2) }}</td>
            <td style="text-align:center;font-weight:700">{{ $b->dispositivos_count }}</td>
            <td style="text-align:center"><span class="badge badge-info">{{ $b->asignados_count }}</span></td>
            <td style="text-align:center"><span class="badge badge-success">{{ $b->disponibles_count }}</span></td>
            <td style="text-align:center"><span class="badge badge-danger">{{ $b->baja_count }}</span></td>
            <td style="font-weight:700;color:var(--success)">Q {{ number_format($b->costo_total_mensual,2) }}</td>
            <td style="color:var(--muted)">Q {{ number_format($b->costo_total_mensual*12,2) }}</td>
            <td><a href="{{ route('bloques.show',$b) }}" class="btn btn-secondary btn-sm">Ver</a></td>
        </tr>
        @endforeach
        </tbody>
        <tfoot><tr style="background:var(--surface-2);font-weight:700">
            <td colspan="3">Subtotal {{ $gamaLabels[$gama]??ucfirst($gama) }}</td>
            <td style="text-align:center">{{ $subtotalDisp }}</td>
            <td style="text-align:center">{{ $subtotalAsig }}</td>
            <td colspan="2"></td>
            <td style="color:var(--success)">Q {{ number_format($subtotalMensual,2) }}</td>
            <td style="color:var(--muted)">Q {{ number_format($subtotalMensual*12,2) }}</td>
            <td></td>
        </tr></tfoot>
    </table></div>
</div>
@endforeach
@if($bloques->isEmpty())
<div style="text-align:center;color:var(--muted);padding:60px"><p style="font-size:15px;font-weight:600;margin-bottom:8px">No hay bloques.</p><a href="{{ route('bloques.create') }}" class="btn btn-primary">Crear primer bloque</a></div>
@else
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
    <span style="font-weight:700;font-size:15px;color:var(--accent)">TOTAL GENERAL</span>
    <div style="display:flex;gap:32px;flex-wrap:wrap">
        <div style="text-align:center"><p style="font-size:11px;color:var(--muted)">Dispositivos</p><p style="font-weight:800;font-size:20px">{{ $totalDispositivos }}</p></div>
        <div style="text-align:center"><p style="font-size:11px;color:var(--muted)">Asignados</p><p style="font-weight:800;font-size:20px;color:var(--accent)">{{ $totalAsignados }}</p></div>
        <div style="text-align:center"><p style="font-size:11px;color:var(--muted)">Costo mensual</p><p style="font-weight:800;font-size:20px;color:var(--success)">Q {{ number_format($totalMensual,2) }}</p></div>
        <div style="text-align:center"><p style="font-size:11px;color:var(--muted)">Costo anual</p><p style="font-weight:800;font-size:20px;color:var(--success)">Q {{ number_format($totalMensual*12,2) }}</p></div>
    </div>
</div>
@endif
@endsection