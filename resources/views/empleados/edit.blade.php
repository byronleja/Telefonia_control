@extends('layouts.app')
@section('title','Editar Empleado')@section('page-title','Editar Empleado')
@section('content')
<div class="breadcrumb"><a href="{{ route('empleados.index') }}">Empleados</a><span class="sep">/</span><a href="{{ route('empleados.show',$empleado) }}">{{ $empleado->nombre_completo }}</a><span class="sep">/</span><span>Editar</span></div>
<div class="card">
    <form method="POST" action="{{ route('empleados.update',$empleado) }}">@csrf @method('PUT')
        <div class="form-grid">
            <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" value="{{ old('nombre',$empleado->nombre) }}" required></div>
            <div class="form-group"><label>Apellido *</label><input type="text" name="apellido" value="{{ old('apellido',$empleado->apellido) }}" required></div>
            <div class="form-group"><label>Codigo *</label><input type="text" name="codigo_empleado" value="{{ old('codigo_empleado',$empleado->codigo_empleado) }}" required class="{{ $errors->has('codigo_empleado')?'is-invalid':'' }}">@error('codigo_empleado')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label>Email *</label><input type="email" name="email" value="{{ old('email',$empleado->email) }}" required class="{{ $errors->has('email')?'is-invalid':'' }}">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group">
                <label>Departamento *</label>
                <select name="departamento" required>
                    <option value="">— Seleccionar —</option>
                    @foreach($departamentos as $dep)
                    <option value="{{ $dep->nombre }}" {{ old('departamento',$empleado->departamento)===$dep->nombre?'selected':'' }}>{{ $dep->nombre }}</option>
                    @endforeach
                    @if(!$departamentos->contains('nombre',$empleado->departamento))
                    <option value="{{ $empleado->departamento }}" selected>{{ $empleado->departamento }} (inactivo)</option>
                    @endif
                </select>
            </div>
            <div class="form-group"><label>Cargo *</label><input type="text" name="cargo" value="{{ old('cargo',$empleado->cargo) }}" required></div>
            <div class="form-group"><label>Telefono</label><input type="text" name="telefono" value="{{ old('telefono',$empleado->telefono) }}"></div>
            <div class="form-group"><label>Estado *</label><select name="estado" required><option value="activo" {{ old('estado',$empleado->estado)==='activo'?'selected':'' }}>Activo</option><option value="inactivo" {{ old('estado',$empleado->estado)==='inactivo'?'selected':'' }}>Inactivo</option></select></div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-primary">Guardar Cambios</button><a href="{{ route('empleados.show',$empleado) }}" class="btn btn-secondary">Cancelar</a></div>
    </form>
</div>
@endsection