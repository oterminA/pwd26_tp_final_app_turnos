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
                        <h1 class="h3 fw-bold">Nombre Clínica</h1>
                        <h5 class="text-muted">Inicio de sesión</h5>
                        <h6 class="text-muted fst-italic">-Profesional-</h6>

                    </div>

                    <form action="" method="POST">
                        @csrf

                        <div>
                            <p class="error"></p>
                        </div>

                        {{-- tipos de matriculas hardcodeadas --}}
                        <div class="mb-3">
                            <label class="form-label">Tipo de matrícula</label>
                            <select class="form-select" class="tipoMat">
                                <option selected>Seleccione una opción</option>
                                <option value="MN99">Nacional</option>
                                <option value="MP2">Buenos Aires</option>
                                <option value="MP1">Capital Federal</option>
                                <option value="MP3">Catamarca</option>
                                <option value="MP16">Chaco</option>
                                <option value="MP17">Chubut</option>
                                <option value="MP4">Córdoba</option>
                                <option value="MP5">Corrientes</option>
                                <option value="MP6">Entre Ríos</option>
                                <option value="MP18">Formosa</option>
                                <option value="MP7">Jujuy</option>
                                <option value="MP19">La Pampa</option>
                                <option value="MP8">La Rioja</option>
                                <option value="MP9">Mendoza</option>
                                <option value="MP20">Misiones</option>
                                <option value="MP21">Neuquén</option>
                                <option value="MP22">Río Negro</option>
                                <option value="MP10">Salta</option>
                                <option value="MP11">San Juan</option>
                                <option value="MP12">San Luis</option>
                                <option value="MP23">Santa Cruz</option>
                                <option value="MP13">Santa Fe</option>
                                <option value="MP14">Santiago del Estero</option>
                                <option value="MP24">Tierra del Fuego</option>
                                <option value="MP15">Tucumán</option>
                            </select>
                        </div>


                        <div class="mb-3">
                            <label for="numMat" class="form-label">Número de matrícula</label>
                            <input type="text" name="numMat" id="numMat" class="form-control"
                                placeholder="Ej: 12345678" maxlength="8" pattern="[0-9]{7,8}" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" id="password" placeholder="••••••••••" required minlength="8" maxlength="12">
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
