(function () {
    function recalcularTotalPedido() {
        var total = 0;
        var inputs = document.querySelectorAll('.cantidad-producto');

        inputs.forEach(function (input) {
            var cantidad = parseInt(input.value, 10);
            var precio = parseFloat(input.dataset.precio || '0');

            if (!Number.isFinite(cantidad) || cantidad < 0) {
                cantidad = 0;
            }
            if (!Number.isFinite(precio) || precio < 0) {
                precio = 0;
            }

            total += cantidad * precio;
        });

        var nodoTotal = document.getElementById('totalPedido');
        if (nodoTotal) {
            nodoTotal.textContent = total.toFixed(2);
        }
    }

    document.querySelectorAll('.cantidad-producto').forEach(function (input) {
        input.addEventListener('input', recalcularTotalPedido);
    });

    recalcularTotalPedido();
})();
