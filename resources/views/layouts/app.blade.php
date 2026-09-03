<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>@yield('title','GestiCell') — Sistema de Gestion</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/layout.css') }}">
<link rel="stylesheet" href="{{ asset('css/components.css') }}">
<link rel="stylesheet" href="{{ asset('css/forms.css') }}">
<link rel="stylesheet" href="{{ asset('css/utilities.css') }}">
@stack('styles')
</head><body>
<aside class="sidebar">
    <div class="sidebar-logo"><h1>Gesti<span>Cell</span></h1><p>Gestion de Dispositivos</p></div>
    <nav class="sidebar-nav">

    {{-- ══════════════════════════════════════════
         ROL CONTABILIDAD: sidebar minimalista
    ══════════════════════════════════════════ --}}
    @if(auth()->user()?->esContabilidad())
        <div class="nav-section">Mis Solicitudes</div>
        <a href="{{ route('solicitudes.index') }}" class="nav-item {{ request()->routeIs('solicitudes.index')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Solicitudes de Entrega
        </a>
        <a href="{{ route('solicitudes.create') }}" class="nav-item {{ request()->routeIs('solicitudes.create')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
            Nueva Solicitud
        </a>

    {{-- ══════════════════════════════════════════
         ADMIN Y VISUALIZADOR: sidebar completo
    ══════════════════════════════════════════ --}}
    @else
        <div class="nav-section">Principal</div>
        <a href="{{ route('reportes.index') }}" class="nav-item {{ request()->routeIs('reportes.index')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>

        <div class="nav-section">Gestion</div>
        <a href="{{ route('empleados.index') }}" class="nav-item {{ request()->routeIs('empleados.*')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Empleados
        </a>
        <a href="{{ route('dispositivos.index') }}" class="nav-item {{ request()->routeIs('dispositivos.*')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Dispositivos
        </a>
        <a href="{{ route('asignaciones.index') }}" class="nav-item {{ request()->routeIs('asignaciones.*')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            Asignaciones
        </a>
        <a href="{{ route('renovaciones.entregar') }}" class="nav-item {{ request()->routeIs('renovaciones.entregar*')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Entrega por Renovacion
        </a>
        <a href="{{ route('renovaciones.index') }}" class="nav-item {{ request()->routeIs('renovaciones.*') && !request()->routeIs('renovaciones.entregar*')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Historial Renovaciones
        </a>

        <div class="nav-section">Bloques</div>
        <a href="{{ route('bloques.index') }}" class="nav-item {{ request()->routeIs('bloques.index','bloques.show','bloques.create','bloques.edit')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Bloques
        </a>
        <a href="{{ route('bloques.reporte') }}" class="nav-item {{ request()->routeIs('bloques.reporte')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Rep. por Bloque
        </a>

        <div class="nav-section">Herramientas</div>
        <a href="{{ route('solicitudes.index') }}" class="nav-item {{ request()->routeIs('solicitudes.*')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Solicitudes Entrega
            @php try { $__pend = \App\Models\SolicitudEntrega::where('estado','pendiente')->count(); } catch(\Exception $e) { $__pend = 0; } @endphp
            @if($__pend > 0)<span style="margin-left:auto;background:var(--danger);color:#fff;border-radius:20px;padding:1px 7px;font-size:10px;font-weight:700">{{ $__pend }}</span>@endif
        </a>
        <a href="{{ route('importar.index') }}" class="nav-item {{ request()->routeIs('importar.*')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Importar Masivo
        </a>
        <a href="{{ route('reportes.alertas') }}" class="nav-item {{ request()->routeIs('reportes.alertas')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Alertas Renovacion
        </a>

        <div class="nav-section">Reportes</div>
        <a href="{{ route('reportes.renovaciones') }}" class="nav-item {{ request()->routeIs('reportes.renovaciones')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Rep. Renovaciones
        </a>
        <a href="{{ route('reportes.dispositivos') }}" class="nav-item {{ request()->routeIs('reportes.dispositivos')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Rep. Dispositivos
        </a>
        <a href="{{ route('reportes.comprados') }}" class="nav-item {{ request()->routeIs('reportes.comprados')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Rep. Comprados
        </a>
        <a href="{{ route('reportes.equipos_danados') }}" class="nav-item {{ request()->routeIs('reportes.equipos_danados')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Equipos Danados / Baja
        </a>

        <div class="nav-section">Sistema</div>
        @if(auth()->user()?->esAdmin())
        <a href="{{ route('departamentos.index') }}" class="nav-item {{ request()->routeIs('departamentos.*')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Departamentos
        </a>
        <a href="{{ route('usuarios.index') }}" class="nav-item {{ request()->routeIs('usuarios.*')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Usuarios del Sistema
        </a>
        <a href="{{ route('configuracion.index') }}" class="nav-item {{ request()->routeIs('configuracion.*')?'active':'' }}">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Configuracion
        </a>
        @endif
    @endif

    </nav>
    <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,.07)">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
            <div style="width:34px;height:34px;border-radius:50%;background:var(--sidebar-active);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:14px;flex-shrink:0">{{ strtoupper(substr(auth()->user()?->name??'U',0,1)) }}</div>
            <div style="overflow:hidden">
                <p style="color:#e2e8f0;font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth()->user()?->name }}</p>
                <p style="color:#64748b;font-size:11px">{{ auth()->user()?->rol_label }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" style="width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#94a3b8;padding:7px;border-radius:7px;font-size:12px;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:6px" onmouseover="this.style.background='rgba(255,255,255,.12)'" onmouseout="this.style.background='rgba(255,255,255,.06)'">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Cerrar Sesion
            </button>
        </form>
    </div>
</aside>
<div class="main-wrap">
    <header class="topbar">
        <span class="topbar-title">@yield('page-title','Dashboard')</span>
        @if(!auth()->user()?->esContabilidad())
        <div style="display:flex;align-items:center;gap:12px;flex:1;max-width:420px;margin:0 24px">
            <form method="GET" action="{{ route('busqueda.index') }}" style="display:flex;align-items:center;width:100%;background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:0 12px;gap:8px">
                <svg width="15" height="15" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar empleado, serie, IMEI..." autocomplete="off" style="border:none;background:transparent;padding:8px 0;font-size:13px;width:100%;outline:none;color:var(--text)">
                @if(request('q'))<a href="{{ route('busqueda.index') }}" style="color:var(--muted);text-decoration:none;font-size:16px;line-height:1">x</a>@endif
            </form>
        </div>
        @endif
        <div class="topbar-actions">
            @if(auth()->user()?->esVisualizador())<span class="badge badge-secondary" style="margin-right:8px">Solo lectura</span>@endif
            @if(auth()->user()?->esContabilidad())<span class="badge badge-purple" style="margin-right:8px">Contabilidad</span>@endif
            @yield('topbar-actions')
        </div>
    </header>
    <main class="page-content">
        @if(session('success'))<div class="alert alert-success"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M5 13l4 4L19 7"/></svg><span>{{ session('success') }}</span></div>@endif
        @if(session('error'))<div class="alert alert-danger"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M6 18L18 6M6 6l12 12"/></svg><span>{{ session('error') }}</span></div>@endif
        @if(session('info'))<div class="alert alert-info"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('info') }}</span></div>@endif
        @yield('content')
    </main>
</div>
@stack('scripts')
</body></html>
