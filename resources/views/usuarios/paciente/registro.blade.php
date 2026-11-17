@extends('layouts.login')
@section('content')
    <div class="card shadow contenedor-signin">
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <h1 class="h3 fw-bold">Nombre Clínica</h1>
                <h5 class="text-muted">Crear cuenta</h5>
                <h6 class="text-muted fst-italic">-Paciente-</h6>

            </div>

            <form action="{{route('store.paciente')}}" method="POST">
                @csrf
                {{-- revisar si falta algo más en la validación en html --}}

                <div class="alert alert-danger error d-none" role="alert"></div>

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

                <div class="mb-3">
                    <label for="fechaNac" class="form-label">Fecha de nacimiento</label>
                    <input type="date" name="fechaNac" id="fechaNac" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" name="dni" id="dni" class="form-control" placeholder="Ej: 12345678"
                        pattern="[0-9]{7,8}" title="El DNI debe tener entre 7 y 8 números" required>
                </div>

                {{-- obras sociales hardcodeadas --}}
                <div class="mb-3">
                    <label for="nombre" class="form-label">Obra social</label>
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
                    <label for="tel" class="form-label">Teléfono</label>
                    <input type="number" name="tel" id="tel" class="form-control" placeholder="Ej: 2991234567"
                        pattern="[0-9]{7,8}" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control" id="email"
                        placeholder="Ej: nombre@gmail.com" required>
                </div>

                <div class="mb-3">
                    <label for="email_confirmation" class="form-label">Repetir correo electrónico</label>
                    <input type="email" name="email_confirmation" class="form-control" id="email_confirmation" required>
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
                    <span class="text-muted">¿Ya tenés una cuenta?</span>
                    <a href="{{ route('login.paciente') }}" class="text-decoration-none fw-bold link-secondary">Iniciar
                        sesión</a>
                </div>
                <br>
                <div class="text-center">
                    <a href="{{ url('/') }}" class="text-secondary text-decoration-none small mb-0">Volver</a>
                </div>
            </form>
        </div>
    </div>
@endsection
