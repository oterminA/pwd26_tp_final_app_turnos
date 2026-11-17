////acá se maneja el codigo js de las librerias tom select y full calendar////
document.addEventListener('DOMContentLoaded', function () {
    //datos de prueba, esto despues se saca para poder poner los de la bd
    const opcionesEspecialidades = [
        { id: 1, nombre: "Pediatría" },
        { id: 2, nombre: "Cardiología" },
        { id: 3, nombre: "Dermatología" }
    ];

    //datos de prueba, esto despues se saca para poder poner los de la bd
    const opcionesMedicos = [
        { id: 1, nombre: "Fulana", apellido: "Fulana", imagen: "/css/Assets/medico-3.png" },
        { id: 2, nombre: "Marta", apellido: "Pereira", imagen: null }
    ];

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
        },
        placeholder: "🔎︎ Ej: Marta Pereira"
    });



    // cuando cambia la especialidad traemos a los medicos que corresponden
    //falta hacer el resto
    document.getElementById('especialidad').addEventListener('change', async function () {
    });

});
