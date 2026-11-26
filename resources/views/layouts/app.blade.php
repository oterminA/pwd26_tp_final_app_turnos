<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- cdn de tom select --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.7.0/dist/css/tom-select.css" rel="stylesheet">

    {{-- cdn de flatpickr --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- css de la app --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @stack('styles')

    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
</head>

<body class="bg-light">

    <header class="shadow-sm border-bottom sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark py-3">
            <div class="container">

                {{-- logo de la clinica --}}
                <a class="navbar-brand fw-bold"
                    href="{{ Auth::check() && Auth::user()->isAdmin() ? route('admin.dashboard') : url('/') }}">
                    <span>{{ config('app.name') }}</span>
                </a>

                {{-- boton para menu en celular --}}
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">

                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <ul class="navbar-nav ms-auto gap-lg-3 align-items-lg-center">

                        @auth

                            @if (Auth::user()->isAdmin())
                                {{-- menu del administrador --}}
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.dashboard', 'usuarios.admin.panel') ? 'active' : 'text-white-50' }}"
                                        href="{{ route('admin.dashboard') }}">
                                        Panel
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.medicos.*') ? 'active' : 'text-white-50' }}"
                                        href="{{ route('admin.medicos.index') }}">
                                        Profesionales
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.especialidades.*') ? 'active' : 'text-white-50' }}"
                                        href="{{ route('admin.especialidades.index') }}">
                                        Especialidades
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.edit') ? 'active' : 'text-white-50' }}"
                                        href="{{ route('admin.edit') }}">
                                        Mi cuenta
                                    </a>
                                </li>
                            @else
                                {{-- menu de pacientes y medicos --}}



                                @if (Auth::user()->isPaciente())
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('usuarios.paciente.panel') ? 'active' : 'text-white-50' }}"
                                            href="{{ route('usuarios.paciente.panel') }}">
                                            Panel
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('paciente.edit') }}">
                                            Mi cuenta
                                        </a>
                                    </li>
                                @endif

                                @if (Auth::user()->isMedico())
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('usuarios.medico.panel') ? 'active' : 'text-white-50' }}"
                                            href="{{ route('usuarios.medico.panel') }}">
                                            Panel
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white-50" href="{{ route('medico.edit') }}">
                                            Mi cuenta
                                        </a>
                                    </li>
                                @endif
                            @endif

                            {{-- cerrar sesion --}}
                            <li class="nav-item ms-lg-2">
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">

                                    @csrf

                                    <button type="submit" class="nav-link text-white bg-transparent border-0">
                                        Salir
                                    </button>
                                </form>
                            </li>
                        @else
                            {{-- menu publico --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('/') ? 'active' : 'text-white-50' }}"
                                    href="{{ url('/') }}">
                                    Inicio
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link text-white-50" href="{{ route('login.paciente') }}">
                                    Portal Pacientes
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link text-white-50" href="{{ route('login.medico') }}">
                                    Portal Profesional
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link text-white-50" href="{{ route('login.admin') }}">
                                    Portal Admin
                                </a>
                            </li>

                        @endauth

                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="py-4 mt-5 text-white">
        <div class="container">
            <p class="small mb-0">
                {{ config('app.name') }} - TP Final Integrador - PWD 2026<br>
                <i>Hernández · Villagran · Otermin</i>
            </p>
        </div>
    </footer>

    {{-- bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    {{-- tom select --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.7.0/dist/js/tom-select.complete.min.js"></script>

    {{-- flatpickr --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    {{-- javascript de la app --}}
    <script src="{{ asset('js/validacionesForms.js') }}"></script>
    <script src="{{ asset('js/librerias.js') }}"></script>

    @stack('scripts')
</body>

</html>
