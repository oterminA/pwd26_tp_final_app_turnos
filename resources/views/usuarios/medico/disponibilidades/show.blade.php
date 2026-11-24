@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5">
                    <h3 class="fw-bold mb-4">Detalle de Disponibilidad</h3>

                    <p><strong>Día:</strong> <{{ $disponibilidad->dia_nombre }}</p>
                    <p><strong>Hora inicio:</strong> {{ $disponibilidad->hora_inicio->format('H:i') }}
                    <p><strong>Hora fin:</strong> {{ $disponibilidad->hora_fin->format('H:i') }} hs</p>
                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('medico.disponibilidades.edit', $disponibilidad->id) }}"
                            class="btn btn-outline-warning btn-sm rounded-pill px-3">Editar</a>
                        <a href="{{ route('medico.disponibilidades.index') }}"
                            class="btn btn-outline-secondary btn-sm rounded-pill px-3">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
