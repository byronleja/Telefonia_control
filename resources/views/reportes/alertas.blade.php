@extends('layouts.app')
@section('title','Alertas de Renovacion')@section('page-title','Alertas de Renovacion')
@section('topbar-actions')<a href="{{ route('exportar.alertas') }}" class="btn btn-success btn-sm">Excel</a>@endsection
@section('content')
@if(!$tieneAsignacion)
<div class="alert alert-warning"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Ejecuta <code>php artisan migrate</code> para habilitar alertas.</span></div>
@elseif($dispositivos->isEmpty())
<div class="alert alert-success"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M5 13l4 4L19 7"/></svg><span>Ningun dispositivo en alerta. Todos los ciclos estan vigentes.</span></div>
@else
<div class="card">
    <div class="card-header"><span class="card-title">Equipos proximos a vencer o vencidos</span><span class="badge badge-danger">{{ $dispositivos->count() }}</span></div>
    <div class="table-wrap"><table>
        <thead><tr><th>N Serie</th><th>Dispositivo</th><th>Empleado</th><th>Depto.</th><th>F. Asignacion</th><th>F. Vencimiento</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @foreach($dispositivos as $d)
        @php $mr=$d->meses_para_renovacion; @endphp
        <tr><td style="font-weight:600">{{ $d->numero_serie }}</td><td>{{ $d->nombre_completo }}</td><td>{{ $d->empleado?->nombre_completo??'—' }}</td><td style="color:var(--muted)">{{ $d->empleado?->departamento??'—' }}</td><td>{{ $d->fecha_asignacion->format('d/m/Y') }}</td><td>{{ $d->fecha_vencimiento_renovacion?->format('d/m/Y')??'—' }}</td><td><span class="badge badge-{{ $mr<=0?'danger':'warning' }}">{{ $mr<=0?'Vencido hace '.abs($mr).'m':$mr.' mes(es) restantes' }}</span></td><td><a href="{{ route('dispositivos.show',$d) }}" class="btn btn-secondary btn-sm">Ver</a></td></tr>
        @endforeach
        </tbody>
    </table></div>
</div>
@endif
@endsection