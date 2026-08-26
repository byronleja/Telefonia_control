@extends('layouts.app')
@section('title','Importar Masivo')
@section('page-title','Importar Masivo')

@section('content')

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

    {{-- Dispositivos --}}
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span class="card-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px"><path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Importar Dispositivos
            </span>
        </div>
        <p style="color:var(--muted);font-size:13px;margin-bottom:16px">
            Carga masiva de dispositivos desde archivo CSV o Excel.
            Campos obligatorios: numero_serie, marca, modelo, tipo, meses_renovacion.
        </p>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="{{ route('importar.plantilla') }}" class="btn btn-success btn-sm">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Descargar Plantilla
            </a>
            <a href="{{ route('importar.index') }}#form-dispositivos" class="btn btn-primary btn-sm">Importar ahora</a>
        </div>

        <div id="form-dispositivos" style="margin-top:20px;border-top:1px solid var(--border);padding-top:16px">
            @if(session('errores_importacion') && count(session('errores_importacion')))
            <div class="alert alert-warning" style="flex-direction:column;align-items:flex-start">
                <strong>Advertencias:</strong>
                <ul style="margin-top:8px;padding-left:18px">
                    @foreach(session('errores_importacion') as $e)
                    <li style="font-size:12px;margin-bottom:4px">{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form method="POST" action="{{ route('importar.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group" style="margin-bottom:14px">
                    <label>Archivo (.csv o .xlsx) *</label>
                    <input type="file" name="archivo" accept=".csv,.xlsx,.txt"
                           class="{{ $errors->has('archivo') ? 'is-invalid' : '' }}" required>
                    @error('archivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small>Maximo 5 MB.</small>
                </div>
                <button type="submit" class="btn btn-primary">Importar Dispositivos</button>
            </form>
        </div>
    </div>

    {{-- Empleados --}}
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span class="card-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Importar Empleados
            </span>
        </div>
        <p style="color:var(--muted);font-size:13px;margin-bottom:16px">
            Carga masiva de empleados desde archivo CSV o Excel.
            Campos obligatorios: nombre, apellido, codigo_empleado, email, departamento, cargo.
        </p>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="{{ route('importar.empleados.plantilla') }}" class="btn btn-success btn-sm">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Descargar Plantilla
            </a>
            <a href="{{ route('importar.empleados') }}" class="btn btn-primary btn-sm">Ver formulario completo</a>
        </div>
        <div style="margin-top:20px;border-top:1px solid var(--border);padding-top:16px">
            <div class="info-box" style="margin-bottom:0">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                El formulario completo de importacion de empleados incluye la lista de departamentos disponibles y guia de campos.
            </div>
        </div>
    </div>

</div>
@endsection
