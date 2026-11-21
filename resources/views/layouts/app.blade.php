<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- pongo esto acá para no olvidarnos, en caso de usar ajax y eso --}}

    {{-- cdn de tom select --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.7.0/dist/css/tom-select.css" rel="stylesheet">

    {{-- cdn de flat picker --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


    {{-- css de la app --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
    <title>{{ $title ?? 'Nombre Clinica' }}</title>
</head>

<body class="bg-light">

    <header class="shadow-sm border-bottom sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark py-3">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                    <span>CLÍNICA MÉDICA</span>
                </a>

                {{-- esta parte es para el boton de hamburguesa si se usa en el celu --}}
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                {{-- se manejan las opciones diferentes del menu segun el rol --}}
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto gap-lg-6">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="{{ url('/') }}">Inicio</a>
                        </li>

                        @guest
                            {{-- lo que aparece en la vista publica para gente no autenticada --}}
                            <li class="nav-item">
                                <a class="nav-link text-white-50" href="{{ route('login.paciente') }}">Portal Pacientes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white-50" href="{{ route('login.medico') }}">Portal Profesional</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white-50" href="{{ route('login.admin') }}">Portal Admin</a>
                            </li>
                        @else
                            {{-- esto le aparece a cada usuario autenticado, segun su rol --}}

                            @if (Auth::user()->isPaciente())
                                <li class="nav-item">
                                    <a class="nav-link text-white-50" href="{{ route('paciente.edit') }}">Mi cuenta</a>
                                </li>
                            @endif

                            @if (Auth::user()->isMedico())
                                <li class="nav-item">
                                    <a class="nav-link text-white-50" href="{{ route('medico.edit') }}">Mi cuenta</a>
                                </li>
                            @endif

                            @if (Auth::user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link text-white-50" href="{{ route('admin.edit') }}">Mi cuenta</a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle text-white-50" role="button"
                                        data-bs-toggle="dropdown" href="#">Administración</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Profesionales</a>
                                        </li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.especialidades.index') }}">Especialidades</a></li>
                                    </ul>
                                </li>
                            @endif

                            {{-- y cerrar sesion es para todos --}}
                            <li class="nav-item ms-lg-2">
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="nav-link text-white bg-transparent border-0">⏻</button>
                                </form>
                            </li>
                        @endguest
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
                TP Final Integrador - PWD 2026<br>
                <i>Hernández · Villagran · Otermin</i>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle xra dropdowns y modales -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    {{-- cdn de tom select --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.7.0/dist/js/tom-select.complete.min.js"></script>

    <!-- cdn de flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <!-- el js de la app, o demás scripts-->
    <script src="{{ asset('js/validacionesForms.js') }}"></script>
    <script src="{{ asset('js/librerias.js') }}"></script>
</body>

</html>
