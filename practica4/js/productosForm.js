(function () {
    var base = document.getElementById('precio_base'); //Input de precio base
    var iva = document.getElementById('iva'); //Select de IVA
    var total = document.getElementById('precio_final'); //Input de precio final

    if (!base || !iva || !total) { //Si falta algun campo, no hace nada
        return;
    }

    //Calcula el precio final con IVA
    function recalcula() {
        var b = parseFloat(base.value || '0'); //Precio base
        var i = parseFloat(iva.value || '0'); //IVA elegido

        if (!Number.isFinite(b) || b < 0) { //Evita precio invalido
            b = 0;
        }
        if (!Number.isFinite(i) || i < 0) { //Evita IVA invalido
            i = 0;
        }

        var resultado = b + (b * i / 100); //Precio final
        var sufijo = total.dataset.sufijo || ''; //Sufijo opcional, por ejemplo EUR
        total.value = resultado.toFixed(2) + sufijo; //Muestra resultado
    }

    base.addEventListener('input', recalcula); //Recalcula al cambiar precio
    iva.addEventListener('change', recalcula); //Recalcula al cambiar IVA
    recalcula(); //Calcula al cargar la pagina
})();
