@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <section id="panel-paciente" class="py-4 mb-5">
            <div class="text-center max-w-700 mx-auto mb-5">
                <h2 class="fw-bold text-dark display-6 mb-3">Panel de paciente</h2>
                <h5 class="text-muted">
                    @auth
                        <span>Hola, {{ Auth::user()->nombre_visible }}!</span>
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
                <h4 class="fw-bold text-dark mb-0">Mis turnos</h4>
                <a href="{{ route('create.turno') }}" class="btn text-white"
                    style="background-color: var(--clinic-primary);">
                    Solicitar nuevo turno
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead style="background-color: var(--clinic-light);">
                                <tr>
                                    <th>Código</th> {{-- no sé si el codigo se le tiene que mostrar al paciente --}}
                                    <th>Especialidad</th>
                                    <th>Profesional</th>
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
                                        <td>{{ $turno->especialidad->nombre }}</td>
                                        <td>{{ $turno->medico->user->name }}</td>
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
                                            @can('cancelar', $turno)
                                                @if ($turno->puedeCancelarPorPaciente())
                                                    <form action="{{ route('turnos.delete', $turno->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('¿Seguro que querés cancelar este turno?')">
                                                            Cancelar
                                                        </button>
                                                    </form>
                                                @elseif ($turno->puedeCancelar())
                                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled>
                                                        Cancelar
                                                    </button>
                                                    <small class="d-block text-muted">Se requiere un minimo de 48 horas de anticipacion.</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            @endcan
                                        </td>
                                        <td>
                                            {{-- acá se usa la policy de turno que no deja que otro paciente descargue el comprobante si no es el dueño --}}
                                            @can('descargarComprobante', $turno)
                                                {{-- @if ($turno->estado !== 'cancelado') --}}
                                                <a href="{{ route('turno.comprobante', $turno->id) }}"
                                                    class="btn btn-sm btn-outline-light" target="_blank">
                                                    🖨️
                                                </a>
                                                {{-- @endif --}}
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Todavía no tenés turnos registrados.
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
