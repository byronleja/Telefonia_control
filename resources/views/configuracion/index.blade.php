@extends('layouts.app')
@section('title','Configuracion')
@section('page-title','Configuracion del Sistema')

@section('content')

<div class="card" style="max-width:600px">
    <div class="card-header">
        <span class="card-title">Parametros Generales</span>
    </div>

    <form method="POST" action="{{ route('configuracion.update') }}">
        @csrf @method('PUT')

        <div style="display:flex;flex-direction:column;gap:20px">

            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:20px">
                <p style="font-weight:700;font-size:13px;color:var(--text);margin-bottom:14px">
                    Identidad de la Empresa
                </p>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Nombre de la Empresa</label>
                        <input type="text" name="nombre_empresa"
                               value="{{ old('nombre_empresa', $configs['nombre_empresa']->valor ?? 'Gas Zeta, S.A.') }}"
                               required>
                        <small>Aparece en las cartas de custodia.</small>
                    </div>
                    <div class="form-group full">
                        <label>Departamento de Sistemas</label>
                        <input type="text" name="departamento_sistemas"
                               value="{{ old('departamento_sistemas', $configs['departamento_sistemas']->valor ?? 'Direccion de Sistemas') }}"
                               required>
                        <small>Aparece en el pie de las cartas de custodia.</small>
                    </div>
                </div>
            </div>

            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:20px">
                <p style="font-weight:700;font-size:13px;color:var(--text);margin-bottom:14px">
                    Ciclos de Renovacion
                </p>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Ciclo de renovacion (meses)</label>
                        <input type="number" name="ciclo_renovacion_meses"
                               value="{{ old('ciclo_renovacion_meses', $configs['ciclo_renovacion_meses']->valor ?? 18) }}"
                               min="1" max="120" required
                               style="font-size:18px;font-weight:700;color:var(--accent);text-align:center">
                        <small>Meses por defecto para nuevos dispositivos.</small>
                    </div>
                    <div class="form-group">
                        <label>Alerta anticipacion (meses)</label>
                        <input type="number" name="alerta_anticipacion_meses"
                               value="{{ old('alerta_anticipacion_meses', $configs['alerta_anticipacion_meses']->valor ?? 2) }}"
                               min="1" max="24" required
                               style="font-size:18px;font-weight:700;color:var(--warning);text-align:center">
                        <small>Meses antes del vencimiento para mostrar alerta.</small>
                    </div>
                </div>

                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px;margin-top:12px">
                    <p style="font-size:12px;color:#1e40af;line-height:1.6">
                        <strong>Ejemplo:</strong> Con ciclo de
                        <strong>{{ $configs['ciclo_renovacion_meses']->valor ?? 18 }} meses</strong>
                        y alerta de
                        <strong>{{ $configs['alerta_anticipacion_meses']->valor ?? 2 }} meses</strong>,
                        los dispositivos apareceran en alertas cuando queden
                        <strong>{{ $configs['alerta_anticipacion_meses']->valor ?? 2 }} mes(es) o menos</strong>
                        para su vencimiento.
                    </p>
                </div>
            </div>

        </div>

        <div class="form-actions" style="margin-top:20px">
            <button type="submit" class="btn btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                Guardar Configuracion
            </button>
        </div>
    </form>
</div>
@endsection
