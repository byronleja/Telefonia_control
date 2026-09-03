@extends('layouts.app')
@section('title',$bloque->nombre)
@section('page-title',$bloque->nombre)
@section('topbar-actions')
@if(auth()->user()?->esAdmin())
<a href="{{ route('bloques.edit',$bloque) }}" class="btn btn-secondary btn-sm">Editar Bloque</a>
@endif
@endsection
@section('content')
<div class="breadcrumb"><a href="{{ route('bloques.index') }}">Bloques</a><span class="sep">/</span><span>{{ $bloque->nombre }}</span></div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <div class="stat-card blue" style="border-top:4px solid {{ $bloque->color_etiqueta }}">
        <div class="stat-value">{{ $bloque->dispositivos_count }}</div><div class="stat-label">Total dispositivos</div>
    </div>
    <div class="stat-card green">
        <div class="stat-value">{{ $bloque->asignados_count }}</div><div class="stat-label">Asignados</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-value">{{ $bloque->disponibles_count }}</div><div class="stat-label">Disponibles</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-value">Q {{ number_format($bloque->costo_total_mensual,2) }}</div>
        <div class="stat-label">Costo mensual total</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
    <div class="card" style="margin-bottom:0">
        <div class="card-header"><span class="card-title">Informacion del Bloque</span>
            <span class="badge badge-{{ $bloque->gama_badge }}">{{ $bloque->gama_label }}</span>
        </div>
        <div class="detail-grid">
            <div class="detail-item"><label>Nombre</label><p style="font-weight:700">{{ $bloque->nombre }}</p></div>
            <div class="detail-item"><label>Gama</label><p>{{ $bloque->gama_label }}</p></div>
            <div class="detail-item"><label>Costo mensual / linea</label>
                <p style="font-size:20px;font-weight:800;color:var(--accent)">Q {{ number_format($bloque->costo_mensual_linea,2) }}</p>
            </div>
            <div class="detail-item"><label>Operadora</label><p>{{ $bloque->operadora ?? '—' }}</p></div>
            @if($bloque->descripcion)
            <div class="detail-item" style="grid-column:1/-1"><label>Descripcion</label><p>{{ $bloque->descripcion }}</p></div>
            @endif
        </div>
    </div>
    <div class="card" style="margin-bottom:0">
        <div class="card-header"><span class="card-title">Desglose de costos</span></div>
        <div style="display:flex;flex-direction:column;gap:10px">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:var(--surface-2);border-radius:8px">
                <span style="font-size:13px;color:var(--text-2)">Costo por equipo/mes</span>
                <span style="font-weight:700;color:var(--accent)">Q {{ number_format($bloque->costo_mensual_linea,2) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:var(--surface-2);border-radius:8px">
                <span style="font-size:13px;color:var(--text-2)">Equipos asignados activos</span>
                <span style="font-weight:700">{{ $bloque->asignados_count }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px">
                <span style="font-size:13px;font-weight:600;color:var(--accent)">Total mensual del bloque</span>
                <span style="font-weight:800;font-size:18px;color:var(--accent)">Q {{ number_format($bloque->costo_total_mensual,2) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:var(--surface-2);border-radius:8px">
                <span style="font-size:13px;color:var(--text-2)">Costo anual proyectado</span>
                <span style="font-weight:700;color:var(--success)">Q {{ number_format($bloque->costo_total_mensual*12,2) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Dispositivos del bloque --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Dispositivos en este bloque ({{ $dispositivos->total() }})</span>
        @if(auth()->user()?->esAdmin())
        <a href="{{ route('dispositivos.index',['bloque_id'=>$bloque->id]) }}" class="btn btn-secondary btn-sm">Ver en listado</a>
        @endif
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>N Serie</th><th>Dispositivo</th><th>Estado</th><th>Empleado</th><th>Departamento</th>
        @if(auth()->user()?->esAdmin())<th>Cambiar bloque</th>@endif</tr></thead>
        <tbody>
        @forelse($dispositivos as $d)
        @php $em=['disponible'=>'success','asignado'=>'info','en_reparacion'=>'warning','dado_de_baja'=>'danger']; @endphp
        <tr>
            <td style="font-weight:600">{{ $d->numero_serie }}</td>
            <td><a href="{{ route('dispositivos.show',$d) }}" style="color:var(--accent);text-decoration:none">{{ $d->nombre_completo }}</a></td>
            <td><span class="badge badge-{{ $em[$d->estado]??'secondary' }}">{{ str_replace('_',' ',ucfirst($d->estado)) }}</span></td>
            <td>{{ $d->empleado?->nombre_completo ?? '—' }}</td>
            <td style="color:var(--muted)">{{ $d->empleado?->departamento ?? '—' }}</td>
            @if(auth()->user()?->esAdmin())
            <td>
                <form method="POST" action="{{ route('bloques.cambiar',$d) }}" style="display:flex;gap:6px;align-items:center">
                    @csrf
                    <select name="bloque_id" style="font-size:12px;padding:4px 8px;border:1px solid var(--border-2);border-radius:6px;background:var(--surface)">
                        <option value="">Sin bloque</option>
                        @foreach(\App\Models\Bloque::activos()->get() as $bl)
                        <option value="{{ $bl->id }}" {{ $d->bloque_id===$bl->id?'selected':'' }}>{{ $bl->nombre }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Mover</button>
                </form>
            </td>
            @endif
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">Sin dispositivos en este bloque.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $dispositivos->links() }}
</div>

{{-- Historial de movimientos --}}
@if($historial->isNotEmpty())
<div class="card">
    <div class="card-header"><span class="card-title">Historial de Asignaciones en este Bloque</span></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Empleado</th><th>Dispositivo</th><th>F. Asignacion</th><th>F. Devolucion</th><th>Asignado por</th></tr></thead>
        <tbody>
        @foreach($historial as $a)
        <tr>
            <td style="font-weight:600">{{ $a->empleado?->nombre_completo ?? '—' }}</td>
            <td>{{ $a->dispositivo?->nombre_completo ?? '—' }}</td>
            <td>{{ $a->fecha_asignacion->format('d/m/Y') }}</td>
            <td>{{ $a->fecha_devolucion?->format('d/m/Y') ?? '—' }}</td>
            <td style="color:var(--muted)">{{ $a->asignado_por ?? '—' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table></div>
</div>
@endif
@endsection