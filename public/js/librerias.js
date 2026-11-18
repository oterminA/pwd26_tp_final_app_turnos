//// acá se maneja el código JS de las librerías Tom Select y Flatpickr ////
document.addEventListener('DOMContentLoaded', function () {
    const opcionesEspecialidades = [
        { id: 1, nombre: "Pediatría" },
        { id: 2, nombre: "Cardiología" },
        { id: 3, nombre: "Dermatología" }
    ];

    const opcionesMedicos = [
        { id: 1, nombre: "Fulana", apellido: "Fulana", imagen: "/css/Assets/medico-3.png" },
        { id: 2, nombre: "Marta", apellido: "Pereira", imagen: null }
    ];

    const disponibilidadPrueba = {
        "2026-11-18": ["09:00", "09:30", "10:00", "11:30"],
        "2026-11-19": ["14:00", "14:30", "15:00"],
        "2026-11-19": ["14:00", "14:30", "15:00"],
        "2026-11-25": ["14:00", "14:30", "15:00"],
        "2026-11-21": ["14:00"],
    };

    const fechaInput = document.getElementById('fecha_turno');
    const contenedorHorarios = document.getElementById('contenedor-horarios');
    const fechaHoraInput = document.getElementById('fecha_hora_input');

    const configuracionBase = {
        valueField: "id",
        labelField: "nombre",
        searchField: ["nombre"],
        maxItems: 1,
        create: false,
        openOnFocus: true,
        sortField: { field: "nombre", direction: "asc" }
    };

    function mostrarMedico(data, escape) {
        let fotoUrl = data.imagen ? data.imagen : '/css/Assets/medico-default.png';

        return `<div class="producto-opcion d-flex align-items-center gap-2">
            <img src="${escape(fotoUrl)}" alt="Foto" width="40" height="40" class="rounded-circle object-fit-cover">
            <span>${escape(data.nombre)} ${escape(data.apellido)}</span>
        </div>`;
    }

    const selectEspecialidad = new TomSelect("#especialidad", {
        ...configuracionBase,
        options: opcionesEspecialidades,
        placeholder: "🔎︎ Ej: Pediatría"
    });

    const selectMedico = new TomSelect("#medico", {
        ...configuracionBase,
        options: opcionesMedicos,
        searchField: ["nombre", "apellido"],
        render: {
            option: mostrarMedico,
            item: mostrarMedico
        },
        placeholder: "🔎︎ Ej: Marta Pereira"
    });



    let disponibilidadActual = {};

    const flatPickr = flatpickr("#fecha_turno", {
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

    selectEspecialidad.on('change', function (especialidadId) {
        selectMedico.clear();
        selectMedico.clearOptions();
        resetearFechaYHorarios();

        if (!especialidadId) {
            selectMedico.disable();
            return;
        }

        selectMedico.enable();
        // cuando se conecta la base ed datos acá va el fetch
        selectMedico.addOptions(opcionesMedicos);
    });

    selectMedico.on('change', function (medicoId) {
        resetearFechaYHorarios();

        if (!medicoId) {
            return;
        }

        // cuando se conecta la base ed datos acá va el fetch
        disponibilidadActual = disponibilidadPrueba;

        flatPickr.set('enable', Object.keys(disponibilidadActual));
        fechaInput.disabled = false;
        contenedorHorarios.innerHTML = '<span class="text-muted small">Seleccioná un día en el calendario.</span>';
    });
});
