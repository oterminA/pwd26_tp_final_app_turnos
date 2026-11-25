@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Mi Disponibilidad Horaria</h2>
        <a href="{{ route('medico.disponibilidades.create') }}" class="btn btn-primary rounded-pill">
            + Agregar franja horaria
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($disponibilidades->isEmpty())
        <div class="card shadow-sm border-0 rounded-4 p-4 text-center text-muted">
            No tenés franjas horarias cargadas todavía.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle bg-white">
                <thead class="table-light">
                    <tr>
                        <th>Especialidad</th>
                        <th>Día</th>
                        <th>Hora inicio</th>
                        <th>Hora fin</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($disponibilidades as $disponibilidad)
                        <tr>
                            <td>{{ $disponibilidad->especialidad?->nombre ?? "General (sin asignar)" }}</td>
                            <td>{{ $disponibilidad->dia_nombre }}</td>
                            <td>{{ $disponibilidad->hora_inicio->format('H:i') }} hs</td>
                            <td>{{ $disponibilidad->hora_fin->format('H:i') }} hs</td>
                            <td class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('medico.disponibilidades.show', $disponibilidad->id) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                <a href="{{ route('medico.disponibilidades.edit', $disponibilidad->id) }}" class="btn btn-sm btn-outline-warning">Editar</a>
                                <form action="{{ route('medico.disponibilidades.delete', $disponibilidad->id) }}" method="POST" onsubmit="return confirm('¿Seguro que querés eliminar esta franja horaria?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
