//// acá se maneja el código JS de las librerías Tom Select y Flatpickr ////
document.addEventListener('DOMContentLoaded', function () { //esperamos a que el dom de html se cargue por completo antes de buscar los elementos
    const configuracionBase = { //definimos las opciones compartidas por defecto para tom select
        valueField: "id",
        labelField: "nombre",
        searchField: ["nombre"],
        maxItems: 1,
        create: false,
        openOnFocus: true,
        sortField: { field: "nombre", direction: "asc" }
    };

    /////////////selects estaticos pero que igual usan tom select
    const configSelectEstatico = { //definimos las opciones compartidas por defecto para tom select
        ...configuracionBase, //ademas agregamos las de los select dinamicos
        sortField: { field: "text", direction: "asc" }
    };

    const obraSocialEl = document.getElementById('obra_social'); //incializacion de los select de obra social(singular)
    if (obraSocialEl && !obraSocialEl.tomselect) {
        new TomSelect(obraSocialEl, configSelectEstatico);
    }

    const obrasSocialesEl = document.getElementById('obras_sociales'); //incializacion de los select de obra social(multiple)
    if (obrasSocialesEl && !obrasSocialesEl.tomselect) {
        new TomSelect(obrasSocialesEl, {
            ...configSelectEstatico,
            maxItems: null,
            plugins: ['remove_button'],
            placeholder: 'Seleccioná una o más obras sociales'
        });
    }

    const tipoMatEl = document.getElementById('tipoMat'); //incializacion de los select de tipo de matricula
    if (tipoMatEl && !tipoMatEl.tomselect) {
        new TomSelect(tipoMatEl, configSelectEstatico);
    }

    const especialidadesEl = document.getElementById('especialidades'); //incializacion de los select de especialidades
    if (especialidadesEl && !especialidadesEl.tomselect) {
        new TomSelect(especialidadesEl, {
            ...configSelectEstatico,
            maxItems: null,
            plugins: ['remove_button'],
            placeholder: 'Seleccioná una o más especialidades'
        });
    }

    /////////////////////////////////////////////////////////////

    //recuperamos de los formularios estos elementos
    const especialidadEl = document.getElementById('especialidad');
    const medicoEl = document.getElementById('medico');
    const fechaInput = document.getElementById('fecha_turno');
    const contenedorHorarios = document.getElementById('contenedor-horarios');
    const fechaHoraInput = document.getElementById('fecha_hora_input');

    if (especialidadEl && medicoEl && fechaInput && contenedorHorarios && fechaHoraInput) { //esto es para que si noestán estos elementos en otros formularios no de error
        const opcionesEspecialidades = window.especialidadesBD || []; //tomamos el listado de especialidades que blade inyectó en window.especialidadesBD (o un array vacío si no existe)

        function mostrarMedico(data, escape) { //funcion para dibujar cada medico tanto en la lista desplegable
            const fotoUrl = data.imagen || '/css/Assets/medico-default.jpg';
            let aviso = '';

            if (data.mostrar_cobertura) { //si eso es verdadero, armamos una etiqueta verde o amarilla según data.acepta_obra_social.
                aviso = data.acepta_obra_social
                    ? '<small class="text-success">Acepta tu cobertura</small>'
                    : '<small class="text-warning">Atención particular</small>';
            }

            //devolvemos el HTML con la foto circular, el nombre (protegido con escape() contra inyección HTML) y el aviso de obra social(o sea si el medico acepta o no la obra social)
            return `
                <div class="producto-opcion d-flex align-items-center gap-2">
                    <img src="${escape(fotoUrl)}" alt="Foto" width="40"
                         height="40" class="rounded-circle object-fit-cover">
                    <div>
                        <span>${escape(data.nombre)}</span>
                        <div>${aviso}</div>
                    </div>
                </div>
            `;
        }

        const selectEspecialidad = new TomSelect(especialidadEl, { //incializamos otro tom select pero de las especialidades
            ...configuracionBase,
            options: opcionesEspecialidades,
            placeholder: "Ej: Pediatría"
        });

        const selectMedico = new TomSelect(medicoEl, { //incializamos otro tom select pero de los medicos
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
        let pedidoMedicos = 0;
        let pedidoHorarios = 0;

        let disponibilidadActual = {}; //acá guardamos la disponibilidad que viene del servidor

        const flatPickr = flatpickr(fechaInput, { //inicializamos el flat pickr
            locale: "es",
            altInput: true,           // se hace un input visual alternativo para mostrar la fecha como nosotros, es la forma en la que funcionó
            altFormat: "j F, Y",      // esto ve el usuario "25 Noviembre, 2026"
            dateFormat: "Y-m-d", //esto es lo que se manda
            minDate: "today",
            enable: [], //arranca con los dias deshabilitados hasta elegir medico
            onChange: function (selectedDates, dateStr) { //esto indica que se dispara cuando elpaciente hace click en un dia valido del calendario
                //limpiamos los horarios anteriores
                contenedorHorarios.innerHTML = '';
                fechaHoraInput.value = '';

                const horarios = disponibilidadActual[dateStr] || []; //acá busacmos los horarios disponibles para la fecha que eligió el paciente

                if (horarios.length === 0) { //si no hay horarios disponibles decimo que nohay
                    contenedorHorarios.innerHTML = '<span class="text-muted small">No hay horarios para este día.</span>';
                    return;
                }

                horarios.forEach(hora => { //por cada horario disponible dibujamos los botones en verde
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-outline-success btn-sm';
                    btn.textContent = `${hora} hs`;

                    btn.onclick = () => { //cuando se clickea el horario
                        contenedorHorarios.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        fechaHoraInput.value = `${dateStr} ${hora}:00`;
                    };

                    contenedorHorarios.appendChild(btn);
                });
            }
        });

        function resetearFechaYHorarios() { //funcion aux limpia el calendario, deshabilita el input de fecha, basicamente limpia todo como si se empezara de nuevo
            disponibilidadActual = {};
            flatPickr.clear();
            flatPickr.set('enable', []);
            fechaInput.disabled = true;
            flatPickr.altInput.disabled = true;
            fechaHoraInput.value = '';
            contenedorHorarios.innerHTML = '<span class="text-muted small">Elegí un médico y una fecha.</span>';
        }

        resetearFechaYHorarios();

        selectEspecialidad.on('change', async function (especialidadId) { //esto se ejecuta cuando el paciente elije una especialidad
            const pedido = ++pedidoMedicos;
            ++pedidoHorarios;
            selectMedico.disable();
            selectMedico.clear(); //se limpia al medico elegido
            selectMedico.clearOptions();
            resetearFechaYHorarios(); //llamamosa la funcion que limpia todo porque si se eligió otra especialidad se tienen que mostrar opciones adecuadas

            if (!especialidadId) { //si borra la especialidad deshabilitamos el medico y se corta la ejecucion
                selectMedico.disable();
                return;
            }

            //hacemos una peticion fetch a esa url, se convierte la rta a json, habilitamos la eleccion de medico y los cargamos, hacemos un catch por si falla el servidor o algo
            try {
                const response = await fetch(`/turnos/ajax/medicos/${especialidadId}`);
                if (!response.ok) throw new Error('Error al obtener los médicos');
                const medicos = await response.json();

                if (pedido !== pedidoMedicos) return;
                selectMedico.enable();
                selectMedico.addOptions(medicos);
            } catch (error) {
                if (pedido !== pedidoMedicos) return;
                console.error("Error AJAX:", error);
                contenedorHorarios.innerHTML = '<span class="text-danger small">No se pudieron cargar los médicos. Intentá nuevamente.</span>';
            }
        });

        selectMedico.on('change', async function (medicoId) { //se ejecuta cuando el paciente elige a un medico
            const pedido = ++pedidoHorarios;
            resetearFechaYHorarios(); //limpiamos todo

            const especialidadId = selectEspecialidad.getValue(); //revisamos que medico y especialidad sean seleccionados, sino cortamos
            if (!medicoId || !especialidadId) {
                return;
            }

            //hcemos la consulta por fetch a esa url para traer los dias y horarios libres de ese medico en esa especialidad
            try {
                const response = await fetch(`/turnos/ajax/disponibilidad/${medicoId}?especialidad=${encodeURIComponent(especialidadId)}`);
                if (!response.ok) throw new Error('Error al obtener la disponibilidad');
                const disponibilidad = await response.json();
                if (pedido !== pedidoHorarios) return;
                disponibilidadActual = disponibilidad;

                //habilitamos en flat pickr las fechas que vinieron como claves del objeto
                flatPickr.set('enable', Object.keys(disponibilidadActual));
                fechaInput.disabled = false;
                flatPickr.altInput.disabled = false;
                contenedorHorarios.innerHTML = '<span class="text-muted small">Seleccioná un día en el calendario.</span>';
            } catch (error) { //catch por si hay un error en el servidor o algo
                if (pedido !== pedidoHorarios) return;
                console.error("Error AJAX:", error);
                contenedorHorarios.innerHTML = '<span class="text-danger small">No se pudo cargar la disponibilidad. Intentá nuevamente.</span>';
            }
        });
    }
});
