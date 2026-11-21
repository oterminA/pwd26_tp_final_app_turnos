@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm border-0 rounded-4 p-4">
        <h3 class="fw-bold mb-3">Cambiar Estado del Turno #{{ $turno->codigo }}</h3>

        <form action="{{ route('turnos.update', $turno->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-select" required>
                    @foreach (['finalizado', 'ausente', 'cancelado', 'pendiente'] as $estado)
                        <option value="{{ $estado }}" {{ $turno->estado === $estado ? 'selected' : '' }}>
                            {{ ucfirst($estado) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success btn-sm">Guardar cambios</button>
            <a href="{{ route('medico.panel') }}" class="btn btn-outline-secondary btn-sm">Cancelar</a>
        </form>
    </div>
</div>
@endsection
