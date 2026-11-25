@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@php
    // Los usuarios actuales guardan nombre y apellido juntos en users.name.
    $partes = isset($medico) ? explode(' ', trim($medico->user->name), 2) : ['', ''];
    $nombre = $partes[0];
    $apellido = $partes[1] ?? '';
    $seleccionadas = old('especialidades', isset($medico) ? $medico->especialidades->pluck('id')->all() : []);
    $obrasSeleccionadas = old('obras_sociales', isset($medico) ? $medico->obrasSocialesAsignadas() : []);
@endphp

<form action="{{ isset($medico) ? route('admin.medicos.update', $medico->id) : route('admin.medicos.store') }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @isset($medico)
        @method('PUT')
    @endisset


    <div class="mb-4">
        <label for="imagen" class="form-label">Foto del profesional (opcional)</label>
        <div class="d-flex gap-3 align-items-center flex-wrap">
            @if (isset($medico) && $medico->imagen)
                @php $fotoActual = str_starts_with($medico->imagen, 'medicos/') ? asset('storage/'.$medico->imagen) : asset($medico->imagen); @endphp
                <img src="{{ $fotoActual }}" alt="Foto actual" class="gestion-upload-preview" id="vistaFoto">
            @else
                <img src="" alt="Vista previa de la foto" class="gestion-upload-preview d-none" id="vistaFoto">
            @endif
            <div class="flex-grow-1">
                <input type="file" name="imagen" id="imagen" class="form-control"
                    accept="image/jpeg,image/png,image/webp">
                <small class="text-muted">JPG, PNG o WebP. Máximo 2 MB. Si no elegís otra foto, se mantiene la
                    actual.</small>
                @error('imagen')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input class="form-control" type="text" id="nombre" name="nombre"
                value="{{ old('nombre', $medico->nombre) }}" required maxlength="80">
        </div>
        <div class="col-md-6 mb-3">
            <label for="apellido" class="form-label">Apellido</label>
            <input class="form-control" type="text" id="apellido" name="apellido"
                value="{{ old('apellido', $medico->apellido) }}" required maxlength="80">
        </div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Correo electronico</label>
        <input class="form-control" type="email" id="email" name="email"
            value="{{ old('email', $medico->user->email ?? '') }}" required>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="tipoMat" class="form-label">Tipo de matricula</label>
            <select class="form-select" name="tipoMat" id="tipoMat" required>
                <option value="">Seleccionar</option>
                @foreach (config('administracion.tipos_matricula') as $codigo => $provincia)
                    <option value="{{ $codigo }}" @selected(old('tipoMat', $medico->tipoMat ?? '') == $codigo)>{{ $provincia }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="numMat" class="form-label">Numero de matricula</label>
            <input class="form-control" type="text" name="numMat" id="numMat" maxlength="10"
                pattern="[0-9]{1,10}" inputmode="numeric" title="Ingresa entre 1 y 10 digitos, sin puntos ni guiones"
                value="{{ old('numMat', $medico->numMat ?? '') }}" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="obras_sociales" class="form-label">Obras sociales que acepta</label>
        <select class="form-select" name="obras_sociales[]" id="obras_sociales" multiple required>
            @foreach (config('administracion.obras_sociales') as $codigo => $nombreObra)
                <option value="{{ $codigo }}" @selected(in_array($codigo, $obrasSeleccionadas))>{{ $nombreObra }}</option>
            @endforeach
        </select>
        <small class="text-muted">Seleccioná una o varias coberturas. El listado es fijo.</small>
        @error('obras_sociales')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
        @error('obras_sociales.*')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="especialidades" class="form-label">Especialidades (podes seleccionar varias)</label>
        <select class="form-select" name="especialidades[]" id="especialidades" multiple size="5" required>
            @foreach ($especialidades as $especialidad)
                <option value="{{ $especialidad->id }}" @selected(in_array($especialidad->id, $seleccionadas))>
                    {{ $especialidad->nombre }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Para seleccionar varias, mantené Ctrl presionado.</small>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="password"
                class="form-label">{{ isset($medico) ? 'Nueva contraseña (opcional)' : 'Contraseña' }}</label>
            <input class="form-control" type="password" id="password" name="password" minlength="8"
                {{ isset($medico) ? '' : 'required' }}>
        </div>
        <div class="col-md-6 mb-3">
            <label for="password_confirmation" class="form-label">Repetir contraseña</label>
            <input class="form-control" type="password" id="password_confirmation" name="password_confirmation"
                {{ isset($medico) ? '' : 'required' }}>
        </div>
    </div>

    @isset($medico)
        <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select class="form-select" name="estado" id="estado" required>
                <option value="activo" @selected(old('estado', $medico->trashed() ? 'inactivo' : 'activo') == 'activo')>Activo</option>
                <option value="inactivo" @selected(old('estado', $medico->trashed() ? 'inactivo' : 'activo') == 'inactivo')>Inactivo</option>
            </select>
        </div>
    @endisset

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('admin.medicos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn text-white"
            style="background-color: var(--clinic-primary);">Guardar</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('imagen');
        const vista = document.getElementById('vistaFoto');
        if (input && vista) input.addEventListener('change', function() {
            const archivo = this.files && this.files[0];
            if (!archivo) return;
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(archivo.type) || archivo.size > 2 *
                1024 * 1024) {
                alert('Elegí una foto JPG, PNG o WebP de hasta 2 MB.');
                this.value = '';
                return;
            }
            vista.src = URL.createObjectURL(archivo);
            vista.classList.remove('d-none');
        });
        if (window.TomSelect && document.getElementById('obras_sociales')) {
            const selectObras = document.getElementById('obras_sociales');
            if (!selectObras.tomselect) new TomSelect(selectObras, {
                plugins: ['remove_button'],
                placeholder: 'Elegí una o varias obras sociales',
                maxOptions: null
            });
        }
        if (window.TomSelect && document.getElementById('especialidades')) {
            const select = document.getElementById('especialidades');
            if (!select.tomselect) new TomSelect(select, {
                plugins: ['remove_button'],
                placeholder: 'Elegí una o varias especialidades'
            });
        }
    });
</script>
