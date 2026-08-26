@extends('layouts.app')
@section('title','Editar Dispositivo')
@section('page-title','Editar Dispositivo')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dispositivos.index') }}">Dispositivos</a>
    <span class="sep">/</span>
    <a href="{{ route('dispositivos.show',$dispositivo) }}">{{ $dispositivo->nombre_completo }}</a>
    <span class="sep">/</span>
    <span>Editar</span>
</div>

<div class="card">
    <form method="POST" action="{{ route('dispositivos.update',$dispositivo) }}">
        @csrf @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label>Número de Serie *</label>
                <input type="text" name="numero_serie"
                       value="{{ old('numero_serie',$dispositivo->numero_serie) }}"
                       class="{{ $errors->has('numero_serie') ? 'is-invalid' : '' }}" required>
                @error('numero_serie')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Marca *</label>
                <input type="text" name="marca" value="{{ old('marca',$dispositivo->marca) }}" required>
            </div>
            <div class="form-group">
                <label>Modelo *</label>
                <input type="text" name="modelo" value="{{ old('modelo',$dispositivo->modelo) }}" required>
            </div>
            <div class="form-group">
                <label>IMEI</label>
                <input type="text" name="imei" value="{{ old('imei',$dispositivo->imei) }}"
                       class="{{ $errors->has('imei') ? 'is-invalid' : '' }}">
                @error('imei')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Tipo *</label>
                <select name="tipo" required>
                    @foreach(['smartphone' => 'Smartphone','basico' => 'Básico','tablet' => 'Tablet'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('tipo',$dispositivo->tipo) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Estado *</label>
                <select name="estado" required>
                    @foreach(['disponible' => 'Disponible','asignado' => 'Asignado','en_reparacion' => 'En reparación','dado_de_baja' => 'Dado de baja'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('estado',$dispositivo->estado) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>N° Telefónico</label>
                <input type="text" name="numero_telefonico" value="{{ old('numero_telefonico',$dispositivo->numero_telefonico) }}">
            </div>
            <div class="form-group">
                <label>Operadora</label>
                <input type="text" name="operadora" value="{{ old('operadora',$dispositivo->operadora) }}">
            </div>
            <div class="form-group">
                <label>Color</label>
                <input type="text" name="color" value="{{ old('color',$dispositivo->color) }}">
            </div>
            <div class="form-group">
                <label>Fecha de Compra</label>
                <input type="date" name="fecha_compra" value="{{ old('fecha_compra',$dispositivo->fecha_compra?->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label>Fecha de Asignación</label>
                <input type="date" name="fecha_asignacion" value="{{ old('fecha_asignacion',$dispositivo->fecha_asignacion?->format('Y-m-d')) }}">
                <small>Inicio del ciclo de renovación.</small>
            </div>
            <div class="form-group">
                <label>Costo (Q)</label>
                <input type="number" name="costo" value="{{ old('costo',$dispositivo->costo) }}" step="0.01" min="0">
            </div>
        </div>

        {{-- Meses de renovación --}}
        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:18px;margin-top:16px">
            <p style="font-weight:600;font-size:13px;color:var(--text);margin-bottom:4px">Ciclo de renovación *</p>
            <p style="color:var(--muted);font-size:12px;margin-bottom:14px">
                Meses de vida útil de este dispositivo. El valor global es <strong>{{ $mesesDefault }} meses</strong>.
            </p>
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                <div class="form-group" style="max-width:160px">
                    <label>Meses *</label>
                    <input type="number" name="meses_renovacion"
                           value="{{ old('meses_renovacion',$dispositivo->meses_renovacion) }}"
                           min="1" max="120" required
                           class="{{ $errors->has('meses_renovacion') ? 'is-invalid' : '' }}"
                           style="font-size:16px;font-weight:700;color:var(--accent);text-align:center">
                    @error('meses_renovacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label>Presets rápidos</label>
                    <div style="display:flex;gap:8px;margin-top:6px">
                        @foreach([12 => '1 año', 18 => '18 m', 24 => '2 años', 36 => '3 años'] as $m => $lbl)
                            <button type="button" class="btn btn-secondary btn-sm"
                                    onclick="document.querySelector('[name=meses_renovacion]').value={{ $m }}">
                                {{ $lbl }}
                            </button>
                        @endforeach
                    </div>
                </div>
                @if($dispositivo->fecha_asignacion)
                    @php $mr = $dispositivo->meses_para_renovacion; @endphp
                    <div style="padding:10px 14px;border-radius:8px;background:{{ $mr <= 0 ? '#fee2e2' : ($mr <= 2 ? '#fef9c3' : '#dcfce7') }}">
                        <p style="font-size:11px;font-weight:600;color:var(--muted);margin-bottom:2px">ESTADO ACTUAL</p>
                        <p style="font-weight:700;color:{{ $mr <= 0 ? 'var(--danger)' : ($mr <= 2 ? 'var(--warning)' : 'var(--success)') }}">
                            {{ $mr <= 0 ? '¡Vencido hace '.abs($mr).' mes(es)!' : $mr.' mes(es) restantes' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Asignación --}}
        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:18px;margin-top:16px">
            <p style="font-weight:600;font-size:13px;color:var(--text);margin-bottom:4px">Asignación de empleado</p>
            <p style="color:var(--muted);font-size:12px;margin-bottom:14px">
                Un empleado solo puede tener <strong>un dispositivo asignado</strong> a la vez.
                Si ya tiene uno, debes indicar la razón de excepción.
            </p>
            <div class="form-grid">
                <div class="form-group">
                    <label>Empleado</label>
                    <select name="empleado_id" id="sel-empleado" onchange="verificarEmpleado(this)"
                            data-actual="{{ $dispositivo->empleado_id }}">
                        <option value="">— Sin asignar —</option>
                        @foreach($empleados as $emp)
                            @php
                                $tieneOtro = $emp->dispositivos()
                                    ->where('estado','asignado')
                                    ->where('id','!=',$dispositivo->id)
                                    ->exists();
                            @endphp
                            <option value="{{ $emp->id }}"
                                    data-tiene="{{ $tieneOtro ? '1' : '0' }}"
                                    {{ old('empleado_id',$dispositivo->empleado_id) == $emp->id ? 'selected' : '' }}>
                                {{ $emp->nombre_completo }} ({{ $emp->codigo_empleado }})
                            </option>
                        @endforeach
                    </select>
                    @error('empleado_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" id="grupo-excepcion" style="display:none">
                    <label>Razón de excepción *</label>
                    <select name="razon_excepcion">
                        <option value="">— Seleccionar —</option>
                        @foreach($razonesExcepcion as $val => $lbl)
                            <option value="{{ $val }}" {{ old('razon_excepcion') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <small style="color:var(--warning)">⚠ Este empleado ya tiene un dispositivo asignado.</small>
                    @error('razon_excepcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="form-group full" style="margin-top:16px">
            <label>Observaciones</label>
            <textarea name="observaciones">{{ old('observaciones',$dispositivo->observaciones) }}</textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Actualizar Dispositivo</button>
            <a href="{{ route('dispositivos.show',$dispositivo) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function verificarEmpleado(select) {
    const opt   = select.options[select.selectedIndex];
    const tiene = opt && opt.dataset.tiene === '1';
    document.getElementById('grupo-excepcion').style.display = tiene ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', () => verificarEmpleado(document.getElementById('sel-empleado')));
</script>
@endpush
@endsection
