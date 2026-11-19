@extends('layouts.login')
@section('content')
    <div class="card shadow contenedor-login mb-3" style="max-width: 900px; width: 100%;">
        <div class="row g-0 align-items-stretch">

            {{-- ocupa 5 col --}}
            <div class="col-md-5 d-none d-md-block">
                <img src="{{ asset('css/Assets/centro-medico.png') }}"
                    class="img-fluid rounded-start h-100 w-100 object-fit-cover" alt="Imagen de la recepcion de un hospital">
            </div>

            {{-- ocupa 7 col --}}
            <div class="col-md-7 d-flex align-items-center">
                <div class="card-body p-4 w-100">

                    <div class="text-center mb-4">
                        <h1 class="h3 fw-bold">Nombre Clínica</h1>
                        <h5 class="text-muted">Inicio de sesión</h5>
                        <h6 class="text-muted fst-italic">-Profesional-</h6>

                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('medico.autenticar') }}" method="POST">
                        @csrf
                        <div class="alert alert-danger error d-none" role="alert"></div>

                        <div class="mb-3">
                            {{-- lleva tom select --}}
                            <label for="tipoMat" class="form-label">Tipo de matrícula</label>
                            <select class="form-select" id="tipoMat" name="tipoMat" required>
                                <option value="" disabled @selected(!old('tipoMat'))>Seleccione una opción</option>
                                <option value="MN99" @selected(old('tipoMat') == 'MN99')>Nacional</option>
                                <option value="MP2" @selected(old('tipoMat') == 'MP2')>Buenos Aires</option>
                                <option value="MP1" @selected(old('tipoMat') == 'MP1')>Capital Federal</option>
                                <option value="MP3" @selected(old('tipoMat') == 'MP3')>Catamarca</option>
                                <option value="MP16" @selected(old('tipoMat') == 'MP16')>Chaco</option>
                                <option value="MP17" @selected(old('tipoMat') == 'MP17')>Chubut</option>
                                <option value="MP4" @selected(old('tipoMat') == 'MP4')>Córdoba</option>
                                <option value="MP5" @selected(old('tipoMat') == 'MP5')>Corrientes</option>
                                <option value="MP6" @selected(old('tipoMat') == 'MP6')>Entre Ríos</option>
                                <option value="MP18" @selected(old('tipoMat') == 'MP18')>Formosa</option>
                                <option value="MP7" @selected(old('tipoMat') == 'MP7')>Jujuy</option>
                                <option value="MP19" @selected(old('tipoMat') == 'MP19')>La Pampa</option>
                                <option value="MP8" @selected(old('tipoMat') == 'MP8')>La Rioja</option>
                                <option value="MP9" @selected(old('tipoMat') == 'MP9')>Mendoza</option>
                                <option value="MP20" @selected(old('tipoMat') == 'MP20')>Misiones</option>
                                <option value="MP21" @selected(old('tipoMat') == 'MP21')>Neuquén</option>
                                <option value="MP22" @selected(old('tipoMat') == 'MP22')>Río Negro</option>
                                <option value="MP10" @selected(old('tipoMat') == 'MP10')>Salta</option>
                                <option value="MP11" @selected(old('tipoMat') == 'MP11')>San Juan</option>
                                <option value="MP12" @selected(old('tipoMat') == 'MP12')>San Luis</option>
                                <option value="MP23" @selected(old('tipoMat') == 'MP23')>Santa Cruz</option>
                                <option value="MP13" @selected(old('tipoMat') == 'MP13')>Santa Fe</option>
                                <option value="MP14" @selected(old('tipoMat') == 'MP14')>Santiago del Estero</option>
                                <option value="MP24" @selected(old('tipoMat') == 'MP24')>Tierra del Fuego</option>
                                <option value="MP15" @selected(old('tipoMat') == 'MP15')>Tucumán</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="numMat" class="form-label">Número de matrícula</label>
                            <input type="text" name="numMat" id="numMat" class="form-control"
                                value="{{ old('numMat') }}" placeholder="Ej: 12345678" maxlength="8" pattern="[0-9]{7,8}"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" id="password"
                                placeholder="••••••••••" required minlength="8" maxlength="12">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Ingresar</button>

                        <hr class="my-3">

                        <div class="text-center">
                            <a href="{{ url('/') }}" class="text-secondary text-decoration-none small mb-0">Volver</a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
