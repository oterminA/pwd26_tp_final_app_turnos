@extends('layouts.login')
@section('content')
    <div class="card shadow contenedor-signin">
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <h1 class="h3 fw-bold">Nombre Clínica</h1>
                <h5 class="text-muted">Crear cuenta</h5>
                <h6 class="text-muted fst-italic">-Profesional-</h6>

            </div>

            <form action="" method="POST">
                @csrf
                {{-- revisar si falta algo más en la validación en html --}}

                <div>
                    <p class="error"></p>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej: Marta"
                            minlength="3" maxlength="30" required>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <label for="apellido" class="form-label">Apellido</label>
                        <input type="text" name="apellido" id="apellido" class="form-control" placeholder="Ej: Juarez"
                            minlength="3" maxlength="30" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="fotoProfesional" class="form-label fw-semibold">Imagen (opcional)
                        <input class="form-control" type="file" id="fotoProfesional" name="fotoProfesional"
                            accept="image/jpeg, image/png, image/webp">
                        <div class="form-text">JPG, PNG o WEBP. Maximo 300 KB.</div>
                </div>

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
                    <input type="text" name="numMat" id="numMat" class="form-control" placeholder="Ej: 12345678"
                        maxlength="8" pattern="[0-9]{7,8}" required>
                </div>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Obra social con la que trabaja</label>
                    <select class="form-select" name="obraSocial">
                        <option selected>Seleccione una opción</option>
                        <option value="particular">Particular</option>
                        <option value="ipross">IPROSS</option>
                        <option value="issn">ISSN</option>
                        <option value="osecac">OSECAC</option>
                        <option value="ospe">OSPE / Construir Salud</option>
                        <option value="osde">OSDE</option>
                        <option value="swiss">Swiss Medical</option>
                        <option value="sancor">Sancor Salud</option>
                        <option value="medife">Medifé</option>
                        <option value="galeno">Galeno</option>
                    </select>
                </div>

                <div class="mb-3">
                    {{-- las especialidades se cargan dinamicamente desde el control --}}
                    {{-- acá se usa tom select tambien --}}
                    <label for="esp-registro" class="form-label">Especialidad</label>
                    <select id="esp-registro" name="esp-registro" multiple autocomplete="off">
                        <option value="">Selecccione la/s especialidad/es</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control" id="email"
                        placeholder="Ej: nombre@gmail.com" required>
                </div>

                <div class="mb-3">
                    <label for="email_confirmation" class="form-label">Repetir correo electrónico</label>
                    <input type="email" name="email_confirmation" class="form-control" id="email_confirmation"
                        required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" id="password"
                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,12}"
                        title="Debe contener al menos un número, una letra mayúscula, una minúscula, y entre 8 y 12 caracteres."
                        placeholder="••••••••••" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Repetir contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" id="password_confirmation"
                        required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">Registrarse</button>

                <hr class="my-3">

                <div class="text-center">
                    <a href="{{ url('/') }}" class="text-secondary text-decoration-none small mb-0">Volver</a>
                </div>
            </form>
        </div>
    </div>
@endsection
