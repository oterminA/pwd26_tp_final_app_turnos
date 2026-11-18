
@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-md-7">

            <div class="card shadow-sm border-0">

                <div class="card-header border-0 py-3"
                     style="background-color: var(--clinic-light);">

                    <h4 class="mb-0" style="color: var(--clinic-dark);">
                        Agregar especialidad
                    </h4>
                </div>

                <div class="card-body p-4">

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.especialidades.store') }}"
                          method="POST">

                        @csrf

                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre de la especialidad
                            </label>

                            <input type="text"
                                   class="form-control"
                                   id="nombre"
                                   name="nombre"
                                   value="{{ old('nombre') }}"
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
                                      rows="3">{{ old('descripcion') }}</textarea>

                            @error('descripcion')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="duracion_turno" class="form-label">
                                Duracion del turno (minutos)
                            </label>

                            <input type="number"
                                   class="form-control"
                                   id="duracion_turno"
                                   name="duracion_turno"
                                   value="{{ old('duracion_turno', 30) }}"
                                   min="1"
                                   required>

                            @error('duracion_turno')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">

                            <a href="{{ route('admin.especialidades.index') }}"
                               class="btn btn-outline-secondary">
                                Cancelar
                            </a>

                            <button type="submit"
                                    class="btn text-white"
                                    style="background-color: var(--clinic-primary);">
                                Guardar especialidad
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

@endsection
