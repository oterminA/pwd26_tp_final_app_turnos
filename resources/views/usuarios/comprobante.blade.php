<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    {{-- creo que dompdf necesita la ruta entera al css para poder ponerlo de forma externa --}}
    <link rel="stylesheet" href="{{ public_path('css/comprobante.css') }}">
    <title>Comprobante de turno</title>
</head>

<body>
    <div class="recibo-container">
        <div class="header">
            <h1>Comprobante de turno</h1>
            <p>Emitido el {{ now()->format('d/m/Y H:i') }} hs</p>
        </div>

        <div class="codigo-box">
            Código: <span>{{ $turno->codigo }}</span>
        </div>

        <hr class="hr-comprobante">

        <table class="datos">
            <tr>
                <td class="etiqueta">Paciente:</td>
                <td>{{ $turno->paciente->user->name }}</td>
            </tr>
            <tr>
                <td class="etiqueta">DNI:</td>
                <td>{{ $turno->paciente->dni }}</td>
            </tr>
            <tr>
                <td class="etiqueta">Obra social:</td>
                <td>{{ $turno->paciente->obra_social ?? 'Particular' }}</td>
            </tr>
            <tr>
                <td class="etiqueta">Especialidad:</td>
                <td>{{ $turno->especialidad->nombre }}</td>
            </tr>
            <tr>
                <td class="etiqueta">Profesional:</td>
                <td>Dr/a. {{ $turno->medico->user->name }}</td>
            </tr>
            <tr>
                <td class="etiqueta">Fecha del turno:</td>
                <td><strong>{{ $turno->fecha->format('d/m/Y') }}</strong></td>
            </tr>
            <tr>
                <td class="etiqueta">Horario:</td>
                <td><strong>{{ $turno->hora_inicio->format('H:i') }} hs</strong></td>
            </tr>
            <tr>
                <td class="etiqueta">Estado:</td>
                <td><span class="badge estado-{{ strtolower($turno->estado) }}">
                        {{ ucfirst($turno->estado) }}
                    </span></td>
            </tr>
        </table>

        <hr class="hr-comprobante">

        <div class="footer">
            En caso de no poder asistir, recuerde cancelar su turno con anticipación desde el portal. <br>
            <i>Clinica Nombre</i>
        </div>
    </div>
</body>

</html>
