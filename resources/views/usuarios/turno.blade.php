@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-dark">Solicitar turno</h2>
                        </div>

                        <form id="form-turno" action="{{ route('store.turno') }}" method="POST">
                            @csrf
                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif
                            <div class="error alert alert-danger d-none" role="alert"></div>
                            <div class="mb-3">
                                {{-- lleva tom select --}}
                                <label for="medico" class="form-label">Buscar por profesional</label>
                                <select name="medico" id="medico" class="form-select" required>
                                </select>
                            </div>

                            <div class="mb-3">
                                {{-- lleva tom select --}}
                                <label for="especialidad" class="form-label">Buscar por especialidad</label>
                                <select name="especialidad" id="especialidad" class="form-select" required>
                                </select>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="fecha_turno" class="form-label fw-semibold">Fecha</label>
                                    <input type="text" id="fecha_turno" class="form-control bg-white"
                                        placeholder="Seleccioná un día..." readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Horarios disponibles</label>
                                    <div id="contenedor-horarios"
                                        class="d-flex flex-wrap gap-2 p-2 bg-light rounded border">
                                        <span class="text-muted small">Elegí un médico y una fecha.</span>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="fecha_hora" id="fecha_hora_input">

                            <div class="text-center d-flex flex-column gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm fw-semibold rounded-pill py-2"
                                    data-bs-toggle="modal" data-bs-target="#modalTurno">
                                    Revisar turno
                                </button>
                                <a href="{{ route('usuarios.paciente.panel') }}" class="text-secondary text-decoration-none small mt-2">
                                    Cancelar y volver
                                </a>
                            </div>

                            {{-- codigo del modal que muestra los detalles de turno para posterior confirmacion --}}
                            <div class="modal fade" id="modalTurno" tabindex="-1" aria-labelledby="modalTurnoLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalTurnoLabel">Confirmación de turno</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Cerrar"></button>
                                        </div>

                                        <div class="modal-body text-start">
                                            <p>Estás a punto de solicitar el turno con los datos seleccionados. Verificá que
                                                todo esté correcto antes de confirmar.</p>
                                            <dl class="row mb-0">
                                                <dt class="col-sm-4">Profesional</dt>
                                                <dd class="col-sm-8" id="resumen-medico"></dd>
                                                <dt class="col-sm-4">Especialidad</dt>
                                                <dd class="col-sm-8" id="resumen-especialidad"></dd>
                                                <dt class="col-sm-4">Fecha</dt>
                                                <dd class="col-sm-8" id="resumen-fecha"></dd>
                                                <dt class="col-sm-4">Horario</dt>
                                                <dd class="col-sm-8" id="resumen-hora"></dd>
                                                <dt class="col-sm-4">Cobertura</dt>
                                                <dd class="col-sm-8" id="resumen-cobertura"></dd>
                                            </dl>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button"
                                                class="btn text-secondary text-decoration-none small mt-2"
                                                data-bs-dismiss="modal">Volver
                                                a revisar</button>
                                            <button type="submit" class="btn btn-outline-success rounded-pill">Confirmar
                                                turno</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
    <script>
    window.especialidadesBD = @json($especialidades);
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('js/confirmacionTurno.js') }}"></script>
@endpush
