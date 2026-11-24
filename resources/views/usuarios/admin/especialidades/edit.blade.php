
@extends('layouts.app')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-gestion.css') }}">@endpush

@section('content')

<div class="container py-4 py-lg-5 admin-gestion">
    <div class="gestion-header mb-4">
        <div><div class="gestion-eyebrow">ADMINISTRACIÓN / ESPECIALIDADES</div><h1>Editar especialidad</h1><p>Actualizá los datos y el estado de esta especialidad.</p></div>
        <a href="{{ route('admin.especialidades.index') }}" class="btn btn-outline-secondary">Volver a especialidades</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-7">

            <div class="card shadow-sm border-0 rounded-4">

                <div class="card-header border-0 py-3"
                     style="background-color: var(--clinic-light);">

                    <h4 class="mb-0" style="color: var(--clinic-dark);">
                        Editar especialidad
                    </h4>
                </div>

                <div class="card-body p-4">

                    <form action="{{ route('admin.especialidades.update', $especialidad->id) }}"
                          method="POST">

                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre de la especialidad
                            </label>

                            <input type="text"
                                   class="form-control"
                                   id="nombre"
                                   name="nombre"
                                   value="{{ old('nombre', $especialidad->nombre) }}"
                                   maxlength="255"
                                   required>

                            @error('nombre')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">
                                Descripcion
                            </label>

                            <textarea class="form-control"
                                      id="descripcion"
                                      name="descripcion"
                                      rows="3">{{ old('descripcion', $especialidad->descripcion) }}</textarea>

                            @error('descripcion')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="duracion_turno" class="form-label">
                                Duracion del turno (minutos)
                            </label>

                            <input type="number"
                                   class="form-control"
                                   id="duracion_turno"
                                   name="duracion_turno"
                                   value="{{ old('duracion_turno', $especialidad->duracion_turno) }}"
                                   min="1"
                                   required>

                            @error('duracion_turno')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="estado" class="form-label">
                                Estado de la especialidad
                            </label>

                            <select name="estado"
                                    id="estado"
                                    class="form-select"
                                    required>

                                <option value="activa"
                                    @selected(old('estado', $especialidad->trashed() ? 'inactiva' : 'activa') == 'activa')>
                                    Activa
                                </option>

                                <option value="inactiva"
                                    @selected(old('estado', $especialidad->trashed() ? 'inactiva' : 'activa') == 'inactiva')>
                                    Inactiva
                                </option>

                            </select>

                            @error('estado')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">

                            <a href="{{ route('admin.especialidades.index') }}"
                               class="btn btn-outline-secondary">
                                Cancelar
                            </a>

                            <button type="submit"
                                    class="btn text-white"
                                    style="background-color: var(--clinic-primary);">
                                Guardar cambios
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

</div>

@endsection
