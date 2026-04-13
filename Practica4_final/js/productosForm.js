(function () {
    var base = document.getElementById('precio_base');
    var iva = document.getElementById('iva');
    var total = document.getElementById('precio_final');

    if (!base || !iva || !total) {
        return;
    }

    function recalcula() {
        var b = parseFloat(base.value || '0');
        var i = parseFloat(iva.value || '0');

        if (!Number.isFinite(b) || b < 0) {
            b = 0;
        }
        if (!Number.isFinite(i) || i < 0) {
            i = 0;
        }

        var resultado = b + (b * i / 100);
        var sufijo = total.dataset.sufijo || '';
        total.value = resultado.toFixed(2) + sufijo;
    }

    base.addEventListener('input', recalcula);
    iva.addEventListener('change', recalcula);
    recalcula();
})();
