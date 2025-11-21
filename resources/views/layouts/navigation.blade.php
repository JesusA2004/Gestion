@php
    use Illuminate\Support\Facades\Route;

    $routeName = Route::currentRouteName() ?? '';

    if (str_starts_with($routeName, 'dashboard')) {
        $currentTitle = 'Dashboard';
    } elseif (str_starts_with($routeName, 'profile.')) {
        $currentTitle = 'Perfil';
    } else {
        $currentTitle = 'Panel de control';
    }
@endphp

<div class="sb-layout relative">
    {{-- SIDEBAR --}}
    <aside
        class="sb-sidebar"
        :class="sidebarOpen ? 'sb-sidebar-open animate-sidebar-in' : 'sb-sidebar-closed'"
    >
        {{-- Logo + título --}}
        <div class="sb-sidebar-header">
            <a href="{{ route('dashboard') }}" class="sb-brand group">
                <div class="sb-brand-icon">
                    <x-application-logo class="h-6 w-6 text-slate-900" />
                </div>
                <div class="flex flex-col">
                    <span class="sb-brand-title">
                        {{ config('app.name', 'Gestion') }}
                    </span>
                    <span class="sb-brand-subtitle">
                        Panel de control
                    </span>
                </div>
            </a>
        </div>

        {{-- NAV PRINCIPAL --}}
        <nav class="sb-sidebar-nav">
            <p class="sb-sidebar-section-title">
                NAVEGACIÓN
            </p>

            <ul class="space-y-1.5">
                {{-- Dashboard --}}
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="sb-item group
                              @if(request()->routeIs('dashboard'))
                                  sb-item-active
                              @else
                                  sb-item-default
                              @endif"
                    >
                        <span class="sb-item-icon group-hover:sb-item-icon-hover">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
                            </svg>
                        </span>
                        <span class="flex-1 flex items-center justify-between">
                            <span>Dashboard</span>
                            @if(request()->routeIs('dashboard'))
                                <span class="sb-pill-active">
                                    <span class="sb-pill-dot"></span>
                                    Activo
                                </span>
                            @endif
                        </span>
                    </a>
                </li>

                {{-- Supervisores --}}
                <li>
                    <a href="{{ route('supervisors.index') }}"
                       class="sb-item group
                              @if(request()->routeIs('supervisors.*'))
                                  sb-item-active
                              @else
                                  sb-item-default
                              @endif"
                    >
                        <span class="sb-item-icon group-hover:sb-item-icon-hover">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 20h5V4h-5M2 20h5V10H2m6 10h8V8H8"/>
                            </svg>
                        </span>
                        <span class="flex-1 flex items-center justify-between">
                            <span>Supervisores</span>
                            @if(request()->routeIs('supervisors.*'))
                                <span class="sb-pill-active">
                                    <span class="sb-pill-dot"></span>
                                    Activo
                                </span>
                            @endif
                        </span>
                    </a>
                </li>

                {{-- Patrones --}}
                <li>
                    <a href="{{ route('patrons.index') }}"
                       class="sb-item group
                              @if(request()->routeIs('patrons.*'))
                                  sb-item-active
                              @else
                                  sb-item-default
                              @endif"
                    >
                        <span class="sb-item-icon group-hover:sb-item-icon-hover">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 20h5V4h-5M2 20h5V10H2m6 10h8V8H8"/>
                            </svg>
                        </span>
                        <span class="flex-1 flex items-center justify-between">
                            <span>Patrones</span>
                            @if(request()->routeIs('patrons.*'))
                                <span class="sb-pill-active">
                                    <span class="sb-pill-dot"></span>
                                    Activo
                                </span>
                            @endif
                        </span>
                    </a>
                </li>

                {{-- Sucursales --}}
                <li>
                    <a href="{{ route('sucursals.index') }}"
                       class="sb-item group
                              @if(request()->routeIs('sucursals.*'))
                                  sb-item-active
                              @else
                                  sb-item-default
                              @endif"
                    >
                        <span class="sb-item-icon group-hover:sb-item-icon-hover">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 21h18M4 10h16M6 6h12l1 4H5l1-4zM8 10v11m8-11v11"/>
                            </svg>
                        </span>
                        <span class="flex-1 flex items-center justify-between">
                            <span>Plazas</span>
                            @if(request()->routeIs('sucursals.*'))
                                <span class="sb-pill-active">
                                    <span class="sb-pill-dot"></span>
                                    Activo
                                </span>
                            @endif
                        </span>
                    </a>
                </li>

                {{-- Departamentos --}}
                <li>
                    <a href="{{ route('departamentos.index') }}"
                       class="sb-item group
                              @if(request()->routeIs('departamentos.*'))
                                  sb-item-active
                              @else
                                  sb-item-default
                              @endif"
                    >
                        <span class="sb-item-icon group-hover:sb-item-icon-hover">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 6h16M4 12h10M4 18h7"/>
                            </svg>
                        </span>
                        <span class="flex-1 flex items-center justify-between">
                            <span>Departamentos</span>
                            @if(request()->routeIs('departamentos.*'))
                                <span class="sb-pill-active">
                                    <span class="sb-pill-dot"></span>
                                    Activo
                                </span>
                            @endif
                        </span>
                    </a>
                </li>

                {{-- Empleados --}}
                <li>
                    <a href="{{ route('empleados.index') }}"
                       class="sb-item group
                              @if(request()->routeIs('empleados.*'))
                                  sb-item-active
                              @else
                                  sb-item-default
                              @endif"
                    >
                        <span class="sb-item-icon group-hover:sb-item-icon-hover">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 9a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        <span class="flex-1 flex items-center justify-between">
                            <span>Empleados</span>
                            @if(request()->routeIs('empleados.*'))
                                <span class="sb-pill-active">
                                    <span class="sb-pill-dot"></span>
                                    Activo
                                </span>
                            @endif
                        </span>
                    </a>
                </li>

            </ul>

            {{-- Separador --}}
            <div class="sb-sidebar-divider"></div>

            <p class="sb-sidebar-section-title">
                SISTEMA
            </p>

            <ul class="space-y-1.5">
                {{-- Perfil --}}
                <li>
                    <a href="{{ route('profile.edit') }}"
                       class="sb-item group
                              @if(request()->routeIs('profile.edit'))
                                  sb-item-active
                              @else
                                  sb-item-default
                              @endif"
                    >
                        <span class="sb-item-icon group-hover:sb-item-icon-hover">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        <span class="flex-1 flex items-center justify-between">
                            <span>Perfil</span>
                            @if(request()->routeIs('profile.edit'))
                                <span class="sb-pill-active">
                                    <span class="sb-pill-dot"></span>
                                    Activo
                                </span>
                            @endif
                        </span>
                    </a>
                </li>

                {{-- Logout con SweetAlert2 --}}
                <li>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="button"
                            id="logout-button"
                            class="sb-item sb-item-logout group"
                        >
                            <span class="sb-item-icon sb-item-logout-icon group-hover:sb-item-logout-icon-hover">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3-3m0 0l3 3m-3-3v12"/>
                                </svg>
                            </span>
                            <span class="flex-1 text-left">
                                Cerrar sesión
                            </span>
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    </aside>

    {{-- TOP NAVBAR --}}
    <nav class="sb-topnav sb-nav-animate">
        <div class="sb-topnav-inner">
            {{-- Botón menú SIEMPRE visible + título --}}
            <div class="flex items-center gap-3">
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="sb-burger"
                    :class="sidebarOpen ? 'sb-burger-active' : 'sb-burger-idle'"
                    aria-label="Alternar menú lateral"
                >
                    <svg x-show="!sidebarOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="sidebarOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="flex flex-col">
                    <span class="sb-topnav-title-row">
                        <span class="sb-topnav-chip">
                            {{ strtoupper($currentTitle) }}
                        </span>
                        <span class="hidden sm:inline text-xs text-slate-500">
                            {{ now()->format('d M Y') }}
                        </span>
                    </span>
                    <span class="text-xs text-slate-500">
                        Bienvenido de nuevo, {{ Auth::user()->name }}
                    </span>
                </div>
            </div>

            {{-- Acciones rápidas + user --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}"
                   class="sb-quick-btn hidden sm:inline-flex items-center gap-2"
                >
                    <span class="sb-quick-dot"></span>
                    Vista general
                </a>

                {{-- Menú usuario con x-data local --}}
                <div class="relative" x-data="{ profileOpen: false }">
                    <button
                        @click="profileOpen = !profileOpen"
                        class="sb-user-btn"
                    >
                        <span class="sb-user-avatar">
                            {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden sm:flex flex-col items-start leading-tight">
                            <span class="text-xs font-semibold truncate max-w-[140px]">
                                {{ Auth::user()->name }}
                            </span>
                            <span class="text-[10px] text-slate-400">
                                {{ Auth::user()->email }}
                            </span>
                        </span>
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        x-show="profileOpen"
                        x-cloak
                        @click.outside="profileOpen = false"
                        x-transition:enter="sb-dropdown-enter"
                        x-transition:enter-start="sb-dropdown-enter-start"
                        x-transition:enter-end="sb-dropdown-enter-end"
                        x-transition:leave="sb-dropdown-leave"
                        x-transition:leave-start="sb-dropdown-leave-start"
                        x-transition:leave-end="sb-dropdown-leave-end"
                        class="sb-user-dropdown"
                    >
                        <a href="{{ route('profile.edit') }}"
                           class="sb-user-dropdown-item"
                        >
                            <span class="sb-user-dropdown-icon">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </span>
                            <span>Perfil</span>
                        </a>

                        <div class="sb-user-dropdown-divider"></div>

                        <button
                            type="button"
                            class="sb-user-dropdown-item sb-user-dropdown-logout"
                            onclick="document.getElementById('logout-button').click()"
                        >
                            <span class="sb-user-dropdown-icon-logout">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3-3m0 0l3 3m-3-3v12"/>
                                </svg>
                            </span>
                            <span>Cerrar sesión</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const logoutBtn = document.getElementById('logout-button');
            const logoutForm = document.getElementById('logout-form');

            if (!logoutBtn || !logoutForm) return;

            logoutBtn.addEventListener('click', () => {
                Swal.fire({
                    title: "¿Cerrar sesión?",
                    text: "Se cerrará tu sesión actual.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, salir",
                    cancelButtonText: "Cancelar",
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6"
                }).then((result) => {
                    if (result.isConfirmed) {
                        logoutForm.submit();
                    }
                });
            });
        });
        </script>

</div>

