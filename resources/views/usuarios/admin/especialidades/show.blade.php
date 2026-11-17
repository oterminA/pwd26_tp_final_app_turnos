
@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-md-7">

            <div class="card shadow-sm border-0">

                <div class="card-header border-0 py-3"
                     style="background-color: var(--clinic-light);">

                    <h4 class="mb-0" style="color: var(--clinic-dark);">
                        Detalle de especialidad
                    </h4>
                </div>

                <div class="card-body p-4">

                    <div class="mb-3">
                        <h6 class="text-muted">Nombre</h6>
                        <p>{{ $especialidad->nombre }}</p>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6 class="text-muted">Descripcion</h6>
                        <p>{{ $especialidad->descripcion ?? 'Sin descripcion' }}</p>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h6 class="text-muted">Duracion del turno</h6>
                        <p>{{ $especialidad->duracion_turno }} minutos</p>
                    </div>

                    <div class="d-flex justify-content-end gap-2">

                        <a href="{{ route('admin.especialidades.index') }}"
                           class="btn btn-outline-secondary">
                            Volver
                        </a>

                        <a href="{{ route('admin.especialidades.edit', $especialidad->id) }}"
                           class="btn text-white"
                           style="background-color: var(--clinic-primary);">
                            Editar especialidad
                        </a>

                    </div>

                </div>
            </div>

        </div>
    </div>

</div>

@endsection
