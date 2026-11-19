@extends('layouts.login')
@section('content')
    <div class="card shadow contenedor-signin">
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <h1 class="h3 fw-bold">Nombre Clínica</h1>
                <h5 class="text-muted">Crear cuenta</h5>
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

            <form action="{{ route('store.medico') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="alert alert-danger error d-none" role="alert"></div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej: Marta"
                            value="{{ old('nombre') }}" minlength="3" maxlength="30" required>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <label for="apellido" class="form-label">Apellido</label>
                        <input type="text" name="apellido" id="apellido" class="form-control" placeholder="Ej: Juarez"
                            value="{{ old('apellido') }}" minlength="3" maxlength="30" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="fotoProfesional" class="form-label fw-semibold">Imagen de perfil (opcional)</label>
                    <input class="form-control" type="file" id="fotoProfesional" name="fotoProfesional"
                        accept="image/jpeg, image/png, image/webp">
                    <div class="form-text">JPG, PNG o WEBP. Máximo 300 KB.</div>
                </div>

                <div class="mb-3">
                    {{-- lleva tom select --}}
                    <label for="tipoMat" class="form-label">Tipo de matrícula</label>
                    <select class="form-select" id="tipoMat" name="tipoMat" required>
                        <option value="" disabled @selected(!old('tipoMat'))>Seleccione una opción</option>
                        <option value="MN99" @selected(old('tipoMat') == 'MN99')>Nacional</option>
                        <option value="MP2" @selected(old('tipoMat') == 'MP2')>Buenos Aires</option>
                        <option value="MP1" @selected(old('tipoMat') == 'MP1')>Capital Federal</option>
                        <option value="MP3" @selected(old('tipoMat') == 'MP3')>Catamarca</option>
                        <option value="MP16" @selected(old('tipoMat') == 'MP16')>Chaco</option>
                        <option value="MP17" @selected(old('tipoMat') == 'MP17')>Chubut</option>
                        <option value="MP4" @selected(old('tipoMat') == 'MP4')>Córdoba</option>
                        <option value="MP5" @selected(old('tipoMat') == 'MP5')>Corrientes</option>
                        <option value="MP6" @selected(old('tipoMat') == 'MP6')>Entre Ríos</option>
                        <option value="MP18" @selected(old('tipoMat') == 'MP18')>Formosa</option>
                        <option value="MP7" @selected(old('tipoMat') == 'MP7')>Jujuy</option>
                        <option value="MP19" @selected(old('tipoMat') == 'MP19')>La Pampa</option>
                        <option value="MP8" @selected(old('tipoMat') == 'MP8')>La Rioja</option>
                        <option value="MP9" @selected(old('tipoMat') == 'MP9')>Mendoza</option>
                        <option value="MP20" @selected(old('tipoMat') == 'MP20')>Misiones</option>
                        <option value="MP21" @selected(old('tipoMat') == 'MP21')>Neuquén</option>
                        <option value="MP22" @selected(old('tipoMat') == 'MP22')>Río Negro</option>
                        <option value="MP10" @selected(old('tipoMat') == 'MP10')>Salta</option>
                        <option value="MP11" @selected(old('tipoMat') == 'MP11')>San Juan</option>
                        <option value="MP12" @selected(old('tipoMat') == 'MP12')>San Luis</option>
                        <option value="MP23" @selected(old('tipoMat') == 'MP23')>Santa Cruz</option>
                        <option value="MP13" @selected(old('tipoMat') == 'MP13')>Santa Fe</option>
                        <option value="MP14" @selected(old('tipoMat') == 'MP14')>Santiago del Estero</option>
                        <option value="MP24" @selected(old('tipoMat') == 'MP24')>Tierra del Fuego</option>
                        <option value="MP15" @selected(old('tipoMat') == 'MP15')>Tucumán</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="numMat" class="form-label">Número de matrícula</label>
                    <input type="text" name="numMat" id="numMat" class="form-control" placeholder="Ej: 12345678"
                        value="{{ old('numMat') }}" maxlength="8" pattern="[0-9]{7,8}" required>
                </div>

                <div class="mb-3">
                    {{-- lleva tom select --}}
                    <label for="obraSocial" class="form-label">Obra social con la que trabaja</label>
                    <select class="form-select" id="obraSocial" name="obraSocial" required>
                        <option value="" disabled @selected(!old('obraSocial'))>Seleccione una opción</option>
                        <option value="particular" @selected(old('obraSocial') == 'particular')>Particular</option>
                        <option value="ipross" @selected(old('obraSocial') == 'ipross')>IPROSS</option>
                        <option value="issn" @selected(old('obraSocial') == 'issn')>ISSN</option>
                        <option value="osecac" @selected(old('obraSocial') == 'osecac')>OSECAC</option>
                        <option value="ospe" @selected(old('obraSocial') == 'ospe')>OSPE / Construir Salud</option>
                        <option value="osde" @selected(old('obraSocial') == 'osde')>OSDE</option>
                        <option value="swiss" @selected(old('obraSocial') == 'swiss')>Swiss Medical</option>
                        <option value="sancor" @selected(old('obraSocial') == 'sancor')>Sancor Salud</option>
                        <option value="medife" @selected(old('obraSocial') == 'medife')>Medifé</option>
                        <option value="galeno" @selected(old('obraSocial') == 'galeno')>Galeno</option>
                    </select>
                </div>

                <div class="mb-3">
                    {{-- lleva tom select --}}
                    <label for="especialidad" class="form-label">Especialidad</label>
                    <select id="especialidad" name="especialidad[]" multiple autocomplete="off" required>
                        <option value="">Seleccione la/s especialidad/es</option>
                        @foreach ($especialidades ?? [] as $esp)
                            <option value="{{ $esp->id }}" @selected(in_array($esp->id, old('especialidad', [])))>
                                {{ $esp->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control" id="email"
                        value="{{ old('email') }}" placeholder="Ej: nombre@gmail.com" required>
                </div>

                <div class="mb-3">
                    <label for="email_confirmation" class="form-label">Repetir correo electrónico</label>
                    <input type="email" name="email_confirmation" class="form-control" id="email_confirmation"
                        value="{{ old('email_confirmation') }}" required>
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
                        placeholder="••••••••••" required>
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
