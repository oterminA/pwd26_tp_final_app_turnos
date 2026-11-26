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
                        <h6 class="text-muted fst-italic">-Profesional-</h6>

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

                    <form action="{{ route('medico.autenticar') }}" method="POST" novalidate>
                        @csrf
                        <div class="alert alert-danger error d-none" role="alert"></div>

                        <div class="row">
                            <div class="mb-3">
                                <label for="tipoMat" class="form-label">Tipo de matricula</label>
                                <select class="form-select" name="tipoMat" id="tipoMat" required>
                                    <option value="">Seleccionar</option>
                                    @foreach (config('administracion.tipos_matricula') as $codigo => $provincia)
                                        <option value="{{ $codigo }}" @selected(old('tipoMat', $medico->tipoMat ?? '') == $codigo)>{{ $provincia }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class=" mb-3">
                                <label for="numMat" class="form-label">Numero de matricula</label>
                                <input class="form-control" type="text" name="numMat" id="numMat" maxlength="10"
                                    pattern="[0-9]{1,10}" inputmode="numeric"
                                    title="Ingresa entre 1 y 10 digitos, sin puntos ni guiones"
                                    value="{{ old('numMat', $medico->numMat ?? '') }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" id="password"
                                placeholder="••••••••••" required minlength="8" maxlength="12">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Ingresar</button>

                        <hr class="my-3">

                        <div class="text-center">
                            <a href="{{ url('/') }}" class="text-secondary text-decoration-none small mb-0">Volver</a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
