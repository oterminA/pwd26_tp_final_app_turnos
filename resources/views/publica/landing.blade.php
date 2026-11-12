@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <section id="nosotros" class="py-4 mb-5">
            <div class="text-center max-w-700 mx-auto mb-5">
                <h2 class="fw-bold text-dark display-6 mb-3">Sobre nosotros</h2>
                <p class="text-muted fs-5 lh-base">
                    Desde 2026 brindamos servicios médicos con la más alta tecnología y un equipo interdisciplinario
                    enfocado en la atención personalizada de cada paciente.
                </p>
                <a href="{{route('show.equipo')}}" class="btn btn-outline-success">
                    Conocé a nuestro equipo
                </a>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-2 overflow-hidden card-hover">
                        <img src="{{ asset('css/Assets/clinica.png') }}" class="card-img-top object-fit-cover"
                            style="height: 220px;" alt="Consultorios externos">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark mb-2">Consultorios Equipados</h5>
                            <p class="card-text text-muted small">Espacios diseñados para garantizar confort y privacidad
                                durante cada atención.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-2 overflow-hidden card-hover">
                        <img src="{{ asset('css/Assets/clinica-2.png') }}" class="card-img-top object-fit-cover"
                            style="height: 220px;" alt="Diagnóstico por imagen">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark mb-2">Diagnóstico y Tecnología</h5>
                            <p class="card-text text-muted small">Equipamiento de última generación para obtener resultados
                                precisos e inmediatos.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-2 overflow-hidden card-hover">
                        <img src="{{ asset('css/Assets/clinica-3.png') }}" class="card-img-top object-fit-cover"
                            style="height: 220px;" alt="Atención de guardia">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark mb-2">Atención Integral</h5>
                            <p class="card-text text-muted small">Amplia cobertura de especialidades médicas coordinadas
                                para cuidar tu bienestar.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
