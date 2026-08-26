@extends('layouts.app')
@section('title','Dashboard')@section('page-title','Dashboard')
@section('content')
<div class="stats-grid">
    <div class="stat-card blue"><div class="stat-value">{{ $stats['empleados_activos'] }}</div><div class="stat-label">Empleados Activos</div></div>
    <div class="stat-card green"><div class="stat-value">{{ $stats['dispositivos_disponibles'] }}</div><div class="stat-label">Disponibles</div></div>
    <div class="stat-card purple"><div class="stat-value">{{ $stats['dispositivos_asignados'] }}</div><div class="stat-label">Asignados</div></div>
    <div class="stat-card yellow"><div class="stat-value">{{ $stats['renovaciones_pendientes'] }}</div><div class="stat-label">Renovaciones Pendientes</div></div>
    <div class="stat-card teal"><div class="stat-value">{{ $stats['renovaciones_entregadas'] }}</div><div class="stat-label">Renovaciones Entregadas</div></div>
    <div class="stat-card red"><div class="stat-value">{{ $stats['alertas_renovacion'] }}</div><div class="stat-label">Alertas Renovacion</div></div>
    <div class="stat-card red"><div class="stat-value">{{ $stats['dispositivos_baja'] }}</div><div class="stat-label">Dados de Baja</div></div>
    <div class="stat-card yellow"><div class="stat-value">{{ $stats['dispositivos_reparacion'] }}</div><div class="stat-label">En Reparacion</div></div>
</div>
@if($stats['alertas_renovacion']>0)
<div class="alert alert-warning"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>Hay <strong>{{ $stats['alertas_renovacion'] }}</strong> equipo(s) proximos a vencer o ya vencidos. <a href="{{ route('reportes.alertas') }}" style="color:var(--warning);font-weight:700;margin-left:8px">Ver alertas</a></div>
@endif
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px">
    <div class="card" style="margin-bottom:0"><div class="card-header"><span class="card-title">Renovaciones por mes (ultimos 12 meses)</span></div><canvas id="chartRenovaciones" height="100"></canvas></div>
    <div class="card" style="margin-bottom:0"><div class="card-header"><span class="card-title">Asignados por departamento</span></div>
        @if($porDepartamento->isEmpty())<p style="color:var(--muted);text-align:center;padding:40px;font-size:13px">Sin datos.</p>
        @else<canvas id="chartDepartamentos" height="160"></canvas>@endif
    </div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <div class="card" style="margin-bottom:0">
        <div class="card-header"><span class="card-title">Renovaciones recientes</span><a href="{{ route('renovaciones.index') }}" class="btn btn-secondary btn-sm">Ver todas</a></div>
        <div class="table-wrap"><table><thead><tr><th>Solicitud</th><th>Empleado</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>
        @forelse($renovacionesRecientes as $r)
        <tr><td><a href="{{ route('renovaciones.show',$r) }}" style="color:var(--accent);text-decoration:none;font-weight:600">{{ $r->numero_solicitud }}</a></td><td>{{ $r->empleado?->nombre_completo??'—' }}</td><td><span class="badge badge-{{ $r->estado_badge }}">{{ ucfirst($r->estado) }}</span></td><td style="color:var(--muted)">{{ $r->fecha_solicitud->format('d/m/Y') }}</td></tr>
        @empty<tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">Sin renovaciones</td></tr>@endforelse
        </tbody></table></div>
    </div>
    <div style="display:flex;flex-direction:column;gap:20px">
        <div class="card" style="margin-bottom:0"><div class="card-header"><span class="card-title">Estado de la flota</span></div><canvas id="chartEstadoFlota" height="130"></canvas></div>
        <div class="card" style="margin-bottom:0"><div class="card-header"><span class="card-title">Top marcas</span></div>
        @foreach($topMarcas as $marca)
        @php $pct=$stats['total_dispositivos']>0?round(($marca->total/$stats['total_dispositivos'])*100):0; @endphp
        <div style="margin-bottom:10px"><div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px"><span style="font-weight:500">{{ $marca->marca }}</span><span style="color:var(--muted)">{{ $marca->total }} ({{ $pct }}%)</span></div><div style="height:6px;background:var(--surface-2);border-radius:6px;overflow:hidden"><div style="height:100%;width:{{ $pct }}%;background:var(--accent);border-radius:6px"></div></div></div>
        @endforeach
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const accent='#2563eb',success='#059669',warning='#d97706',danger='#dc2626';
const mesesLabels=@json(array_column($mesesGrafica,'label'));
const mesesTotal=@json(array_column($mesesGrafica,'total'));
const mesesEntregadas=@json(array_column($mesesGrafica,'entregadas'));
const mesesPendientes=@json(array_column($mesesGrafica,'pendientes'));
new Chart(document.getElementById('chartRenovaciones'),{type:'bar',data:{labels:mesesLabels,datasets:[{label:'Total',data:mesesTotal,backgroundColor:'rgba(37,99,235,.15)',borderColor:accent,borderWidth:1.5,borderRadius:4,order:2},{label:'Entregadas',data:mesesEntregadas,type:'line',borderColor:success,backgroundColor:'rgba(5,150,105,.1)',borderWidth:2,tension:.35,pointRadius:4,pointBackgroundColor:success,fill:true,order:1},{label:'Pendientes',data:mesesPendientes,type:'line',borderColor:warning,borderWidth:2,tension:.35,pointRadius:4,pointBackgroundColor:warning,fill:false,order:0}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{boxWidth:12,padding:16,font:{size:12}}},tooltip:{mode:'index',intersect:false}},scales:{x:{grid:{display:false},ticks:{font:{size:11}}},y:{beginAtZero:true,ticks:{stepSize:1,font:{size:11}},grid:{color:'#f1f5f9'}}}}});
@if($porDepartamento->isNotEmpty())
new Chart(document.getElementById('chartDepartamentos'),{type:'doughnut',data:{labels:@json($porDepartamento->pluck('departamento')),datasets:[{data:@json($porDepartamento->pluck('total')),backgroundColor:['#2563eb','#7c3aed','#059669','#d97706','#dc2626','#0891b2','#db2777','#65a30d'],borderWidth:2,borderColor:'#fff'}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{boxWidth:12,padding:10,font:{size:11}}},tooltip:{callbacks:{label:ctx=>` ${ctx.label}: ${ctx.raw} dispositivo(s)`}}},cutout:'60%'}});
@endif
new Chart(document.getElementById('chartEstadoFlota'),{type:'doughnut',data:{labels:['Disponibles','Asignados','En reparacion','Dados de baja'],datasets:[{data:[{{ $stats['dispositivos_disponibles'] }},{{ $stats['dispositivos_asignados'] }},{{ $stats['dispositivos_reparacion'] }},{{ $stats['dispositivos_baja'] }}],backgroundColor:[success,accent,warning,danger],borderWidth:2,borderColor:'#fff'}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{boxWidth:12,padding:10,font:{size:11}}},tooltip:{callbacks:{label:ctx=>` ${ctx.label}: ${ctx.raw}`}}},cutout:'55%'}});
</script>
@endpush
@endsection