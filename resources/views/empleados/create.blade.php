@extends('layouts.app')
@section('title','Nuevo Empleado')@section('page-title','Nuevo Empleado')
@section('content')
<div class="breadcrumb"><a href="{{ route('empleados.index') }}">Empleados</a><span class="sep">/</span><span>Nuevo</span></div>
<div class="card">
    <form method="POST" action="{{ route('empleados.store') }}">@csrf
        <div class="form-grid">
            <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" value="{{ old('nombre') }}" required></div>
            <div class="form-group"><label>Apellido *</label><input type="text" name="apellido" value="{{ old('apellido') }}" required></div>
            <div class="form-group"><label>Codigo *</label><input type="text" name="codigo_empleado" value="{{ old('codigo_empleado') }}" required placeholder="EMP-001" class="{{ $errors->has('codigo_empleado')?'is-invalid':'' }}">@error('codigo_empleado')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label>Email *</label><input type="email" name="email" value="{{ old('email') }}" required class="{{ $errors->has('email')?'is-invalid':'' }}">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group">
                <label>Departamento *</label>
                <select name="departamento" required class="{{ $errors->has('departamento')?'is-invalid':'' }}">
                    <option value="">— Seleccionar departamento —</option>
                    @foreach($departamentos as $dep)
                    <option value="{{ $dep->nombre }}" {{ old('departamento')===$dep->nombre?'selected':'' }}>{{ $dep->nombre }}@if($dep->descripcion) — {{ $dep->descripcion }}@endif</option>
                    @endforeach
                </select>
                @error('departamento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($departamentos->isEmpty())<small style="color:var(--warning)">No hay departamentos. <a href="{{ route('departamentos.create') }}" style="color:var(--accent)">Crear uno primero</a></small>@endif
            </div>
            <div class="form-group"><label>Cargo *</label><input type="text" name="cargo" value="{{ old('cargo') }}" required></div>
            <div class="form-group"><label>Telefono</label><input type="text" name="telefono" value="{{ old('telefono') }}"></div>
            <div class="form-group"><label>Estado *</label><select name="estado" required><option value="activo" {{ old('estado','activo')==='activo'?'selected':'' }}>Activo</option><option value="inactivo" {{ old('estado')==='inactivo'?'selected':'' }}>Inactivo</option></select></div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-primary">Registrar Empleado</button><a href="{{ route('empleados.index') }}" class="btn btn-secondary">Cancelar</a></div>
    </form>
</div>
@endsection