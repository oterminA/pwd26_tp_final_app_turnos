@extends('layouts.app')

@section('content')
    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0" style="color: var(--clinic-dark);">
                Mi cuenta
            </h2>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold" style="color: var(--clinic-primary);">Datos personales</h5>
                    </div>
                    <div class="card-body p-4">

                        <form action="{{ route('medico.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="alert alert-danger error d-none" role="alert"></div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control"
                                         value="{{ old('nombre', $medico->nombre) }}" minlength="3" maxlength="30"
                                        required>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" name="apellido" id="apellido" class="form-control"
                                         value="{{ old('apellido', $medico->apellido) }}" minlength="3" maxlength="30"
                                        required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email', $user->email) }}" maxlength="255" required>
                            </div>

                            <div class="mb-3">
                                <label for="imagen" class="form-label fw-semibold">Imagen de perfil (opcional)</label>
                                <input class="form-control" type="file" id="fotoProfesional" name="imagen"
                                    accept="image/jpeg, image/png, image/webp">
                                <div class="form-text">JPG, PNG o WEBP. Máximo 300 KB.</div>
                            </div>

                            <div class="mb-3">
                                {{-- lleva tom select --}}
                                <label for="tipoMat" class="form-label fw-semibold">Tipo de matrícula</label>
                                <select class="form-select" id="tipoMat" name="tipoMat" required>
                                    <option value="" disabled>Seleccione una opción</option>
                                    <option value="MN99" @selected(old('tipoMat', $medico->tipo_matricula) == 'MN99')>Nacional</option>
                                    <option value="MP2" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP2')>Buenos Aires</option>
                                    <option value="MP1" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP1')>Capital Federal</option>
                                    <option value="MP3" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP3')>Catamarca</option>
                                    <option value="MP16" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP16')>Chaco</option>
                                    <option value="MP17" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP17')>Chubut</option>
                                    <option value="MP4" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP4')>Córdoba</option>
                                    <option value="MP5" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP5')>Corrientes</option>
                                    <option value="MP6" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP6')>Entre Ríos</option>
                                    <option value="MP18" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP18')>Formosa</option>
                                    <option value="MP7" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP7')>Jujuy</option>
                                    <option value="MP19" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP19')>La Pampa</option>
                                    <option value="MP8" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP8')>La Rioja</option>
                                    <option value="MP9" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP9')>Mendoza</option>
                                    <option value="MP20" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP20')>Misiones</option>
                                    <option value="MP21" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP21')>Neuquén</option>
                                    <option value="MP22" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP22')>Río Negro</option>
                                    <option value="MP10" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP10')>Salta</option>
                                    <option value="MP11" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP11')>San Juan</option>
                                    <option value="MP12" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP12')>San Luis</option>
                                    <option value="MP23" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP23')>Santa Cruz</option>
                                    <option value="MP13" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP13')>Santa Fe</option>
                                    <option value="MP14" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP14')>Santiago del Estero</option>
                                    <option value="MP24" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP24')>Tierra del Fuego</option>
                                    <option value="MP15" @selected(old('tipoMat', $medico->tipo_matricula) == 'MP15')>Tucumán</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="matricula" class="form-label fw-semibold">Matrícula</label>
                                    <input type="text" class="form-control" id="matricula" name="matricula"
                                        value="{{ old('matricula', $medico->matricula) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    {{-- lleva tom select --}}
                                    <label for="obra_social" class="form-label fw-semibold">Obra social con la que
                                        trabaja</label>
                                    <select class="form-select" id="obra_social" name="obra_social" required>
                                        <option value="" disabled>Seleccione una opción</option>
                                        <option value="particular" @selected(old('obra_social', $medico->obra_social) == 'particular')>Particular</option>
                                        <option value="ipross" @selected(old('obra_social', $medico->obra_social) == 'ipross')>IPROSS</option>
                                        <option value="issn" @selected(old('obra_social', $medico->obra_social) == 'issn')>ISSN</option>
                                        <option value="osecac" @selected(old('obra_social', $medico->obra_social) == 'osecac')>OSECAC</option>
                                        <option value="ospe" @selected(old('obra_social', $medico->obra_social) == 'ospe')>OSPE / Construir Salud</option>
                                        <option value="osde" @selected(old('obra_social', $medico->obra_social) == 'osde')>OSDE</option>
                                        <option value="swiss" @selected(old('obra_social', $medico->obra_social) == 'swiss')>Swiss Medical</option>
                                        <option value="sancor" @selected(old('obra_social', $medico->obra_social) == 'sancor')>Sancor Salud</option>
                                        <option value="medife" @selected(old('obra_social', $medico->obra_social) == 'medife')>Medifé</option>
                                        <option value="galeno" @selected(old('obra_social', $medico->obra_social) == 'galeno')>Galeno</option>
                                    </select>
                                </div>
                            </div>

                            <h6 class="text-muted mb-3">Seguridad (dejar en blanco para mantener la actual)</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label fw-semibold">Nueva contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        minlength="8" maxlength="12">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label fw-semibold">Confirmar nueva
                                        contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" minlength="8" maxlength="12">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn text-white px-4"
                                    style="background-color: var(--clinic-primary);">
                                    Guardar cambios
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
