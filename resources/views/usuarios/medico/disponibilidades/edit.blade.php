@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5">
                <h3 class="fw-bold mb-4">Editar Disponibilidad</h3>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('medico.disponibilidades.update', $disponibilidad->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="dia_semana" class="form-label fw-semibold">Día de la semana</label>
                        <select name="dia_semana" id="dia_semana" class="form-select" required>
                            @foreach ($dias as $dia)
                                <option value="{{ $dia }}" {{ old('dia_semana', $disponibilidad->dia_semana) === $dia ? 'selected' : '' }}>
                                    {{ ucfirst($dia) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="hora_inicio" class="form-label fw-semibold">Hora inicio</label>
                            <input type="time" name="hora_inicio" id="hora_inicio" class="form-control"
                                value="{{ old('hora_inicio', \Carbon\Carbon::parse($disponibilidad->hora_inicio)->format('H:i')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="hora_fin" class="form-label fw-semibold">Hora fin</label>
                            <input type="time" name="hora_fin" id="hora_fin" class="form-control"
                                value="{{ old('hora_fin', \Carbon\Carbon::parse($disponibilidad->hora_fin)->format('H:i')) }}" required>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success rounded-pill px-4">Guardar cambios</button>
                        <a href="{{ route('medico.disponibilidades.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection