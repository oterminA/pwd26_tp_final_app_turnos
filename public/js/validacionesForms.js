////validaciones de los formularios////
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('main form'); //esto hace referencia al form de los login/registro/cuenta, no a algun form que pueda haber en los layout o algo asi

    if (form) {//esto es para asegurarnos de que existe el elemento form y se ejecute solo si existe, para no generar problemas en los scripts donde pueden no haber forms ya que este script está en el layout principal
        form.addEventListener('submit', function (event) {
            let errores = []; //arreglo de errores para ir colocandolos juntos en el formulario, en la parte de arriba
            let msjError = document.querySelector('.error'); //espacio vacio donde se inserta el mensaje de error arriba en el formulario
            const esEdicion = Boolean(form.querySelector('input[name="_method"][value="PUT"]')); //esto es para revisar despues si la contraseña tambien viene de formulario de cuenta donde es opcional cambiarla, para que no genere problemas si es que no se cambia ya que tienen el mismo id


            //validar que no estén vacíos los campos de nombre y apellido
            const nombreInput = document.querySelector('#nombre');
            const apellidoInput = document.querySelector('#apellido');
            if (nombreInput) {
                let nombre = nombreInput.value.trim();
                const regexNombre = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s'-]{2,30}$/;

                if (nombre === '') {
                    errores.push("Por favor ingresá tu nombre.");
                } else if (!regexNombre.test(nombre)) {
                    errores.push("El nombre no es válido (solo debe contener letras).");
                }
            }
            if (apellidoInput) {
                let apellido = apellidoInput.value.trim();
                const regexApellido = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s'-]{2,30}$/;

                if (apellido === '') {
                    errores.push("Por favor ingresá tu apellido.");
                } else if (!regexApellido.test(apellido)) {
                    errores.push("El apellido no es válido (solo debe contener letras).");
                }
            }


            ///validar que la imagen subida como foto del medico sea del formato y tamaño esperado
            const fotoInput = document.querySelector('#fotoProfesional');
            if (fotoInput && fotoInput.files && fotoInput.files.length > 0) {
                const archivo = fotoInput.files[0];
                const tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
                const maxBytes = 2 * 1024 * 1024;  // 2mB

                if (!tiposPermitidos.includes(archivo.type)) {
                    errores.push('La imagen debe ser JPG, PNG o WEBP.');
                } else if (archivo.size > maxBytes) {
                    errores.push('La imagen no puede superar los 2MB.');
                }
            }

            //validar lo mismo de nombre y apellido pero en el name que se usa en 'mi cuenta'
            const nameInput = document.querySelector('#name');
            if (nameInput) {
                let name = nameInput.value.trim();
                const regexName = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s'-]{2,30}$/;

                if (name === '') {
                    errores.push("Por favor ingresá tu nombre y apellido.");
                } else if (!regexName.test(name)) {
                    errores.push("Nombre y apellido no son válidos (solo deben contener letras).");
                }
            }


            //validar que elijan un tipo de matricula los medicos
            const tipoMatInput = document.querySelector('#tipoMat');
            if (tipoMatInput) {
                let tipoMat = tipoMatInput.value.trim();
                if (tipoMat === "") {
                    errores.push("Campo obligatorio");
                }
            }


            //validar formato de email, que no esté vacio el campo y que cuando se pida repetir que coincida
            const emailInput = document.querySelector('#email');
            const emailConfirmInput = document.querySelector('#email_confirmation');
            if (emailInput) {
                let email = emailInput.value.trim();
                if (email === '') {
                    errores.push("Por favor ingresá tu email.");
                } else if (!emailInput.checkValidity()) {
                    errores.push("El formato del email es inválido.");
                }

                if (emailConfirmInput && email !== emailConfirmInput.value.trim()) {
                    errores.push("Los correos electrónicos no coinciden.");
                }
            }


            //validar que no esté vacio el campo de dni y que sea un numero
            const dniInput = document.querySelector('#dni');
            if (dniInput) {
                let dni = dniInput.value.trim();
                if (dni === "" || isNaN(dni)) {
                    errores.push("El DNI debe ser un valor numérico válido.");
                }
            }


            //validar formato de numero de matricula y que no esté vacioel campo(no validamos que exista realmente)
            const matInput = document.querySelector('#numMat, #matricula'); //para los dos formularios
            if (matInput) {
                let matVal = matInput.value.trim().toUpperCase().replace(/[\s.]/g, '');
                const regex = /^(MN|MP)?\d{3,7}$/;
                if (matVal !== "" && !regex.test(matVal)) {
                    errores.push("El formato de la matrícula es inválido (Ej: MN12345).");
                }
            }


            //validar que no esté vacio el campo de contraseña y que cuando se pida repetirla que coincida
            const passwordInput = form.querySelector('#password');
            const passwordConfirmInput = form.querySelector('#password_confirmation');
            if (passwordInput) {
                let password = passwordInput.value;

                if (!esEdicion && password === '') {
                    errores.push("Por favor ingresá una contraseña.");
                } else if (password !== '') {
                    if (password.length < 8 || password.length > 12) {
                        errores.push("La contraseña debe tener entre 8 y 12 caracteres.");
                    }
                    if (passwordConfirmInput && password !== passwordConfirmInput.value) {
                        errores.push("Las contraseñas no coinciden.");
                    }
                }
            }

            //validar que no esté vacio el campo de duracion de turno y que sea un numero
            const durTurnoInput = document.querySelector('#duracion_turno');
            if (durTurnoInput) {
                let duracionTurno = durTurnoInput.value.trim();
                if (duracionTurno === "" || isNaN(duracionTurno)) {
                    errores.push("La duración del turno debe ser un valor numérico válido.");
                }
            }


            //validar que se elija una opcion del estado de la especialidad
            const estadoEspInput = document.querySelector('#estado');
            if (estadoEspInput) {
                let estadoEspecialidad = estadoEspInput.value.trim();
                if (estadoEspecialidad === "") {
                    errores.push("Campo obligatorio"); //se coloca así y no más especifico porque se reutiliza el msj de error para otros forms
                }
            }


            //validar que se elija una opcion del estado de medicos
            const medicoInput = document.querySelector('#medico');
            if (medicoInput) {
                let medico = medicoInput.value.trim();
                if (medico === "") {
                    errores.push("Campo obligatorio");
                }
            }


            //valida que se elija una opcion de fecha para el turno
            const fechaInput = document.querySelector('#fecha_turno');
            if (fechaInput) {
                let fecha = fechaInput.value.trim();
                if (fecha === "") {
                    errores.push("Campo obligatorio");
                }
            }


            //si hay errores en el array se frena el envio del formulario y se muestra el mensaje
            if (errores.length > 0) {
                event.preventDefault();
                if (msjError) { //para que no de error en los scripts donde no se reciben los msjs de validacion de js
                    msjError.innerHTML = errores.join('<br>');
                    msjError.classList.remove('d-none');
                }
            } else if (msjError) {
                msjError.innerHTML = "";
                msjError.classList.add('d-none');
            }
        });
    }
});
