@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <section id="nosotros" class="py-4 mb-5">
            <div class="text-center max-w-700 mx-auto mb-5">
                <h2 class="fw-bold text-dark display-6 mb-3">Profesionales que trabajan con nosotros</h2>
            </div>

            {{-- esto se tiene que cargar dinamicamente desde la base de datos obteniendo las imagenes, matricula, especialidad y nombre de los medicos, se devuelven desde el control los profesionales y con un foreach se recorren --}}
            {{-- ejemplo estático para ver cómo se ve --}}
            <div class="container my-5">
                <div class="row g-4">

                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-hover">
                            <div class="text-center pt-4 bg-white">
                                <img src="{{ asset('css/Assets/medico.png') }}"
                                    class="img-fluid rounded-pill object-fit-cover shadow-sm"
                                    style="height: 340px; width: 220px; max-width: 100%; object-position: top;">
                            </div>

                            <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                                <div>
                                    <span
                                        class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold mb-2">
                                        Pediatría
                                    </span>

                                    <h5 class="fw-bold text-dark mb-1">Dra. Irene Torres</h5>
                                    <p class="text-muted small mb-3">M.N. 48.102 · Pediatra General</p>
                                </div>

                                <a href="{{ route('turno') }}"
                                    class="btn btn-outline-success btn-sm w-100 fw-semibold rounded-pill py-2 mt-2">
                                    Solicitar turno
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection
