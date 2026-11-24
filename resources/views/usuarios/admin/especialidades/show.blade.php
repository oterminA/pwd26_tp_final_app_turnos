@extends('layouts.app')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-gestion.css') }}">@endpush
@section('content')
<div class="container py-4 py-lg-5 admin-gestion">
    <div class="gestion-header mb-4">
        <div><div class="gestion-eyebrow">ADMINISTRACIÓN / ESPECIALIDADES</div><h1>Ficha de especialidad</h1><p>Consultá los datos de la especialidad y su estado.</p></div>
        <a href="{{ route('admin.especialidades.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
    </div>
    <div class="gestion-profile">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
            <div><div class="gestion-symbol mb-3">✚</div><h2 class="gestion-profile-name mb-2">{{ $especialidad->nombre }}</h2><span class="gestion-status {{ $especialidad->trashed() ? 'gestion-off' : 'gestion-on' }}">{{ $especialidad->trashed() ? 'Inactiva' : 'Activa' }}</span></div>
            <a href="{{ route('admin.especialidades.edit',$especialidad->id) }}" class="btn gestion-btn-primary">Editar especialidad</a>
        </div>
        <hr>
        <div class="row g-4 mt-1">
            <div class="col-md-8"><div class="gestion-profile-label">Descripción</div><div class="gestion-profile-value">{{ $especialidad->descripcion ?: 'Sin descripción registrada' }}</div></div>
            <div class="col-md-4"><div class="gestion-profile-label">Duración de cada turno</div><div class="gestion-profile-value">{{ $especialidad->duracion_turno }} minutos</div></div>
        </div>
        <div class="small text-muted mt-4">La baja se gestiona desde Editar y se controla que no haya turnos asociados.</div>
    </div>
</div>
@endsection
