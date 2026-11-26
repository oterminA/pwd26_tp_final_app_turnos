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

                        <form action="{{ route('paciente.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="alert alert-danger error d-none" role="alert"></div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control"
                                         value="{{ old('nombre', $paciente->nombre) }}" minlength="3" maxlength="30"
                                        required>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" name="apellido" id="apellido" class="form-control"
                                         value="{{ old('apellido', $paciente->apellido) }}" minlength="3" maxlength="30"
                                        required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email', $user->email) }}" maxlength="255" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dni" class="form-label fw-semibold">DNI</label>
                                    <input type="text" class="form-control" id="dni" name="dni"
                                        value="{{ old('dni', $paciente->dni) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                        value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento) }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono"
                                        value="{{ old('telefono', $paciente->telefono) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    {{-- lleva tom select --}}
                                    <label for="obra_social" class="form-label fw-semibold">Obra social</label>
                                    <select class="form-select" id="obra_social" name="obra_social" required>
                                        <option value="" disabled>Seleccione una opción</option>
                                        @foreach (config('administracion.obras_sociales') as $codigo => $nombre)
                            <option value="{{ $codigo }}" @selected(old('obra_social', $paciente->obra_social) == $codigo)>{{ $nombre }}</option>
                        @endforeach
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
