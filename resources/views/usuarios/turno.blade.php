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

                            {{-- la idea podria ser que a medida que se va clickeando una opcion vayan apareciendo los otros selects con la informacion ya cargada dinamicamente(o sea que se muestren los correspondientes a esa eleccion) --}}
                            <div class="mb-3">
                                {{-- capaz que acá usamos tom select acá --}}
                                <label for="especialidad" class="form-label fw-semibold">Especialidad</label>
                                <select name="especialidad_id" id="especialidad" class="form-select" required>
                                    <option value="" selected disabled>Seleccioná una especialidad...</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                {{-- capaz que acá usamos tom select acá y que cuando se seleccione esto se muestren los medicos que tienen esa especialidad --}}
                                <label for="obraSocial" class="form-label fw-semibold">Obra Social / Cobertura</label>
                                <select name="obra_social_id" id="obraSocial" class="form-select" required>
                                    <option value="" selected disabled>Seleccioná tu obra social...</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="medico" class="form-label fw-semibold">Profesional</label>
                                <select name="medico_id" id="medico" class="form-select" required>
                                    <option value="" selected disabled>Primero elegí una especialidad...</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                {{-- capaz que acá usamos full calendar --}}
                                <label class="form-label fw-semibold">Fecha y Hora</label>
                                <div id='calendar' class="p-3 bg-light rounded-4 border">
                                    <p class="text-muted text-center mb-0 small">Seleccioná un profesional para ver sus
                                        horarios disponibles.</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="formFileMultiple" class="form-label fw-semibold">Derivación médica (opcional)
                                    <input class="form-control" type="file" id="formFileMultiple" name="derivacion[]"
                                        multiple accept="image/jpeg, image/png, image/webp, application/pdf">
                                    <div class="form-text">PDF, JPG, PNG o WEBP. Maximo 500 KB.</div>
                            </div>

                            <div class="text-center d-flex flex-column gap-2">
                                {{-- al apretar confirmar se podría mostrar un cartel de "estas a punto de confirmar el turno blablabla" --}}
                                <button type="submit"
                                    class="btn btn-outline-success btn-sm fw-semibold rounded-pill py-2 ">
                                    Confirmar Turno
                                </button>
                                <a href="{{ url('/') }}" class="text-secondary text-decoration-none small mt-2">
                                    Cancelar y volver
                                </a>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
