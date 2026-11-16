////validaciones de los formularios////
document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector('form');
    let msjError = document.querySelector('.error'); //espacio vacio donde se inserta el mensaje de error arriba en el formulario

    if (form) {//esto es para asegurarnos de que existe el elemento form y se ejecute solo si existe, para no generar problemas en los scripts donde pueden no haber forms
        form.addEventListener('submit', function (event) {
            let errores = []; //arreglo de errores para ir colocandolos juntos en el formulario

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
            const matInput = document.querySelector('#numMat');
            if (matInput) {
                let matVal = matInput.value.trim().toUpperCase().replace(/[\s.]/g, '');
                const regex = /^(MN|MP)?\d{3,7}$/;
                if (matVal !== "" && !regex.test(matVal)) {
                    errores.push("El formato de la matrícula es inválido (Ej: MN12345).");
                }
            }


            //validar que no esté vacio el campo de contraseña y que cuando se pida repetirla que coincida
            const passwordInput = document.querySelector('#password');
            const passwordConfirmInput = document.querySelector('#password_confirmation'); // Si tenés confirmación de pass

            if (passwordInput) {
                let password = passwordInput.value;
                if (password === '') {
                    errores.push("Por favor ingresá una contraseña.");
                } else if (password.length < 8) {
                    errores.push("La contraseña debe tener al menos 8 caracteres.");
                }

                if (passwordConfirmInput && password !== passwordConfirmInput.value) {
                    errores.push("Las contraseñas no coinciden.");
                }
            }

            //si hay errores en el array se frena el envio del formulario y se muestra el mensaje
            if (errores.length > 0) {
                event.preventDefault();
                msjError.innerHTML = errores.join('<br>'); //se le insertan los errores al campo de error en los formularios
            } else {
                msjError.innerHTML = ""; // si está todo bien se limpia el campo
            }
        });
    }
});
