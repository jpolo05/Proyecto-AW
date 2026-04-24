(function () {
    function obtenerOfertasConfig() {
        var input = document.getElementById('config-ofertas-json');
        if (!input) {
            return [];
        }

        try {
            var parsed = JSON.parse(input.value || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    var ofertasConfig = obtenerOfertasConfig();

    function recalcularCoinsRecompensas() {
        var totalCoins = 0;

        document.querySelectorAll('.cantidad-recompensa').forEach(function (input) {
            var cantidad = parseInt(input.value, 10);
            var costeCoins = parseInt(input.dataset.coins || '0', 10);

            if (!Number.isFinite(cantidad) || cantidad < 0) {
                cantidad = 0;
            }
            if (!Number.isFinite(costeCoins) || costeCoins < 0) {
                costeCoins = 0;
            }

            totalCoins += cantidad * costeCoins;
        });

        var nodoCoinsSeleccionados = document.getElementById('coinsSeleccionados');
        if (nodoCoinsSeleccionados) {
            nodoCoinsSeleccionados.textContent = String(totalCoins);
        }
    }

    function recalcularTotales() {
        var total = 0;
        document.querySelectorAll('.cantidad-carrito').forEach(function (input) {
            var cantidad = parseInt(input.value, 10);
            var precio = parseFloat(input.dataset.precio || '0');

            if (!Number.isFinite(cantidad) || cantidad < 0) {
                cantidad = 0;
            }
            if (!Number.isFinite(precio) || precio < 0) {
                precio = 0;
            }

            var subtotal = cantidad * precio;
            total += subtotal;

            var celda = input.closest('tr').querySelector('.subtotal-linea');
            if (celda) {
                celda.textContent = subtotal.toFixed(2) + ' EUR';
            }
        });

        var descuentoTotal = 0.0;
        var htmlOfertas = '';
        var cantidadesActuales = {};
        var ofertasSeleccionadas = [];

        document.querySelectorAll('.cantidad-carrito').forEach(function (input) {
            var match = input.name.match(/\d+/);
            if (!match) {
                return;
            }
            cantidadesActuales[match[0]] = parseInt(input.value, 10) || 0;
        });

        document.querySelectorAll('.oferta-disponible:checked').forEach(function (input) {
            ofertasSeleccionadas.push(parseInt(input.value, 10));
        });

        if (Array.isArray(ofertasConfig)) {
            ofertasConfig.forEach(function (oferta) {
                if (!ofertasSeleccionadas.includes(parseInt(oferta.id, 10))) {
                    return;
                }

                var maxAplicacionesPack = Infinity;
                var precioBasePack = 0;
                var cumpleOferta = true;
                var lineasOferta = Array.isArray(oferta.lineas) ? oferta.lineas : [];

                if (lineasOferta.length === 0) {
                    return;
                }

                lineasOferta.forEach(function (linea) {
                    var idProd = linea.idProd;
                    var cantReq = parseInt(linea.cantidad, 10);
                    var inputProducto = document.querySelector('.cantidad-carrito[name="cantidad[' + idProd + ']"]');
                    var precioUnidad = inputProducto ? parseFloat(inputProducto.dataset.precio) : 0;

                    if (!cantidadesActuales[idProd] || cantidadesActuales[idProd] < cantReq) {
                        cumpleOferta = false;
                    } else {
                        var veces = Math.floor(cantidadesActuales[idProd] / cantReq);
                        if (veces < maxAplicacionesPack) {
                            maxAplicacionesPack = veces;
                        }
                    }
                    precioBasePack += (precioUnidad * cantReq);
                });

                if (cumpleOferta && maxAplicacionesPack > 0 && maxAplicacionesPack !== Infinity) {
                    var ahorroPorPack = precioBasePack * (parseFloat(oferta.descuento) / 100);
                    var ahorroTotalOferta = Math.round(ahorroPorPack * maxAplicacionesPack * 100) / 100;

                    descuentoTotal += ahorroTotalOferta;
                    htmlOfertas += '<li>Promoción: <strong>' + oferta.nombre + '</strong> (-' + ahorroTotalOferta.toFixed(2) + ' EUR)</li>';
                }
            });
        }

        var nodoTotal = document.getElementById('totalCarrito');
        if (nodoTotal) {
            nodoTotal.textContent = total.toFixed(2);
        }

        var nodoDescuento = document.getElementById('totalCarritoDescuento');
        if (nodoDescuento) {
            nodoDescuento.textContent = (total - descuentoTotal).toFixed(2);
        }

        var nodoOfertas = document.getElementById('listaOfertasAplicadas');
        if (nodoOfertas) {
            nodoOfertas.innerHTML = htmlOfertas;
        }

        recalcularCoinsRecompensas();
    }

    document.querySelectorAll('.cantidad-carrito').forEach(function (input) {
        input.addEventListener('input', recalcularTotales);
    });

    document.querySelectorAll('.oferta-disponible').forEach(function (input) {
        input.addEventListener('change', recalcularTotales);
    });

    document.querySelectorAll('.cantidad-recompensa').forEach(function (input) {
        input.addEventListener('input', recalcularCoinsRecompensas);
        input.addEventListener('change', recalcularCoinsRecompensas);
    });

    recalcularTotales();
    recalcularCoinsRecompensas();
})();
