@extends('layouts.app')
@section('title','Importar Empleados')
@section('page-title','Importar Empleados Masivo')

@section('content')

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

    {{-- Formulario de importación --}}
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span class="card-title">Subir archivo CSV o Excel</span>
            <a href="{{ route('importar.empleados.plantilla') }}" class="btn btn-success btn-sm">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Descargar Plantilla
            </a>
        </div>

        @if(session('errores_importacion') && count(session('errores_importacion')))
        <div class="alert alert-warning" style="flex-direction:column;align-items:flex-start">
            <strong>Advertencias durante la importacion:</strong>
            <ul style="margin-top:8px;padding-left:18px">
                @foreach(session('errores_importacion') as $e)
                <li style="font-size:12px;margin-bottom:4px">{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('importar.empleados.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group" style="margin-bottom:20px">
                <label>Archivo (.csv o .xlsx) *</label>
                <input type="file" name="archivo" accept=".csv,.xlsx,.txt"
                       class="{{ $errors->has('archivo') ? 'is-invalid' : '' }}" required>
                @error('archivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small>Maximo 5 MB.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Importar Empleados
                </button>
                <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    {{-- Guia de campos --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card" style="margin-bottom:0">
            <div class="card-header"><span class="card-title">Campos del archivo</span></div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Columna</th><th>Obligatorio</th><th>Descripcion</th></tr>
                    </thead>
                    <tbody>
                        <tr><td style="font-weight:600;color:var(--accent)">nombre</td><td><span class="badge badge-danger">Si</span></td><td>Nombre del empleado</td></tr>
                        <tr><td style="font-weight:600;color:var(--accent)">apellido</td><td><span class="badge badge-danger">Si</span></td><td>Apellido del empleado</td></tr>
                        <tr><td style="font-weight:600;color:var(--accent)">codigo_empleado</td><td><span class="badge badge-danger">Si</span></td><td>Unico. Ej: EMP-001</td></tr>
                        <tr><td style="font-weight:600;color:var(--accent)">email</td><td><span class="badge badge-danger">Si</span></td><td>Correo unico y valido</td></tr>
                        <tr><td style="font-weight:600;color:var(--accent)">departamento</td><td><span class="badge badge-danger">Si</span></td><td>Debe existir o se crea</td></tr>
                        <tr><td style="font-weight:600;color:var(--accent)">cargo</td><td><span class="badge badge-danger">Si</span></td><td>Puesto del empleado</td></tr>
                        <tr><td style="font-weight:600;color:var(--muted)">telefono</td><td><span class="badge badge-secondary">No</span></td><td>Numero de telefono</td></tr>
                        <tr><td style="font-weight:600;color:var(--muted)">estado</td><td><span class="badge badge-secondary">No</span></td><td>activo / inactivo (defecto: activo)</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-bottom:0">
            <div class="card-header"><span class="card-title">Departamentos disponibles</span></div>
            @if($departamentos->isEmpty())
            <p style="color:var(--muted);font-size:13px;text-align:center;padding:16px">
                No hay departamentos creados.
                <a href="{{ route('departamentos.create') }}" style="color:var(--accent)">Crear uno primero</a>
            </p>
            @else
            <div style="display:flex;flex-wrap:wrap;gap:8px;padding:4px 0">
                @foreach($departamentos as $d)
                <span class="badge badge-info" style="font-size:12px">{{ $d->nombre }}</span>
                @endforeach
            </div>
            <p style="color:var(--muted);font-size:11px;margin-top:10px">
                Si el departamento del archivo no existe, sera creado automaticamente.
            </p>
            @endif
        </div>
    </div>

</div>
@endsection
