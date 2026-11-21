@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Mis Turnos</h2>
        <a href="{{ route('create.turno') }}" class="btn btn-primary rounded-pill">+ Solicitar turno</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($turnos->isEmpty())
        <p class="text-muted">No hay turnos registrados todavía.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Especialidad</th>
                        <th>Profesional / Paciente</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($turnos as $turno)
                        <tr>
                            <td>{{ $turno->codigo }}</td>
                            <td>{{ \Carbon\Carbon::parse($turno->fecha)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($turno->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($turno->hora_fin)->format('H:i') }}</td>
                            <td>{{ $turno->especialidad->nombre ?? '-' }}</td>
                            <td>
                                @if (auth()->user()->rol === 'medico')
                                    {{ $turno->paciente->user->name ?? '-' }}
                                @else
                                    {{ $turno->medico->user->name ?? '-' }}
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($turno->estado) }}</span>
                            </td>
                            <td class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('turnos.show', $turno->id) }}" class="btn btn-sm btn-outline-primary">Ver</a>

                                @if (auth()->user()->rol === 'paciente')
                                    <a href="{{ route('turno.comprobante', $turno->id) }}" target="_blank" class="btn btn-sm btn-outline-dark">PDF</a>
                                @endif

                                @if (auth()->user()->rol === 'medico')
                                    <a href="{{ route('turnos.edit', $turno->id) }}" class="btn btn-sm btn-outline-warning">Estado</a>
                                @endif

                                @if ($turno->estado !== 'cancelado')
                                    <form action="{{ route('turnos.delete', $turno->id) }}" method="POST" onsubmit="return confirm('¿Seguro que querés cancelar este turno?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection