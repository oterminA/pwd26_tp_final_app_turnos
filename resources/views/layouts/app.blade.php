<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- pongo esto acá para no olvidarnos, en caso de usar ajax y eso --}}

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

            {{--se manejan las opciones diferentes del menu segun el rol--}}
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto gap-lg-6">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="{{ url('/') }}">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="{{ route('login') }}">Portal Pacientes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="{{ route('login') }}">Portal Médicos</a>
                        </li>
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

    <!-- el js de la app, o demás scripts-->
    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>
