<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Clínica Dental Mula')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>

@auth
    <div class="app-wrap">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <a href="{{ route('home') }}">
                    <svg viewBox="0 0 32 32" fill="none"><path d="M8 6c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v4c0 5.5-3.6 10.2-8.5 11.8V26h2.5a1 1 0 010 2h-7a1 1 0 010-2H13.5v-4.2C8.6 20.2 5 15.5 5 10V6z" fill="white" fill-opacity=".9"/></svg>
                    <div>
                        Dental Mula
                        <span>Panel de gestión</span>
                    </div>
                </a>
            </div>
            <nav class="sidebar-nav">
                @if(auth()->user()->isGestor())
                    <div class="nav-section">Principal</div>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">📊 Dashboard</a>
                    <div class="nav-section">Pacientes</div>
                    <a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.*') ? 'active' : '' }}">👥 Listado de clientes</a>
                    <a href="{{ route('clientes.create') }}">➕ Nuevo cliente</a>
                    <div class="nav-section">Agenda</div>
                    <a href="{{ route('citas.calendario') }}" class="{{ request()->routeIs('citas.calendario') ? 'active' : '' }}">📅 Calendario de citas</a>
                @else
                    <div class="nav-section">Mi área</div>
                    <a href="{{ route('citas.mis-citas') }}" class="{{ request()->routeIs('citas.mis-citas') ? 'active' : '' }}">📅 Mis citas</a>
                    @if(auth()->user()->cliente)
                    <a href="{{ route('clientes.show', auth()->user()->cliente) }}">📋 Mi historial</a>
                    @endif
                @endif
            </nav>
            <div class="sidebar-footer">
                <strong>{{ auth()->user()->name }}</strong>
                <span class="badge {{ auth()->user()->isGestor() ? 'badge-gestor' : 'badge-cliente' }}">
                    {{ auth()->user()->isGestor() ? 'Gestor' : 'Cliente' }}
                </span>
                <form method="POST" action="{{ route('logout') }}" style="margin-top:.5rem">
                    @csrf
                    <button type="submit" style="background:none;border:none;color:rgba(255,255,255,.6);cursor:pointer;font-size:.8rem;padding:0;">
                        🚪 Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <div class="app-main">
            <div class="app-topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menú">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <h2>@yield('page-title', 'Inicio')</h2>
                </div>
                <div class="topbar-actions">@yield('topbar-actions')</div>
            </div>
            <div class="app-content">
                @if(session('success'))
                    <div class="alert alert-success">✅ {{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">❌ {{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Corrige los siguientes errores:</strong>
                        <ul style="margin:.5rem 0 0 1rem">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>

@else
    <nav class="navbar">
        <a href="{{ route('home') }}" class="navbar-brand">
            <svg viewBox="0 0 32 32" fill="none"><path d="M8 6c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v4c0 5.5-3.6 10.2-8.5 11.8V26h2.5a1 1 0 010 2h-7a1 1 0 010-2H13.5v-4.2C8.6 20.2 5 15.5 5 10V6z" fill="white"/></svg>
            Clínica Dental Mula
        </a>
        <div class="navbar-links">
            <a href="{{ route('home') }}">Inicio</a>
            <a href="{{ route('servicios') }}">Servicios</a>
            <a href="{{ route('contacto') }}">Contacto</a>
            <a href="{{ route('login') }}" class="btn-nav">Acceder</a>
        </div>
        <button class="navbar-mobile-toggle" id="navbarToggle" aria-label="Abrir menú">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <div class="navbar-mobile-menu" id="navbarMobileMenu">
            <a href="{{ route('home') }}">🏠 Inicio</a>
            <a href="{{ route('servicios') }}">🦷 Servicios</a>
            <a href="{{ route('contacto') }}">📞 Contacto</a>
            <a href="{{ route('login') }}" class="btn-nav">Acceder →</a>
        </div>
    </nav>

    @yield('content')

    <footer style="background:var(--primary);color:rgba(255,255,255,.6);text-align:center;padding:2rem;margin-top:4rem;font-size:.85rem;">
        <p>© {{ date('Y') }} Clínica Dental Mula · C/ Mayor, 1 · 30170 Mula, Murcia · Tel: 968 66 00 00</p>
        <p style="margin-top:.4rem;font-size:.75rem;">Datos protegidos según RGPD y LOPDGDD</p>
    </footer>
@endauth

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
