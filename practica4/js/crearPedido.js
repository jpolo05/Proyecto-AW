(function () {
    //Recalcula BistroCoins seleccionados en recompensas
    function recalcularCoinsRecompensas() {
        var totalCoins = 0; //Acumula coins totales

        document.querySelectorAll('.cantidad-recompensa').forEach(function (input) { //Recorre inputs de recompensas
            var cantidad = parseInt(input.value, 10); //Cantidad elegida
            var coins = parseInt(input.dataset.coins || '0', 10); //Coste por unidad

            if (!Number.isFinite(cantidad) || cantidad < 0) { //Evita cantidades invalidas
                cantidad = 0;
            }
            if (!Number.isFinite(coins) || coins < 0) { //Evita costes invalidos
                coins = 0;
            }

            totalCoins += cantidad * coins; //Suma coste
        });

        var nodoCoins = document.getElementById('coinsSeleccionadosPedido'); //Nodo donde se muestra el total
        if (nodoCoins) { //Si existe, actualiza
            nodoCoins.textContent = String(totalCoins);
        }
    }

    //Recalcula el total del pedido segun cantidades
    function recalcularTotalPedido() {
        var total = 0; //Acumula total
        var inputs = document.querySelectorAll('.cantidad-producto'); //Inputs de productos

        inputs.forEach(function (input) { //Recorre productos
            var cantidad = parseInt(input.value, 10); //Cantidad elegida
            var precio = parseFloat(input.dataset.precio || '0'); //Precio unitario

            if (!Number.isFinite(cantidad) || cantidad < 0) { //Evita cantidades invalidas
                cantidad = 0;
            }
            if (!Number.isFinite(precio) || precio < 0) { //Evita precios invalidos
                precio = 0;
            }

            total += cantidad * precio; //Suma subtotal
        });

        var nodoTotal = document.getElementById('totalPedido'); //Nodo donde se muestra el total
        if (nodoTotal) { //Si existe, actualiza
            nodoTotal.textContent = total.toFixed(2);
        }

        recalcularCoinsRecompensas(); //Tambien actualiza BistroCoins
    }

    document.querySelectorAll('.cantidad-producto').forEach(function (input) { //Escucha cambios en productos
        input.addEventListener('input', recalcularTotalPedido);
    });

    document.querySelectorAll('.cantidad-recompensa').forEach(function (input) { //Escucha cambios en recompensas
        input.addEventListener('input', recalcularCoinsRecompensas);
        input.addEventListener('change', recalcularCoinsRecompensas);
    });

    recalcularTotalPedido(); //Calcula al cargar la pagina
    recalcularCoinsRecompensas(); //Calcula coins al cargar
})();
