
@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color: var(--clinic-dark);">
            Administracion de especialidades
        </h2>

        <a href="{{ route('admin.especialidades.create') }}"
           class="btn text-white"
           style="background-color: var(--clinic-primary);">
            Agregar especialidad
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripcion</th>
                            <th>Duracion</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($especialidades as $especialidad)
                            <tr>
                                <td>{{ $especialidad->nombre }}</td>

                                <td>
                                    {{ $especialidad->descripcion ?? '-' }}
                                </td>

                                <td>
                                    {{ $especialidad->duracion_turno }} minutos
                                </td>

                                <td>
                                    @if($especialidad->trashed())
                                        <span class="badge bg-secondary">
                                            Inactiva
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            Activa
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('admin.especialidades.show', $especialidad->id) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        Ver
                                    </a>

                                    <a href="{{ route('admin.especialidades.edit', $especialidad->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Editar
                                    </a>

                                    @if(!$especialidad->trashed())
                                        <form action="{{ route('admin.especialidades.delete', $especialidad->id) }}"
                                              method="POST"
                                              class="d-inline">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('¿Queres desactivar esta especialidad?')">
                                                Desactivar
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="text-center text-muted py-4">
                                    No hay especialidades registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

@endsection
