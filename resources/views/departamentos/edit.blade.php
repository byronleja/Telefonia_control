@extends('layouts.app')
@section('title', 'Editar Departamento')@section('page-title', 'Editar Departamento')
@section('content')
    <div class="breadcrumb">
        <a href="{{ route('departamentos.index') }}">Departamentos</a><span
            class="sep">/</span><span>{{ $departamento->nombre }}</span>
    </div>
    <div class="card" style="max-width:520px">
        @if ($totalEmpleados > 0)
            <div class="alert alert-info"><svg width="16" height="16" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                    <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg><span>Este departamento tiene <strong>{{ $totalEmpleados }}</strong> empleado(s). Si cambias el
                    nombre, se actualizara en todos ellos automaticamente.</span></div>
        @endif
        <form method="POST" action="{{ route('departamentos.update', $departamento) }}">@csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group full"><label>Nombre del Departamento *</label><input type="text" name="nombre"
                        value="{{ old('nombre', $departamento->nombre) }}" required
                        class="{{ $errors->has('nombre') ? 'is-invalid' : '' }}">
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group full"><label>Descripcion</label><input type="text" name="descripcion"
                        value="{{ old('descripcion', $departamento->descripcion) }}"
                        placeholder="Breve descripcion (opcional)"></div>
                <div class="form-group full"><label
                        style="display:flex;align-items:center;gap:10px;font-size:13px;font-weight:400;text-transform:none;letter-spacing:0;cursor:pointer"><input
                            type="checkbox" name="activo" value="1"
                            {{ old('activo', $departamento->activo ? '1' : '0') === '1' ? 'checked' : '' }}
                            style="width:auto;margin:0"><span>Departamento activo</span></label></div>
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary">Guardar Cambios</button><a
                    href="{{ route('departamentos.index') }}" class="btn btn-secondary">Cancelar</a></div>
        </form>
    </div>
    @if ($totalEmpleados === 0)
        <div class="card" style="max-width:520px;background:var(--surface-2)">
            <p style="font-size:13px;color:var(--muted);margin-bottom:14px">Este departamento no tiene empleados. Puede
                eliminarlo si ya no es necesario.</p>
            <form method="POST" action="{{ route('departamentos.destroy', $departamento) }}"
                onsubmit="return confirm('Eliminar este departamento definitivamente?')">@csrf @method('DELETE')<button
                    type="submit" class="btn btn-danger btn-sm">Eliminar Departamento</button></form>
        </div>
    @endif
@endsection
