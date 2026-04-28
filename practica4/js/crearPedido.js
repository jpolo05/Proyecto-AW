(function () {
    function recalcularCoinsRecompensas() {
        var totalCoins = 0;

        document.querySelectorAll('.cantidad-recompensa').forEach(function (input) {
            var cantidad = parseInt(input.value, 10);
            var coins = parseInt(input.dataset.coins || '0', 10);

            if (!Number.isFinite(cantidad) || cantidad < 0) {
                cantidad = 0;
            }
            if (!Number.isFinite(coins) || coins < 0) {
                coins = 0;
            }

            totalCoins += cantidad * coins;
        });

        var nodoCoins = document.getElementById('coinsSeleccionadosPedido');
        if (nodoCoins) {
            nodoCoins.textContent = String(totalCoins);
        }
    }

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

        recalcularCoinsRecompensas();
    }

    document.querySelectorAll('.cantidad-producto').forEach(function (input) {
        input.addEventListener('input', recalcularTotalPedido);
    });

    document.querySelectorAll('.cantidad-recompensa').forEach(function (input) {
        input.addEventListener('input', recalcularCoinsRecompensas);
        input.addEventListener('change', recalcularCoinsRecompensas);
    });

    recalcularTotalPedido();
    recalcularCoinsRecompensas();
})();
