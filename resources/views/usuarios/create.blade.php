@extends('layouts.app')
@section('title','Nuevo Usuario')@section('page-title','Nuevo Usuario')
@section('content')
<div class="breadcrumb"><a href="{{ route('usuarios.index') }}">Usuarios</a><span class="sep">/</span><span>Nuevo</span></div>
<div class="card" style="max-width:560px">
    <form method="POST" action="{{ route('usuarios.store') }}">@csrf
        <div class="form-grid">
            <div class="form-group full"><label>Nombre completo *</label><input type="text" name="name" value="{{ old('name') }}" required class="{{ $errors->has('name')?'is-invalid':'' }}">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group full"><label>Correo electronico *</label><input type="email" name="email" value="{{ old('email') }}" required class="{{ $errors->has('email')?'is-invalid':'' }}">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group full">
                <label>Rol *</label>
                <select name="rol" required>
                    <option value="visualizador" {{ old('rol','visualizador')==='visualizador'?'selected':'' }}>Visualizador — Solo lectura</option>
                    <option value="contabilidad" {{ old('rol')==='contabilidad'?'selected':'' }}>Contabilidad — Solicitudes de entrega</option>
                    <option value="admin"        {{ old('rol')==='admin'?'selected':'' }}>Administrador — Acceso total</option>
                </select>
                <small><strong>Contabilidad:</strong> ingresa boletas y crea solicitudes de entrega a Sistemas.</small>
            </div>
            <div class="form-group"><label>Contrasena *</label><input type="password" name="password" required class="{{ $errors->has('password')?'is-invalid':'' }}" placeholder="Minimo 8 caracteres">@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><label>Confirmar contrasena *</label><input type="password" name="password_confirmation" required></div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-primary">Crear Usuario</button><a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a></div>
    </form>
</div>
@endsection