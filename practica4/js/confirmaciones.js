(function () {
    document.querySelectorAll('.js-confirmar-accion').forEach(function (enlace) {
        enlace.addEventListener('click', function (event) {
            var mensaje = enlace.dataset.confirm || '¿Seguro que deseas continuar?';
            if (!window.confirm(mensaje)) {
                event.preventDefault();
            }
        });
    });
})();
