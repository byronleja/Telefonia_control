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
            <a href="{{ route('importar.plantilla') }}" class="btn btn-success btn-sm">Descargar Plantilla</a>
        </div>

        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 14px;margin-bottom:16px">
            <p style="font-size:12px;font-weight:600;color:#1e40af;margin-bottom:6px">Campos requeridos:</p>
            <ul style="font-size:12px;color:#1e40af;padding-left:16px;line-height:1.9">
                <li><strong>numero_serie</strong> — unico</li>
                <li><strong>marca</strong> y <strong>modelo</strong></li>
                <li><strong>tipo</strong> — smartphone, basico, tablet</li>
                <li><strong>bloque</strong> — nombre exacto del bloque (ver lista abajo)</li>
                <li><strong>meses_renovacion</strong> — opcional, usa el global si esta vacio</li>
                <li><strong>estado</strong> — disponible, asignado (defecto: disponible)</li>
            </ul>
        </div>

        {{-- Bloques disponibles --}}
        <div style="margin-bottom:16px">
            <p style="font-size:12px;font-weight:600;color:var(--text-2);margin-bottom:8px">Bloques disponibles para usar en la columna "bloque":</p>
            <div style="display:flex;flex-wrap:wrap;gap:6px">
                @foreach($bloques as $b)
                <span style="background:{{ $b->color_etiqueta }}20;border:1px solid {{ $b->color_etiqueta }};border-radius:6px;padding:3px 10px;font-size:12px;font-weight:600;color:{{ $b->color_etiqueta }}">
                    {{ $b->nombre }}
                </span>
                @endforeach
                @if($bloques->isEmpty())
                <span style="color:var(--danger);font-size:12px">No hay bloques. <a href="{{ route('bloques.create') }}" style="color:var(--accent)">Crea uno primero.</a></span>
                @endif
            </div>
        </div>

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
                <input type="file" name="archivo" accept=".csv,.xlsx,.txt" required
                       class="{{ $errors->has('archivo')?'is-invalid':'' }}">
                @error('archivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small>Maximo 5 MB.</small>
            </div>
            <button type="submit" class="btn btn-primary">Importar Dispositivos</button>
        </form>
    </div>

    {{-- Empleados --}}
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span class="card-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Importar Empleados
            </span>
            <a href="{{ route('importar.empleados.plantilla') }}" class="btn btn-success btn-sm">Descargar Plantilla</a>
        </div>
        <p style="color:var(--muted);font-size:13px;margin-bottom:16px">
            Campos obligatorios: nombre, apellido, codigo_empleado, email, departamento, cargo.
        </p>
        <div style="margin-top:20px">
            <div class="info-box" style="margin-bottom:16px">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                El formulario completo incluye la guia de campos y lista de departamentos.
            </div>
            <a href="{{ route('importar.empleados') }}" class="btn btn-primary">Ir al formulario completo</a>
        </div>
    </div>

</div>
@endsection