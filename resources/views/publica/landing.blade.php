@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
@endpush

@section('content')
    <div class="landing">
        <section class="landing-hero py-5">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-5">
                        <h1 class="display-5 fw-bold mb-3">Bienvenidos a {{ config('app.name') }}</h1>
                        <p class="lead text-muted mb-4">Reservá tu turno médico online.</p>
                        <div class="d-flex flex-wrap gap-3">
                            @guest
                                <a href="{{ route('login.paciente') }}" class="btn landing-btn px-4 py-3">Solicitar turno</a>
                            @else
                                @if (Auth::user()->isPaciente())
                                    <a href="{{ route('create.turno') }}" class="btn landing-btn px-4 py-3">Solicitar turno</a>
                                @endif
                            @endguest
                            <a href="{{ route('show.equipo') }}" class="btn landing-outline px-4 py-3">Conocé al equipo</a>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="landing-photo">
                            <img src="{{ asset('css/Assets/equipo-turnapp.jpg') }}" alt="Equipo de atención médica" class="img-fluid" width="1672" height="941">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container py-5" id="como-funciona">
            <h2 class="text-center fw-bold mb-4">Solicitá tu turno</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="landing-step h-100 text-center p-4">
                        <span class="landing-number">1</span>
                        <h3 class="h5 mt-3 mb-0">Ingresá a tu cuenta</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="landing-step h-100 text-center p-4">
                        <span class="landing-number">2</span>
                        <h3 class="h5 mt-3 mb-0">Elegí un profesional</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="landing-step h-100 text-center p-4">
                        <span class="landing-number">3</span>
                        <h3 class="h5 mt-3 mb-0">Confirmá tu turno</h3>
                    </div>
                </div>
            </div>
        </section>

        <section id="nosotros" class="landing-soft py-5">
            <div class="container">
                <h2 class="text-center fw-bold mb-4">Sobre nosotros</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card landing-card h-100 border-0 overflow-hidden">
                            <img src="{{ asset('css/Assets/clinica.png') }}" class="card-img-top" alt="Consultorio médico" loading="lazy">
                            <div class="card-body text-center p-3">
                                <h3 class="h5 mb-0">Consultorios equipados</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card landing-card h-100 border-0 overflow-hidden">
                            <img src="{{ asset('css/Assets/clinica-2.png') }}" class="card-img-top" alt="Espacio de atención" loading="lazy">
                            <div class="card-body text-center p-3">
                                <h3 class="h5 mb-0">Atención médica</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card landing-card h-100 border-0 overflow-hidden">
                            <img src="{{ asset('css/Assets/clinica-3.png') }}" class="card-img-top" alt="Profesionales de la salud" loading="lazy">
                            <div class="card-body text-center p-3">
                                <h3 class="h5 mb-0">Equipo profesional</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
