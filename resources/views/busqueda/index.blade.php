@extends('layouts.app')
@section('title','Busqueda: '.$query)@section('page-title','Resultados de busqueda')
@section('content')
@if(strlen($query)<2)
<div style="text-align:center;padding:60px 20px"><svg width="48" height="48" fill="none" stroke="var(--muted)" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px;display:block"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><p style="color:var(--muted);font-size:15px">Escribe al menos 2 caracteres para buscar.</p></div>
@elseif($total===0)
<div style="text-align:center;padding:60px 20px"><svg width="48" height="48" fill="none" stroke="var(--muted)" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px;display:block"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><p style="color:var(--text);font-size:16px;font-weight:600;margin-bottom:8px">Sin resultados para "{{ $query }}"</p><p style="color:var(--muted);font-size:13px">Intenta con otro termino: nombre, serie, codigo, IMEI...</p></div>
@else
<p style="color:var(--muted);font-size:13px;margin-bottom:20px"><strong style="color:var(--text)">{{ $total }}</strong> resultado(s) para <strong style="color:var(--accent)">"{{ $query }}"</strong></p>
@if($resultados['empleados']->isNotEmpty())
<div class="card"><div class="card-header"><span class="card-title">Empleados</span><span class="badge badge-info">{{ $resultados['empleados']->count() }}</span></div>
<div class="table-wrap"><table><thead><tr><th>Codigo</th><th>Nombre</th><th>Cargo</th><th>Departamento</th><th>Estado</th><th></th></tr></thead><tbody>
@foreach($resultados['empleados'] as $e)<tr><td style="font-weight:600;color:var(--accent)">{{ $e->codigo_empleado }}</td><td style="font-weight:600">{{ $e->nombre_completo }}</td><td>{{ $e->cargo }}</td><td style="color:var(--muted)">{{ $e->departamento }}</td><td><span class="badge badge-{{ $e->estado==='activo'?'success':'secondary' }}">{{ ucfirst($e->estado) }}</span></td><td><a href="{{ route('empleados.show',$e) }}" class="btn btn-secondary btn-sm">Ver</a></td></tr>@endforeach
</tbody></table></div>
<div style="margin-top:10px"><a href="{{ route('empleados.index',['buscar'=>$query]) }}" style="font-size:12px;color:var(--accent)">Ver todos con "{{ $query }}" →</a></div></div>
@endif
@if($resultados['dispositivos']->isNotEmpty())
<div class="card"><div class="card-header"><span class="card-title">Dispositivos</span><span class="badge badge-info">{{ $resultados['dispositivos']->count() }}</span></div>
<div class="table-wrap"><table><thead><tr><th>N Serie</th><th>Dispositivo</th><th>IMEI</th><th>Estado</th><th>Empleado asignado</th><th></th></tr></thead><tbody>
@foreach($resultados['dispositivos'] as $d)
@php $em=['disponible'=>'success','asignado'=>'info','en_reparacion'=>'warning','dado_de_baja'=>'danger']; @endphp
<tr><td style="font-weight:600">{{ $d->numero_serie }}</td><td>{{ $d->nombre_completo }}</td><td style="color:var(--muted);font-size:12px">{{ $d->imei??'—' }}</td><td><span class="badge badge-{{ $em[$d->estado]??'secondary' }}">{{ str_replace('_',' ',ucfirst($d->estado)) }}</span></td><td>{{ $d->empleado?->nombre_completo??'—' }}</td><td><a href="{{ route('dispositivos.show',$d) }}" class="btn btn-secondary btn-sm">Ver</a></td></tr>
@endforeach</tbody></table></div>
<div style="margin-top:10px"><a href="{{ route('dispositivos.index',['buscar'=>$query]) }}" style="font-size:12px;color:var(--accent)">Ver todos con "{{ $query }}" →</a></div></div>
@endif
@if($resultados['asignaciones']->isNotEmpty())
<div class="card"><div class="card-header"><span class="card-title">Asignaciones Activas</span><span class="badge badge-success">{{ $resultados['asignaciones']->count() }}</span></div>
<div class="table-wrap"><table><thead><tr><th>Empleado</th><th>Dispositivo</th><th>N Serie</th><th>F. Asignacion</th><th></th></tr></thead><tbody>
@foreach($resultados['asignaciones'] as $a)<tr><td style="font-weight:600">{{ $a->empleado?->nombre_completo??'—' }}</td><td>{{ $a->dispositivo?->nombre_completo??'—' }}</td><td style="color:var(--muted);font-size:12px">{{ $a->dispositivo?->numero_serie??'—' }}</td><td>{{ $a->fecha_asignacion->format('d/m/Y') }}</td><td><a href="{{ route('dispositivos.show',$a->dispositivo) }}" class="btn btn-secondary btn-sm">Ver</a></td></tr>@endforeach
</tbody></table></div></div>
@endif
@if($resultados['renovaciones']->isNotEmpty())
<div class="card"><div class="card-header"><span class="card-title">Renovaciones</span><span class="badge badge-warning">{{ $resultados['renovaciones']->count() }}</span></div>
<div class="table-wrap"><table><thead><tr><th>N Solicitud</th><th>Empleado</th><th>Motivo</th><th>Estado</th><th>Fecha</th><th></th></tr></thead><tbody>
@foreach($resultados['renovaciones'] as $r)<tr><td style="font-weight:700;color:var(--accent)">{{ $r->numero_solicitud }}</td><td>{{ $r->empleado?->nombre_completo??'—' }}</td><td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--muted)">{{ $r->motivo }}</td><td><span class="badge badge-{{ $r->estado_badge }}">{{ ucfirst($r->estado) }}</span></td><td>{{ $r->fecha_solicitud->format('d/m/Y') }}</td><td><a href="{{ route('renovaciones.show',$r) }}" class="btn btn-secondary btn-sm">Ver</a></td></tr>@endforeach
</tbody></table></div></div>
@endif
@endif
@endsection