////acá se maneja el codigo js de las librerias tom select y full calendar////
///////////COMENTARIO DE CARO: esto no está bien hecho ni terminado/////////////
document.addEventListener('DOMContentLoaded', function () {

    // inicialización de tom select para especialidad en un objeto
    const selectEspecialidad = new TomSelect("#especialidad", {
        create: false, //falso porque no queremos que el usuario pueda agregar especialidades, solo elegir las que le mostramos
        sortField: { field: "text", direction: "asc" } //los nombres se van a mostrar alfabeticamente
    });

    // acá usamos la funcion para armar cómo se van a mostrar las fotos de los medicos en el menu desplegable
    function mostrarMedico(data, escape) {
        let fotoUrl = data.imagen ? data.imagen : '/css/Assets/medico-default.png'; //esto es para manejar si el medico tiene o no fotos, si la tiene se muestra y sino se coloca una por defecto que tenemos guardada

        return `<div class="producto-opcion d-flex align-items-center gap-2">
            <img src="${escape(fotoUrl)}" alt="Foto" width="32" height="32" class="rounded-circle object-fit-cover">
            <span>${escape(data.nombre)}</span>
            <span>${escape(data.apellido)}</span>
        </div>`;
    }

    // inicialización de tom select para medico en un objeto
    const selectMedico = new TomSelect("#medico", {
        create: false,
        sortField: { field: "text", direction: "asc" },
        render: { option: mostrarMedico }, //acá llamamos a la funcion que muestra la foto de cada medico
    });

    // cuando cambia la especialidad traemos a los medicos que corresponden
    document.getElementById('especialidad').addEventListener('change', async function () {
        let especialidadId = this.value;

        // Limpiamos y avisamos que está cargando
        selectMedico.clear();
        selectMedico.clearOptions();
        selectMedico.addOption({ value: '', text: 'Cargando profesionales...', disabled: true });
        selectMedico.setValue('');

        if (!especialidadId) return;

        try {
            //hacemos la peticion ajax
            let response = await fetch(`/api/medicos-por-especialidad/${especialidadId}`);

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor al buscar médicos.');
            }

            let medicos = await response.json();

            // se limpia lo de 'cargando' porque ya trajimos a los medicos
            selectMedico.clearOptions();
            selectMedico.addOption({ value: '', text: 'Seleccioná un profesional...' });

            //ponemos las opciones para que el usuario elija a los medicos con esa especialidad que se cargaron por ajax
            medicos.forEach(medico => {
                selectMedico.addOption({
                    value: medico.id,
                    text: `${medico.nombre} ${medico.apellido}`,
                    nombre: medico.nombre,
                    apellido: medico.apellido,
                    imagen: medico.imagen // si viene de la base de datos
                });
            });

            selectMedico.refreshOptions();

        } catch (error) {
            console.error('Error AJAX:', error);
            selectMedico.clearOptions();
            selectMedico.addOption({ value: '', text: 'Error al cargar profesionales' });
            selectMedico.refreshOptions();
        }
    });


    // acá se maneja la disponibilidad de ese medico elegido
    //para LECTURA full calendar trae ajax incluido(en la parte de 'events')
    let calendarEl = document.getElementById('calendar'); //recuperamos el elemento del html
    let calendar = null;

    document.getElementById('medico').addEventListener('change', function () { //event listener cuando se selecciona un medico
        let medicoId = this.value; //traemos al medico elegido

        if (!medicoId) { //si el usuario no eligió nada le decimos que lo haga
            calendarEl.innerHTML = '<p class="text-muted text-center mb-0 small">Seleccioná un profesional para ver sus horarios disponibles.</p>';
            return;
        }

        calendarEl.innerHTML = ''; //limpiamos el mensaje

        //inicializacion de full calendar
        calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es', //idioma
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            slotMinTime: '08:00:00', //hora donde abre la clinica
            slotMaxTime: '20:00:00', //hora donde cierra
            allDaySlot: false,
            selectable: true,
            selectConstraint: 'businessHours',

            //acá la libreria maneja su ajax solo para LECTURA
            //esto lo tendriamos que modificar cuando tengamos la ruta correcta
            events: `/api/medicos/${medicoId}/turnos-disponibles`,

            select: function (info) {
                let fechaSeleccionada = info.startStr;

                // se guarda el valor en el input hidden del html para enviarlo en el form
                let inputHidden = document.getElementById('fecha_hora_input');
                if (inputHidden) {
                    inputHidden.value = fechaSeleccionada;
                }

                alert("Turno seleccionado para el: " + new Date(fechaSeleccionada).toLocaleString()); //le mostramos al usuario la fehca que eligió
                calendar.unselect();
            }
        });

        calendar.render();
    });

});
