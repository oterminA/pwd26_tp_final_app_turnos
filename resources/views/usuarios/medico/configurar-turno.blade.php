@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-dark">¡Bienvenido! Configuración inicial</h2>
                    <p class="text-muted mb-0">Antes de comenzar, elegí el tiempo estándar para cada turno.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('medico.guardar-turno') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="duracion_turno" class="form-label fw-semibold">Duración del turno</label>
                        <select name="duracion_turno" id="duracion_turno" class="form-select" required>
                            <option value="">Seleccioná una opción...</option>
                            @foreach ([15, 20, 30, 45, 60] as $minutos)
                                <option value="{{ $minutos }}" {{ old('duracion_turno') == $minutos ? 'selected' : '' }}>
                                    {{ $minutos }} minutos
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold">
                            Guardar y continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection