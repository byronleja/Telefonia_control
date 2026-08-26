@extends('layouts.app')
@section('title',$dispositivo->nombre_completo)@section('page-title',$dispositivo->nombre_completo)
@section('topbar-actions')
<a href="{{ route('dispositivos.historial',$dispositivo) }}" class="btn btn-secondary btn-sm">Historial</a>
@if($dispositivo->estado==='disponible'&&auth()->user()?->esAdmin())<a href="{{ route('asignaciones.create',['dispositivo_id'=>$dispositivo->id]) }}" class="btn btn-primary btn-sm">Asignar</a>@endif
@if($dispositivo->estado==='asignado'&&auth()->user()?->esAdmin())<a href="{{ route('asignaciones.devolver',$dispositivo) }}" class="btn btn-warning btn-sm">Devolver</a>@endif
@if(auth()->user()?->esAdmin())<a href="{{ route('dispositivos.edit',$dispositivo) }}" class="btn btn-secondary btn-sm">Editar</a>@endif
@endsection
@section('content')
<div class="breadcrumb"><a href="{{ route('dispositivos.index') }}">Dispositivos</a><span class="sep">/</span><span>{{ $dispositivo->nombre_completo }}</span></div>
@if(session('mostrar_carta')&&$dispositivo->empleado)
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:18px 22px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px">
    <div><p style="font-weight:700;color:#854d0e">Dispositivo asignado a {{ $dispositivo->empleado->nombre_completo }}</p><p style="color:#92400e;font-size:12px;margin-top:2px">Genera e imprime la carta de custodia antes de entregar el equipo.</p></div>
    <a href="{{ route('dispositivos.carta',$dispositivo) }}" target="_blank" class="btn btn-warning" style="white-space:nowrap">Imprimir Carta</a>
</div>
@endif
@php $em=['disponible'=>'success','asignado'=>'info','en_reparacion'=>'warning','dado_de_baja'=>'danger']; @endphp
<div class="card">
    <div class="card-header"><span class="card-title">Informacion del Dispositivo</span><span class="badge badge-{{ $em[$dispositivo->estado]??'secondary' }}">{{ str_replace('_',' ',ucfirst($dispositivo->estado)) }}</span></div>
    <div class="detail-grid">
        <div class="detail-item"><label>N Serie</label><p>{{ $dispositivo->numero_serie }}</p></div>
        <div class="detail-item"><label>Marca / Modelo</label><p>{{ $dispositivo->nombre_completo }}</p></div>
        <div class="detail-item"><label>IMEI</label><p>{{ $dispositivo->imei ?? '—' }}</p></div>
        <div class="detail-item"><label>Tipo</label><p>{{ ucfirst($dispositivo->tipo) }}</p></div>
        <div class="detail-item"><label>N Telefonico</label><p>{{ $dispositivo->numero_telefonico ?? '—' }}</p></div>
        <div class="detail-item"><label>Operadora</label><p>{{ $dispositivo->operadora ?? '—' }}</p></div>
        <div class="detail-item"><label>Color</label><p>{{ $dispositivo->color ?? '—' }}</p></div>
        <div class="detail-item"><label>Fecha Compra</label><p>{{ $dispositivo->fecha_compra?->format('d/m/Y') ?? '—' }}</p></div>
        <div class="detail-item"><label>Costo</label><p>{{ $dispositivo->costo ? 'Q '.number_format($dispositivo->costo,2) : '—' }}</p></div>
        <div class="detail-item"><label>Empleado Actual</label><p>@if($dispositivo->empleado)<a href="{{ route('empleados.show',$dispositivo->empleado) }}" style="color:var(--accent);text-decoration:none;font-weight:600">{{ $dispositivo->empleado->nombre_completo }}</a>@else<span style="color:var(--muted)">Sin asignar</span>@endif</p></div>
        @if($dispositivo->observaciones)<div class="detail-item" style="grid-column:1/-1"><label>Observaciones</label><p style="white-space:pre-line;font-size:12px">{{ $dispositivo->observaciones }}</p></div>@endif
    </div>
