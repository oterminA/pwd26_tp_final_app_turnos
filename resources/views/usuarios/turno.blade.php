@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-dark">Solicitar Turno</h2>
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data">
                            @csrf
                            {{-- revisar si falta algo más en la validación en html --}}
                            {{-- acá tenemos que traer los medicos que trabajen con la obra social que tenga el paciente en sus datos --}}
                            <div class="mb-3">
                                {{-- capaz que acá usamos tom select acá --}}
                                <label for="tel" class="form-label">Buscar por profesional</label>
                                <select name="medico" id="medico" class="form-select" required>
                                    <option value="" selected disabled> 🔎︎ Ej: Marta Pereira</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="especialidad" class="form-label">Buscar por especialidad</label>
                                <select name="especialidad" id="especialidad" class="form-select" required>
                                    <option value="" selected disabled>🔎︎ Ej: Pedriatría</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                {{-- capaz que acá usamos full calendar --}}
                                <label class="form-label fw-semibold">Fecha y hora</label>
                                <div id='calendar' class="p-3 bg-light rounded-4 border">
                                    <p class="text-muted text-center mb-0 small">Seleccioná un profesional para ver sus
                                        horarios disponibles.</p>
                                </div>
                                <!-- input hidden que se ocupa por js cuando el usuario elige una fecha -->
                                <input type="hidden" name="fecha_hora" id="fecha_hora_input" required>
                            </div>

                            <div class="mb-4">
                                <label for="formFileMultiple" class="form-label fw-semibold">Derivación médica /
                                    orden(opcional)
                                    <input class="form-control" type="file" id="formFileMultiple" name="derivacion[]"
                                        multiple accept="image/jpeg, image/png, image/webp, application/pdf">
                                    <div class="form-text">PDF, JPG, PNG o WEBP. Maximo 5 MB.</div>
                            </div>

                            <div class="text-center d-flex flex-column gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm fw-semibold rounded-pill py-2"
                                    data-bs-toggle="modal" data-bs-target="#modalTurno">
                                    Detalles turno
                                </button>
                                <a href="{{ url('/') }}" class="text-secondary text-decoration-none small mt-2">
                                    Cancelar y volver
                                </a>
                            </div>

                            {{-- codigo del modal que muestra los detalles de turno para posterior confirmacion --}}
                            <div class="modal fade" id="modalTurno" tabindex="-1" aria-labelledby="modalTurnoLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalTurnoLabel">Confirmación de Turno</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body text-start">
                                            <p>Estás a punto de solicitar el turno con los datos seleccionados. Verificá que
                                                todo esté correcto antes de confirmar.</p>
                                            <!-- Acá podés inyectar con JS los datos seleccionados si querés hacerlo más dinámico -->
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
@endsection
