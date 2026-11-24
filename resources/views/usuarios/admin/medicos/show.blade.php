@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-gestion.css') }}">
@endpush
@section('content')
    @php
        $nombre = $medico->user->name ?? 'Profesional';
        $iniciales = collect(preg_split('/\s+/', trim($nombre)))
            ->filter()
            ->take(2)
            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
            ->join('');
        $foto = $medico->imagen
            ? (str_starts_with($medico->imagen, 'medicos/')
                ? asset('storage/' . $medico->imagen)
                : asset($medico->imagen))
            : null;
        $tipo = config('administracion.tipos_matricula')[$medico->tipoMat] ?? ($medico->tipoMat ?? 'Sin especificar');
        $obras = collect($medico->obrasSocialesAsignadas())
            ->map(fn($codigo) => config('administracion.obras_sociales')[$codigo] ?? $codigo)
            ->join(', ');
    @endphp
    <div class="container py-4 py-lg-5 admin-gestion">
        <div class="gestion-header mb-4">
            <div>
                <div class="gestion-eyebrow">ADMINISTRACIÓN / FICHA PROFESIONAL</div>
                <h1>Detalle del profesional</h1>
                <p>Información de registro y actividad asistencial.</p>
            </div>
            <a href="{{ route('admin.medicos.index') }}" class="btn btn-outline-secondary">Volver al equipo</a>
        </div>
        <div class="gestion-profile mb-4">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                @if ($foto)
                    <img src="{{ $foto }}" alt="Foto de {{ $nombre }}"
                        class="gestion-avatar gestion-avatar-lg">
                @else<div class="gestion-avatar gestion-avatar-lg gestion-initials">{{ $iniciales ?: 'MD' }}</div>
                @endif
                <div class="flex-grow-1">
                    <div class="gestion-profile-name">{{ $nombre }}</div>
                    <div class="mt-2"><span
                            class="gestion-status {{ $medico->trashed() ? 'gestion-off' : 'gestion-on' }}">{{ $medico->trashed() ? 'Inactivo' : 'Activo' }}</span>
                    </div>
                    <div class="mt-3">
                        @forelse($medico->especialidades as $especialidad)
                        <span class="gestion-tag">{{ $especialidad->nombre }}</span>@empty<span class="text-muted">Sin
                                especialidades</span>
                        @endforelse
                    </div>
                </div>
                <a class="btn gestion-btn-primary" href="{{ route('admin.medicos.edit', $medico->id) }}">Editar
                    profesional</a>
            </div>
            <hr class="my-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="gestion-profile-label">Correo electrónico</div>
                    <div class="gestion-profile-value">{{ $medico->user->email ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="gestion-profile-label">Matrícula</div>
                    <div class="gestion-profile-value">{{ $tipo }} · {{ $medico->numMat ?? $medico->matricula }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="gestion-profile-label">Obras sociales aceptadas</div>
                    <div class="gestion-profile-value">{{ $obras ?: 'Sin especificar' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="gestion-profile-label">Duración estándar por turno</div>
                    <div class="gestion-profile-value">
                        {{ $medico->duracion_turno ? $medico->duracion_turno . ' minutos' : 'Sin configurar' }}</div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="gestion-stat"><span>Turnos del
                        mes</span><strong>{{ $turnosMes }}</strong><small>{{ now()->translatedFormat('F Y') }}</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="gestion-stat"><span>Turnos finalizados</span><strong>{{ $turnosFinalizados }}</strong><small>En
                        el mes actual</small></div>
            </div>
        </div>
        <div class="gestion-profile">
            <h2 class="gestion-form-title">Próximos turnos</h2>
            @forelse($proximosTurnos as $turno)
                <div class="d-flex justify-content-between align-items-center gap-3 py-3 border-bottom flex-wrap">
                    <div><strong>{{ $turno->fecha?->format('d/m/Y') }}</strong> ·
                        {{ substr((string) $turno->hora_inicio, 0, 5) }}<div class="text-muted small">
                            {{ $turno->especialidad->nombre ?? 'Sin especialidad' }}</div>
                    </div>
                    <span class="gestion-tag">{{ ucfirst($turno->estado) }}</span>
                </div>
            @empty
                <p class="text-muted mb-0">No hay turnos futuros pendientes para este profesional.</p>
            @endforelse
            <div class="small text-muted mt-3">Información de consulta. Las reservas y cancelaciones se gestionan desde el
                módulo de turnos.</div>
        </div>
    </div>
@endsection
