@extends('layouts.app')

@section('content')
<div class="container my-5"> 
    <div class="card shadow-sm border-0 rounded-4 p-4">
        <h3 class="fw-bold mb-3">Detalle del Turno #{{ $turno->codigo }}</h3>

        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($turno->fecha)->format('d/m/Y') }}</p>
        <p><strong>Horario:</strong> {{ \Carbon\Carbon::parse($turno->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($turno->hora_fin)->format('H:i') }}</p>
        <p><strong>Especialidad:</strong> {{ $turno->especialidad->nombre ?? '-' }}</p>
        <p><strong>Profesional:</strong> {{ $turno->medico->user->name ?? '-' }}</p>
        <p><strong>Paciente:</strong> {{ $turno->paciente->user->name ?? '-' }}</p>
        <p><strong>Estado:</strong> {{ ucfirst($turno->estado) }}</p>

        <div class="mt-3 d-flex gap-2">
            @if (auth()->user()->rol === 'paciente')
                <a href="{{ route('turno.comprobante', $turno->id) }}" target="_blank" class="btn btn-dark btn-sm">Descargar PDF</a>
            @endif
            <a href="{{ route('turnos.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
        </div>
    </div>
</div>
@endsection