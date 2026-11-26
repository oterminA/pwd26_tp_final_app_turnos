@extends('layouts.login')
@section('content')
    <div class="card shadow contenedor-login mb-3" style="max-width: 900px; width: 100%;">
        <div class="row g-0 align-items-stretch">

            {{-- ocupa 5 col --}}
            <div class="col-md-5 d-none d-md-block">
                <img src="{{ asset('css/Assets/centro-medico.png') }}"
                    class="img-fluid rounded-start h-100 w-100 object-fit-cover" alt="Imagen de la recepcion de un hospital">
            </div>

            {{-- ocupa 7 col --}}
            <div class="col-md-7 d-flex align-items-center">
                <div class="card-body p-4 w-100">

                    <div class="text-center mb-4">
                        <h1 class="h3 fw-bold">{{ config('app.name') }}</h1>
                        <h5 class="text-muted">Inicio de sesión</h5>
                        <h6 class="text-muted fst-italic">-Paciente-</h6>

                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('paciente.autenticar') }}" method="POST">
                        @csrf
                        <div class="alert alert-danger error d-none" role="alert"></div>

                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" name="dni" id="dni" class="form-control"
                                value="{{ old('dni') }}" placeholder="Ej: 12345678" pattern="[0-9]{7,8}"
                                title="El DNI debe tener entre 7 y 8 números" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" id="password"
                                placeholder="••••••••••" required minlength="8" maxlength="12">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Ingresar</button>

                        <hr class="my-3">

                        <div class="text-center">
                            <span class="text-muted d-block mb-2">¿No tenés cuenta?</span>
                            <a href="{{ route('create.paciente') }}" class="btn btn-outline-secondary w-100">Crear
                                cuenta</a>
                        </div>
                        <br>
                        <div class="text-center">
                            <a href="{{ url('/') }}" class="text-secondary text-decoration-none small mb-0">Volver</a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
