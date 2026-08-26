@extends('layouts.app')
@section('title','Editar Usuario')@section('page-title','Editar Usuario')
@section('content')
<div class="breadcrumb"><a href="{{ route('usuarios.index') }}">Usuarios</a><span class="sep">/</span><span>{{ $usuario->name }}</span></div>
<div class="card" style="max-width:560px">
    <form method="POST" action="{{ route('usuarios.update',$usuario) }}">@csrf @method('PUT')
        <div class="form-grid">
            <div class="form-group full"><label>Nombre completo *</label><input type="text" name="name" value="{{ old('name',$usuario->name) }}" required></div>
            <div class="form-group full"><label>Correo electronico *</label><input type="email" name="email" value="{{ old('email',$usuario->email) }}" required class="{{ $errors->has('email')?'is-invalid':'' }}">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group full">
                <label>Rol *</label>
                <select name="rol" required {{ $usuario->id===auth()->id()?'disabled':'' }}>
                    <option value="visualizador" {{ old('rol',$usuario->rol)==='visualizador'?'selected':'' }}>Visualizador — Solo lectura</option>
                    <option value="contabilidad" {{ old('rol',$usuario->rol)==='contabilidad'?'selected':'' }}>Contabilidad — Solicitudes de entrega</option>
                    <option value="admin"        {{ old('rol',$usuario->rol)==='admin'?'selected':'' }}>Administrador — Acceso total</option>
                </select>
                @if($usuario->id===auth()->id())<input type="hidden" name="rol" value="{{ $usuario->rol }}"><small style="color:var(--warning)">No puedes cambiar tu propio rol.</small>@endif
            </div>
        </div>
        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:18px;margin-top:8px">
            <p style="font-weight:600;font-size:13px;margin-bottom:12px">Cambiar contrasena <span style="font-weight:400;color:var(--muted)">(dejar vacio para mantener la actual)</span></p>
            <div class="form-grid">
                <div class="form-group"><label>Nueva contrasena</label><input type="password" name="password" class="{{ $errors->has('password')?'is-invalid':'' }}" placeholder="Minimo 8 caracteres">@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="form-group"><label>Confirmar</label><input type="password" name="password_confirmation"></div>
            </div>
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-primary">Guardar Cambios</button><a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a></div>
    </form>
</div>
@endsection