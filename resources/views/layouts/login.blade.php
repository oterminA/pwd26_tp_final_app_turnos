<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- pongo esto acá para no olvidarnos, en caso de usar ajax y eso --}}

    {{-- css de la app --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    @stack('styles')
    <title>Inicio de sesión</title>
</head>

<body>

    {{-- acá va el contenido especifico de cada vista relacionada al inicio de sesion/registro --}}
    @yield('content')

</body>

</html>
