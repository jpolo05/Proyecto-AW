(function () {
    document.querySelectorAll('.js-confirmar-accion').forEach(function (enlace) { //Busca enlaces que necesitan confirmacion
        enlace.addEventListener('click', function (event) { //Intercepta el click
            var mensaje = enlace.dataset.confirm || '¿Seguro que deseas continuar?'; //Usa mensaje del data-confirm o uno por defecto
            if (!window.confirm(mensaje)) { //Si el usuario cancela
                event.preventDefault(); //Evita que se abra el enlace
            }
        });
    });
})();
