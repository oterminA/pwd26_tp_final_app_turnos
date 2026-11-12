@extends('layouts.login')
@section('content')
    <div class="card shadow contenedor-signin">
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <h1 class="h3 fw-bold">Nombre Clínica</h1>
                <h5 class="text-muted">Crear cuenta</h5>
            </div>

            <form action="" method="POST">
                @csrf
                {{-- revisar si falta algo más en la validación en html --}}
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
                    <label for="dni" class="form-label">DNI</label>
                    <input type="number" name="dni" id="dni" class="form-control" placeholder="Ej: 12345678"
                        minlength="7" maxlength="8" pattern="[0-9]{7,8}" required>
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
                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*_=+-]).{8,12}$"
                        placeholder="8 a 12 caracteres" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Repetir contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" id="password_confirmation"
                        required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">Registrarse</button>

                <hr class="my-3">

                <div class="text-center">
                    <span class="text-muted">¿Ya tenés una cuenta?</span>
                    <a href="{{ route('login') }}" class="text-decoration-none fw-bold link-secondary">Iniciar sesión</a>
                </div>
                <br>
                <div class="text-center">
                    <a href="{{ url('/') }}" class="text-secondary text-decoration-none small mb-0">Volver</a>
                </div>
            </form>
        </div>
    </div>
@endsection