</div>
@if($dispositivo->fecha_asignacion)
<div class="card">
    <div class="card-header"><span class="card-title">Ciclo de Renovacion</span>
    @php $mr=$dispositivo->meses_para_renovacion; @endphp
    <span class="badge badge-{{ $mr<=0?'danger':($mr<=2?'warning':'success') }}">{{ $dispositivo->estado_renovacion }}</span></div>
    <div class="detail-grid">
        <div class="detail-item"><label>Meses del ciclo</label><p style="font-size:22px;font-weight:800;color:var(--accent)">{{ $dispositivo->meses_renovacion }} meses</p></div>
        <div class="detail-item"><label>Fecha de Asignacion</label><p>{{ $dispositivo->fecha_asignacion->format('d/m/Y') }}</p></div>
        <div class="detail-item"><label>Fecha de Vencimiento</label><p>{{ $dispositivo->fecha_vencimiento_renovacion?->format('d/m/Y') ?? '—' }}</p></div>
        <div class="detail-item"><label>Tiempo restante</label><p style="font-weight:700;color:{{ $mr<=0?'var(--danger)':($mr<=2?'var(--warning)':'var(--success)') }}">{{ $mr<=0?'Vencido hace '.abs($mr).' mes(es)!':$mr.' mes(es) restantes' }}</p></div>
    </div>
    @php $total=$dispositivo->meses_renovacion;$usados=min((int)$dispositivo->fecha_asignacion->diffInMonths(now()),$total);$pct=$total>0?round(($usados/$total)*100):0;$color=$pct>=100?'var(--danger)':($pct>=85?'var(--warning)':'var(--success)'); @endphp
    <div style="margin-top:14px"><div style="display:flex;justify-content:space-between;font-size:12px;color:var(--muted);margin-bottom:6px"><span>{{ $dispositivo->fecha_asignacion->format('d/m/Y') }}</span><span>{{ $pct }}% transcurrido</span><span>{{ $dispositivo->fecha_vencimiento_renovacion?->format('d/m/Y') }}</span></div>
    <div style="height:10px;background:var(--surface-2);border-radius:10px;overflow:hidden;border:1px solid var(--border)"><div style="height:100%;width:{{ min($pct,100) }}%;background:{{ $color }};border-radius:10px"></div></div></div>
</div>
@else
<div class="card"><div style="display:flex;align-items:center;justify-content:space-between"><p style="color:var(--muted);font-size:13px">Ciclo: <strong style="color:var(--accent)">{{ $dispositivo->meses_renovacion }} meses</strong>. El conteo inicia al asignar el dispositivo.</p>@if($dispositivo->estado==='disponible'&&auth()->user()?->esAdmin())<a href="{{ route('asignaciones.create',['dispositivo_id'=>$dispositivo->id]) }}" class="btn btn-primary btn-sm">Asignar ahora</a>@endif</div></div>
@endif
@if($dispositivo->empleado)<div style="margin-bottom:20px"><a href="{{ route('dispositivos.carta',$dispositivo) }}" target="_blank" class="btn btn-warning">Carta de Custodia</a></div>@endif
@if(auth()->user()?->esAdmin()&&!in_array($dispositivo->estado,['dado_de_baja']))
<div style="margin-bottom:20px"><a href="{{ route('dispositivos.confirmar_baja',$dispositivo) }}" class="btn btn-danger btn-sm">Dar de Baja</a></div>
@endif
<div class="card">
    <div class="card-header"><span class="card-title">Historial de Asignaciones</span><a href="{{ route('dispositivos.historial',$dispositivo) }}" class="btn btn-secondary btn-sm">Ver detalle</a></div>
    @if($dispositivo->asignaciones->isEmpty())
    <p style="color:var(--muted);text-align:center;padding:24px">Sin historial de asignaciones.</p>
    @else
    <div class="table-wrap"><table><thead><tr><th>Empleado</th><th>Departamento</th><th>F. Asignacion</th><th>F. Devolucion</th><th>Duracion</th><th>Estado</th></tr></thead><tbody>
    @foreach($dispositivo->asignaciones as $a)
    <tr>
        <td><a href="{{ route('empleados.show',$a->empleado) }}" style="color:var(--accent);text-decoration:none;font-weight:600">{{ $a->empleado?->nombre_completo ?? '—' }}</a></td>
        <td style="color:var(--muted)">{{ $a->empleado?->departamento ?? '—' }}</td>
        <td>{{ $a->fecha_asignacion->format('d/m/Y') }}</td>
        <td>{{ $a->fecha_devolucion?->format('d/m/Y') ?? '—' }}</td>
        <td>@if($a->fecha_devolucion){{ $a->fecha_asignacion->diffInDays($a->fecha_devolucion) }} dias@else<span style="color:var(--success);font-weight:600">{{ $a->fecha_asignacion->diffInDays(now()) }} dias (activa)</span>@endif</td>
        <td><span class="badge badge-{{ $a->estaActiva()?'success':'secondary' }}">{{ $a->estaActiva()?'Activa':'Finalizada' }}</span></td>
    </tr>
    @endforeach
    </tbody></table></div>
    @endif
</div>
@endsection