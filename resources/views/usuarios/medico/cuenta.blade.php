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

                            <div class="mb-3">
                                <label for="fotoProfesional" class="form-label fw-semibold">Imagen de perfil
                                    (opcional)</label>

                                <div class="d-flex align-items-center gap-3 mb-2">
                                    @if ($medico->imagen)
                                        <img src="{{ $medico->urlImagen($medico->imagen) }}"
                                            alt="Foto de {{ $medico->nombre ?? 'profesional' }}"
                                            class="rounded-circle border object-fit-cover shadow-sm"
                                            style="width: 96px; height: 96px;">
                                    @else
                                        <div class="rounded-circle border bg-light d-flex align-items-center justify-content-center text-muted"
                                            style="width: 96px; height: 96px;">
                                            <small>Sin foto</small>
                                        </div>
                                    @endif

                                    <div class="flex-grow-1">
                                        <input class="form-control" type="file" id="fotoProfesional" name="imagen"
                                            accept="image/jpeg, image/png, image/webp">
                                        <small class="text-muted d-block mt-1">
                                            JPG, PNG o WebP. Máximo 2 MB. Si no elegís otra foto, se mantiene la actual.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control"
                                        value="{{ old('nombre', $medico->nombre) }}" minlength="3" maxlength="30" required>
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
                                @php
                                    $obrasForm = array_map(
                                        'strval',
                                        (array) old('obras_sociales', $obrasSeleccionadas ?? []),
                                    );
                                @endphp

                                <label for="obras_sociales" class="form-label">Obras sociales que acepta</label>
                                <select class="form-select" name="obras_sociales[]" id="obras_sociales" multiple>
                                    @foreach (config('administracion.obras_sociales', []) as $codigo => $nombreObra)
                                        <option value="{{ $codigo }}" @selected(in_array((string) $codigo, $obrasForm, true))>
                                            {{ $nombreObra }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Seleccioná una o varias coberturas.</small>
                            </div>

                            @php
                                $especialidadesForm = array_map(
                                    'strval',
                                    (array) old('especialidades', $seleccionadas ?? []),
                                );
                            @endphp

                            <div class="mb-3">
                                <label for="especialidades" class="form-label">Especialidades (podés seleccionar
                                    varias)</label>
                                <select class="form-select" name="especialidades[]" id="especialidades" multiple>
                                    @foreach ($especialidades ?? [] as $especialidad)
                                        <option value="{{ $especialidad->id }}" @selected(in_array((string) $especialidad->id, $especialidadesForm, true))>
                                            {{ $especialidad->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <h6 class="text-muted mb-3">Seguridad (dejar en blanco para mantener la actual)</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label fw-semibold">Nueva contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        minlength="8">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label fw-semibold">Confirmar nueva
                                        contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" minlength="8">
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
