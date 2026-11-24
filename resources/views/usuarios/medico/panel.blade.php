@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <section id="panel-medico" class="py-4 mb-5">
            <div class="text-center max-w-700 mx-auto mb-5">
                <h2 class="fw-bold text-dark display-6 mb-3">Panel de profesional</h2>
                <h5 class="text-muted">
                    @auth
                        <span>Hola, {{ Auth::user()->name }}!</span>
                    @endauth
                </h5>
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
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold text-dark mb-0">Turnos agendados</h4>

    <div class="d-flex gap-2">
        <a href="{{ route('create.turno') }}" class="btn text-white"
            style="background-color: var(--clinic-primary);">
            Nuevo turno
        </a>
        <a href="{{ route('medico.disponibilidades.index') }}" class="btn text-white"
            style="background-color: var(--clinic-primary);">
            Disponibilidad
        </a>
    </div>
</div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead style="background-color: var(--clinic-light);">
                                <tr>
                                    <th>Código</th>
                                    <th>Paciente</th>
                                    <th>Especialidad</th>
                                    <th>Fecha y hora</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                    <th>Descargar comprobante</th>
                                </tr>
                            </thead>

                            <tbody>
    @forelse($turnos as $turno)
        <tr>
            <td class="fw-bold">{{ $turno->codigo }}</td>
            <td>{{ $turno->paciente->user->name }}</td>
            <td>{{ $turno->especialidad->nombre }}</td>
            <td>
                <strong>{{ $turno->fecha->format('d/m/Y') }}</strong>
                <strong>{{ $turno->hora_inicio->format('H:i') }} hs</strong>
            </td>
            <td>
                <span class="badge estado-{{ $turno->estado }}">
                    {{ $turno->estado }}
                </span>
            </td>
            <td>
                <div class="d-flex flex-wrap gap-1">
                    <a href="{{ route('turnos.edit', $turno->id) }}"
                        class="btn btn-sm btn-outline-secondary">
                        Editar estado
                    </a>

                    <form action="{{ route('turnos.delete', $turno->id) }}" method="POST"
                        class="d-inline"
                        onsubmit="return confirm('¿Seguro que querés cancelar este turno?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            Cancelar
                        </button>
                    </form>
                </div>
            </td>
            <td>
                @can('descargarComprobante', $turno)
                    @if ($turno->estado !== 'cancelado')
                        <a href="{{ route('turno.comprobante', $turno->id) }}"
                            class="btn btn-sm btn-outline-secondary" target="_blank">
                            🖨️
                        </a>
                    @endif
                @endcan
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="text-center text-muted py-4">
                Todavía no tenés turnos asignados.
            </td>
        </tr>
    @endforelse
</tbody>
                        </table>
                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
