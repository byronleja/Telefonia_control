@extends('layouts.app')
@section('title', 'Departamentos')@section('page-title', 'Departamentos')
@section('topbar-actions')
    <a href="{{ route('departamentos.create') }}" class="btn btn-primary"><svg width="14" height="14" fill="none"
            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 4v16m8-8H4" />
        </svg>Nuevo Departamento</a>
@endsection
@section('content')
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Departamento</th>
                        <th>Descripcion</th>
                        <th style="text-align:center">Empleados</th>
                        <th style="text-align:center">Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departamentos as $d)
                        <tr>
                            <td style="font-weight:600">{{ $d->nombre }}</td>
                            <td style="color:var(--muted)">{{ $d->descripcion ?? '—' }}</td>
                            <td style="text-align:center"><span
                                    style="font-weight:700;color:var(--accent)">{{ $d->empleados_count }}</span></td>
                            <td style="text-align:center"><span
                                    class="badge badge-{{ $d->activo ? 'success' : 'secondary' }}">{{ $d->activo ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px">
                                    <a href="{{ route('departamentos.edit', $d) }}"
                                        class="btn btn-secondary btn-sm btn-icon"><svg width="14" height="14"
                                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg></a>
                                    @if ($d->empleados_count === 0)
                                        <form method="POST" action="{{ route('departamentos.destroy', $d) }}"
                                            onsubmit="return confirm('Eliminar departamento {{ $d->nombre }}?')">@csrf
                                            @method('DELETE')<button type="submit"
                                                class="btn btn-danger btn-sm btn-icon"><svg width="14" height="14"
                                                    fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg></button></form>
                                    @else
                                        <button class="btn btn-secondary btn-sm btn-icon" disabled
                                            title="Tiene empleados asignados"><svg width="14" height="14"
                                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty<tr>
                            <td colspan="5" style="text-align:center;color:var(--muted);padding:40px">No hay
                                departamentos. <a href="{{ route('departamentos.create') }}"
                                    style="color:var(--accent)">Crear el primero</a></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $departamentos->links() }}</div>
    </div>
@endsection
