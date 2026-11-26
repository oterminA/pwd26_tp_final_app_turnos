@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-gestion.css') }}">
@endpush
@section('content')
    <div class="container py-4 py-lg-5 admin-gestion">
        <div class="gestion-header mb-4">
            <div>
                <div class="gestion-eyebrow">ADMINISTRACIÓN / PROFESIONALES</div>
                <h1>Equipo médico</h1>
                <p>Consultá los profesionales, sus especialidades y el estado de sus cuentas.</p>
            </div>
            <a href="{{ route('admin.medicos.create') }}" class="btn gestion-btn-primary">+ Agregar profesional</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="gestion-stat">
                    <span>Profesionales</span><strong>{{ $medicos->count() }}</strong><small>Registrados</small></div>
            </div>
            <div class="col-4">
                <div class="gestion-stat"><span>Activos</span><strong>{{ $activos }}</strong><small>Habilitados</small>
                </div>
            </div>
            <div class="col-4">
                <div class="gestion-stat"><span>Inactivos</span><strong>{{ $inactivos }}</strong><small>Con baja
                        lógica</small></div>
            </div>
        </div>

        <div class="gestion-toolbar mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label for="buscarMedico" class="form-label">Buscar profesional</label>
                    <input id="buscarMedico" type="search" class="form-control"
                        placeholder="Nombre, apellido o matrícula..." autocomplete="off">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="estadoMedico" class="form-label">Estado</label>
                    <select id="estadoMedico" class="form-select">
                        <option value="todos">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <label for="especialidadMedico" class="form-label">Especialidad</label>
                    <select id="especialidadMedico" class="form-select">
                        <option value="todos">Todas las especialidades</option>
                        @foreach ($especialidadesFiltro as $especialidad)
                            <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="gestion-filter-note mt-3"><span id="cantidadMedicos">{{ $medicos->count() }}</span> profesionales
                encontrados</div>
        </div>

        <div class="row g-3" id="listadoMedicos">
            @foreach ($medicos as $medico)
                @php
                    $nombre = $medico->user->nombre_visible ?? 'Profesional sin nombre';
                    $siglas = collect(preg_split('/\s+/', trim($nombre)))
                        ->filter()
                        ->take(2)
                        ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                        ->join('');
                    $foto = $medico->imagen
                        ? (str_starts_with($medico->imagen, 'medicos/')
                            ? asset('storage/' . $medico->imagen)
                            : asset($medico->imagen))
                        : null;
                    $mat =
                        $medico->tipoMat && $medico->numMat
                            ? $medico->tipoMat . ' ' . $medico->numMat
                            : $medico->matricula;
                @endphp
                <div class="col-md-6 col-xl-4 gestion-medico-item"
                    data-search="{{ mb_strtolower($nombre . ' ' . $mat . ' ' . $medico->especialidades->pluck('nombre')->join(' ')) }}"
                    data-estado="{{ $medico->trashed() ? 'inactivo' : 'activo' }}"
                    data-especialidades="{{ $medico->especialidades->pluck('id')->join(',') }}">
                    <article class="gestion-medico-card h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            @if ($foto)
                                <img src="{{ $foto }}" alt="Foto de {{ $nombre }}" class="gestion-avatar"
                                    loading="lazy">
                            @else<div class="gestion-avatar gestion-initials" aria-label="Sin fotografía">
                                    {{ $siglas ?: 'MD' }}</div>
                            @endif
                            <span
                                class="gestion-status {{ $medico->trashed() ? 'gestion-off' : 'gestion-on' }}">{{ $medico->trashed() ? 'Inactivo' : 'Activo' }}</span>
                        </div>
                        <h2 class="gestion-card-name">{{ $nombre }}</h2>
                        <p class="gestion-card-subtitle">
                            {{ $medico->especialidades->pluck('nombre')->join(' · ') ?: 'Sin especialidades' }}</p>
                        <div class="gestion-card-meta"><span>Matrícula</span><strong>{{ $mat ?: 'No informada' }}</strong>
                        </div>
                        <div class="gestion-card-meta"><span>Correo</span><strong class="text-truncate"
                                title="{{ $medico->user->email ?? '' }}">{{ $medico->user->email ?? '-' }}</strong></div>
                        <div class="gestion-card-actions mt-auto">
                            <a class="btn gestion-btn-primary btn-sm"
                                href="{{ route('admin.medicos.show', $medico->id) }}">Ver ficha</a>
                            <a class="btn btn-outline-secondary btn-sm"
                                href="{{ route('admin.medicos.edit', $medico->id) }}">Editar</a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
        @if ($medicos->isEmpty())
            <div class="gestion-empty">Todavía no hay profesionales. Podés registrar el primero desde «Agregar profesional».
            </div>
        @endif
        <div class="gestion-empty d-none" id="sinResultadosMedicos">No encontramos profesionales con esos filtros.</div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buscar = document.getElementById('buscarMedico');
            const estado = document.getElementById('estadoMedico');
            const especialidad = document.getElementById('especialidadMedico');
            const items = document.querySelectorAll('.gestion-medico-item');

            function filtrar() {
                const q = buscar.value.toLocaleLowerCase('es').trim();
                let total = 0;
                items.forEach(function(item) {
                    const ids = item.dataset.especialidades.split(',');
                    const visible = item.dataset.search.includes(q) &&
                        (estado.value === 'todos' || item.dataset.estado === estado.value) &&
                        (especialidad.value === 'todos' || ids.includes(especialidad.value));
                    item.classList.toggle('d-none', !visible);
                    if (visible) total++;
                });
                document.getElementById('cantidadMedicos').textContent = total;
                document.getElementById('sinResultadosMedicos').classList.toggle('d-none', total !== 0 || items
                    .length === 0);
            }
            buscar.addEventListener('input', filtrar);
            estado.addEventListener('change', filtrar);
            especialidad.addEventListener('change', filtrar);
        });
    </script>
@endsection
