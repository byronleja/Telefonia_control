@extends('layouts.app')
@section('title', 'Nuevo Departamento')@section('page-title', 'Nuevo Departamento')
@section('content')
    <div class="breadcrumb">
        <a href="{{ route('departamentos.index') }}">Departamentos</a><span class="sep">/</span><span>Nuevo</span>
    </div>
    <div class="card" style="max-width:520px">
        <form method="POST" action="{{ route('departamentos.store') }}">@csrf
            <div class="form-grid">
                <div class="form-group full"><label>Nombre del Departamento *</label><input type="text" name="nombre"
                        value="{{ old('nombre') }}" required placeholder="Ej: Recursos Humanos, Tecnologia..."
                        class="{{ $errors->has('nombre') ? 'is-invalid' : '' }}">
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group full"><label>Descripcion</label><input type="text" name="descripcion"
                        value="{{ old('descripcion') }}" placeholder="Breve descripcion (opcional)"></div>
                <div class="form-group full">
                    <label
                        style="display:flex;align-items:center;gap:10px;font-size:13px;font-weight:400;text-transform:none;letter-spacing:0;cursor:pointer"><input
                            type="checkbox" name="activo" value="1" {{ old('activo', '1') === '1' ? 'checked' : '' }}
                            style="width:auto;margin:0"><span>Departamento activo (aparece en formularios de
                            empleados)</span></label>
                </div>
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary">Crear Departamento</button><a
                    href="{{ route('departamentos.index') }}" class="btn btn-secondary">Cancelar</a></div>
        </form>
    </div>
@endsection
