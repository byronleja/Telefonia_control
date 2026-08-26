<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>GestiCell — Iniciar Sesion</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);min-height:100vh;display:flex;align-items:center;justify-content:center}.card{background:#fff;border-radius:16px;padding:40px 44px;width:100%;max-width:420px;box-shadow:0 25px 60px rgba(0,0,0,.35)}.logo{text-align:center;margin-bottom:30px}.logo h1{font-family:'Plus Jakarta Sans',sans-serif;font-size:28px;font-weight:800;color:#1e293b}.logo h1 span{color:#2563eb}.logo p{color:#94a3b8;font-size:13px;margin-top:4px}.fg{margin-bottom:18px}label{display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px}input[type=email],input[type=password]{width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;font-family:'Inter',sans-serif;outline:none;transition:border-color .15s,box-shadow .15s;color:#1e293b}input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1)}.is-invalid{border-color:#dc2626!important}.invalid-feedback{color:#dc2626;font-size:12px;margin-top:4px}.remember{display:flex;align-items:center;gap:8px;font-size:13px;color:#475569}.remember input{width:auto}.btn-login{width:100%;padding:11px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:background .15s;margin-top:22px}.btn-login:hover{background:#1d4ed8}.alert-error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:18px}.hint{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;font-size:12px;color:#1e40af;margin-top:16px}.hint strong{display:block;margin-bottom:4px}.footer-text{text-align:center;font-size:12px;color:#94a3b8;margin-top:24px}</style>
</head><body><div class="card">
<div class="logo"><h1>Gesti<span>Cell</span></h1><p>Sistema de Gestion de Dispositivos — Gas Zeta, S.A.</p></div>
@if(session('error'))<div class="alert-error">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert-error">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('login') }}">@csrf
<div class="fg"><label>Correo electronico</label><input type="email" name="email" value="{{ old('email') }}" class="{{ $errors->has('email')?'is-invalid':'' }}" placeholder="usuario@empresa.com" autocomplete="email" autofocus required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="fg"><label>Contrasena</label><input type="password" name="password" placeholder="••••••••" autocomplete="current-password" required></div>
<label class="remember"><input type="checkbox" name="remember" value="1" {{ old('remember')?'checked':'' }}>Mantener sesion iniciada</label>
<button type="submit" class="btn-login">Iniciar Sesion</button>
</form>
<div class="hint"><strong>Acceso inicial:</strong>admin@gesticell.com / Admin1234!<br>Cambia la contrasena despues del primer ingreso.</div>
<div class="footer-text">GestiCell v2.0 &mdash; {{ now()->year }}</div>
</div></body></html>