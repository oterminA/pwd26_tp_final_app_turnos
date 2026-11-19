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

                        <form action="{{ route('admin.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="alert alert-danger error d-none" role="alert"></div>

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Nombre y apellido</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', Auth::user()->name) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email', Auth::user()->email) }}" required>
                            </div>


                            <h6 class="text-muted mb-3">Seguridad (dejar en blanco para mantener la actual)</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label fw-semibold">Nueva contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label fw-semibold">Confirmar nueva
                                        contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation">
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
