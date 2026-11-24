@extends('layouts.app')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-gestion.css') }}">@endpush
@section('content')
@php
    $activas = $especialidades->filter(fn($e) => !$e->trashed())->count();
@endphp
<div class="container py-4 py-lg-5 admin-gestion">
    <div class="gestion-header mb-4">
        <div><div class="gestion-eyebrow">ADMINISTRACIÓN / ESPECIALIDADES</div><h1>Especialidades</h1><p>Organizá las áreas de atención y consultá los médicos asociados.</p></div>
        <a href="{{ route('admin.especialidades.create') }}" class="btn gestion-btn-primary">+ Nueva especialidad</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="row g-3 mb-4">
        <div class="col-4"><div class="gestion-stat"><span>Total</span><strong>{{ $especialidades->count() }}</strong><small>Registradas</small></div></div>
        <div class="col-4"><div class="gestion-stat"><span>Activas</span><strong>{{ $activas }}</strong><small>Disponibles</small></div></div>
        <div class="col-4"><div class="gestion-stat"><span>Inactivas</span><strong>{{ $especialidades->count() - $activas }}</strong><small>Desactivadas</small></div></div>
    </div>
    <div class="gestion-toolbar mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-8"><label class="form-label" for="buscarEspecialidad">Buscar especialidad</label><input type="search" class="form-control" id="buscarEspecialidad" placeholder="Escribí el nombre..."></div>
            <div class="col-md-4"><label class="form-label" for="estadoEspecialidad">Estado</label><select id="estadoEspecialidad" class="form-select"><option value="todos">Todas</option><option value="activa">Activas</option><option value="inactiva">Inactivas</option></select></div>
        </div>
        <div class="gestion-filter-note mt-3"><span id="cantidadEspecialidades">{{ $especialidades->count() }}</span> especialidades encontradas</div>
    </div>
    <div class="row g-3" id="listadoEspecialidades">
        @foreach($especialidades as $especialidad)
            <div class="col-md-6 col-xl-4 gestion-especialidad-item" data-search="{{ mb_strtolower($especialidad->nombre.' '.$especialidad->descripcion) }}" data-estado="{{ $especialidad->trashed() ? 'inactiva' : 'activa' }}">
                <article class="gestion-specialty-card h-100">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3"><div class="gestion-symbol" aria-hidden="true">✚</div><span class="gestion-status {{ $especialidad->trashed() ? 'gestion-off' : 'gestion-on' }}">{{ $especialidad->trashed() ? 'Inactiva' : 'Activa' }}</span></div>
                    <h2 class="gestion-card-name mb-2">{{ $especialidad->nombre }}</h2>
                    <p class="gestion-card-subtitle">{{ $especialidad->descripcion ?: 'Sin descripción registrada' }}</p>
                    <div class="gestion-card-meta"><span>Duración de turno</span><strong>{{ $especialidad->duracion_turno }} minutos</strong></div>
                    <div class="gestion-card-actions mt-auto"><a class="btn gestion-btn-primary btn-sm" href="{{ route('admin.especialidades.show',$especialidad->id) }}">Ver detalle</a><a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.especialidades.edit',$especialidad->id) }}">Editar</a></div>
                </article>
            </div>
        @endforeach
    </div>
    @if($especialidades->isEmpty())<div class="gestion-empty">No hay especialidades registradas.</div>@endif
    <div id="sinResultadosEspecialidades" class="gestion-empty d-none">No encontramos especialidades con esos filtros.</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscar = document.getElementById('buscarEspecialidad');
    const estado = document.getElementById('estadoEspecialidad');
    const items = document.querySelectorAll('.gestion-especialidad-item');
    function filtrar() {
        const q = buscar.value.toLocaleLowerCase('es').trim();
        let total = 0;
        items.forEach(function (item) {
            const visible = item.dataset.search.includes(q) && (estado.value === 'todos' || item.dataset.estado === estado.value);
            item.classList.toggle('d-none', !visible);
            if (visible) total++;
        });
        document.getElementById('cantidadEspecialidades').textContent = total;
        document.getElementById('sinResultadosEspecialidades').classList.toggle('d-none', total !== 0 || items.length === 0);
    }
    buscar.addEventListener('input', filtrar);
    estado.addEventListener('change', filtrar);
});
</script>
@endsection
