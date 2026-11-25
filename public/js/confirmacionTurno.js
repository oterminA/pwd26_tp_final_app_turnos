document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-turno');
    const modal = document.getElementById('modalTurno');
    const error = form.querySelector('.error');
    const fechaHora = document.getElementById('fecha_hora_input');
    const fecha = document.getElementById('fecha_turno');
    const medico = document.getElementById('medico').tomselect;
    const especialidad = document.getElementById('especialidad').tomselect;
    let seleccionConfirmada = '';

    function obtenerSeleccion() {
        if (!medico || !especialidad) return null;
        const profesional = medico.options[medico.getValue()];
        const especialidadElegida = especialidad.options[especialidad.getValue()];
        const horario = fechaHora.value;
        if (!profesional || !especialidadElegida || !fecha.value
            || !/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(horario)
            || horario.slice(0, 10) !== fecha.value) {
            return null;
        }
        return { profesional, especialidadElegida, horario };
    }

    function mostrarError(mensaje) {
        error.textContent = mensaje;
        error.classList.remove('d-none');
    }

    function clave(seleccion) {
        return [medico.getValue(), especialidad.getValue(), seleccion.horario].join('|');
    }

    modal.addEventListener('show.bs.modal', function (event) {
        const seleccion = obtenerSeleccion();
        if (!seleccion) {
            event.preventDefault();
            mostrarError('Seleccioná una especialidad, un médico, una fecha y un horario.');
            return;
        }
        error.classList.add('d-none');
        const { profesional, especialidadElegida, horario } = seleccion;
        document.getElementById('resumen-medico').textContent = profesional.nombre;
        document.getElementById('resumen-especialidad').textContent = especialidadElegida.nombre;
        document.getElementById('resumen-fecha').textContent = horario.slice(0, 10).split('-').reverse().join('/');
        document.getElementById('resumen-hora').textContent = horario.slice(11, 16) + ' hs';
        document.getElementById('resumen-cobertura').textContent = profesional.mostrar_cobertura
            ? (profesional.acepta_obra_social ? 'Acepta tu cobertura' : 'Atención particular')
            : 'Sin información de cobertura';
        seleccionConfirmada = clave(seleccion);
    });

    modal.addEventListener('hidden.bs.modal', function () {
        seleccionConfirmada = '';
    });

    form.addEventListener('submit', function (event) {
        const seleccion = obtenerSeleccion();
        if (!seleccion || !seleccionConfirmada || clave(seleccion) !== seleccionConfirmada) {
            event.preventDefault();
            mostrarError('Revisá los detalles del turno antes de confirmar.');
            if (modal.classList.contains('show')) bootstrap.Modal.getInstance(modal).hide();
        }
    });
});
