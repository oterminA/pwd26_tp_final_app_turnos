//// acá se maneja el código JS de las librerías Tom Select y Flatpickr ////
document.addEventListener('DOMContentLoaded', function () {
    const configuracionBase = {
        valueField: "id",
        labelField: "nombre",
        searchField: ["nombre"],
        maxItems: 1,
        create: false,
        openOnFocus: true,
        sortField: { field: "nombre", direction: "asc" }
    };

    /////////////selects estaticos pero que igual usan tom select
    const configSelectEstatico = {
        ...configuracionBase,
        sortField: { field: "text", direction: "asc" }
    };

    const obraSocialEl = document.getElementById('obra_social');
    if (obraSocialEl) {
        new TomSelect(obraSocialEl, configSelectEstatico);
    }

    const tipoMatEl = document.getElementById('tipoMat');
    if (tipoMatEl) {
        new TomSelect(tipoMatEl, configSelectEstatico);
    }

    /////////////////////////////////////////////////////////////

    const especialidadEl = document.getElementById('especialidad');
    const medicoEl = document.getElementById('medico');
    const fechaInput = document.getElementById('fecha_turno');
    const contenedorHorarios = document.getElementById('contenedor-horarios');
    const fechaHoraInput = document.getElementById('fecha_hora_input');

    if (especialidadEl && medicoEl && fechaInput && contenedorHorarios && fechaHoraInput) { //esto es para que si noestán estos elementos en otros formularios no de error
        const opcionesEspecialidades = window.especialidadesBD || [];

        function mostrarMedico(data, escape) {
            let fotoUrl = data.imagen ? data.imagen : '/css/Assets/medico-default.png';

            return `<div class="producto-opcion d-flex align-items-center gap-2">
                <img src="${escape(fotoUrl)}" alt="Foto" width="40" height="40" class="rounded-circle object-fit-cover">
                <span>${escape(data.nombre)} ${escape(data.apellido)}</span>
            </div>`;
        }

        const selectEspecialidad = new TomSelect(especialidadEl, {
            ...configuracionBase,
            options: opcionesEspecialidades,
            placeholder: "Ej: Pediatría"
        });

        const selectMedico = new TomSelect(medicoEl, {
            ...configuracionBase,
            options: [],
            searchField: ["nombre", "apellido"],
            render: {
                option: mostrarMedico,
                item: mostrarMedico
            },
            placeholder: "Ej: Marta Pereira"
        });
        selectMedico.disable();

        let disponibilidadActual = {};

        const flatPickr = flatpickr(fechaInput, {
            locale: "es",
            dateFormat: "Y-m-d",
            minDate: "today",
            enable: [],
            onChange: function (selectedDates, dateStr) {
                contenedorHorarios.innerHTML = '';
                fechaHoraInput.value = '';

                const horarios = disponibilidadActual[dateStr] || [];

                if (horarios.length === 0) {
                    contenedorHorarios.innerHTML = '<span class="text-muted small">No hay horarios para este día.</span>';
                    return;
                }

                horarios.forEach(hora => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-outline-success btn-sm';
                    btn.textContent = `${hora} hs`;

                    btn.onclick = () => {
                        contenedorHorarios.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        fechaHoraInput.value = `${dateStr} ${hora}:00`;
                    };

                    contenedorHorarios.appendChild(btn);
                });
            }
        });

        function resetearFechaYHorarios() {
            flatPickr.clear();
            flatPickr.set('enable', []);
            fechaInput.disabled = true;
            fechaHoraInput.value = '';
            contenedorHorarios.innerHTML = '<span class="text-muted small">Elegí un médico y una fecha.</span>';
        }

        selectEspecialidad.on('change', async function (especialidadId) {
            selectMedico.clear();
            selectMedico.clearOptions();
            resetearFechaYHorarios();

            if (!especialidadId) {
                selectMedico.disable();
                return;
            }

            try {
                const response = await fetch(`/turnos/ajax/medicos/${especialidadId}`);
                if (!response.ok) throw new Error('Error al obtener los médicos');
                const medicos = await response.json();

                selectMedico.enable();
                selectMedico.addOptions(medicos);
            } catch (error) {
                console.error("Error AJAX:", error);
                contenedorHorarios.innerHTML = '<span class="text-danger small">No se pudieron cargar los médicos. Intentá nuevamente.</span>';
            }
        });

        selectMedico.on('change', async function (medicoId) {
            resetearFechaYHorarios();

            if (!medicoId) {
                return;
            }

            try {
                const response = await fetch(`/turnos/ajax/disponibilidad/${medicoId}`);
                if (!response.ok) throw new Error('Error al obtener la disponibilidad');
                disponibilidadActual = await response.json();

                flatPickr.set('enable', Object.keys(disponibilidadActual));
                fechaInput.disabled = false;
                contenedorHorarios.innerHTML = '<span class="text-muted small">Seleccioná un día en el calendario.</span>';
            } catch (error) {
                console.error("Error AJAX:", error);
                contenedorHorarios.innerHTML = '<span class="text-danger small">No se pudo cargar la disponibilidad. Intentá nuevamente.</span>';
            }
        });
    }
});
