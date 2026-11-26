@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <section id="nosotros" class="py-4 mb-5">
            <div class="text-center max-w-700 mx-auto mb-5">
                <h2 class="fw-bold text-dark display-6 mb-3">Profesionales que trabajan con nosotros</h2>
            </div>

            <div class="container my-5">
                <div class="row g-4">
                    @foreach ($medicos as $medico)
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-hover">
                                <div class="text-center pt-4 bg-white">
                                    <img src="{{ $medico->imagen }}"
                                        class="img-fluid rounded-pill object-fit-cover shadow-sm"
                                        style="height: 340px; width: 220px; max-width: 100%; object-position: top;">
                                </div>

                                <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                                    <div>
                                        @foreach ($medico->especialidades as $especialidad)
                                            <span
                                                class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold mb-2">
                                                <span>{{ $especialidad->nombre }}</span>
                                            </span>
                                        @endforeach

                                        <h5 class="fw-bold text-dark mb-1">{{ $medico->user->nombre_visible }}</h5>
                                        <p class="text-muted small mb-3">{{ $medico->matricula }}</p>
                                        {{-- cambiar eso por la combinacion final --}}
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </section>
    </div>
@endsection
