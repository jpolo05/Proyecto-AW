(function () {
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

        let descuentoTotal = 0.00;
        let htmlOfertas = '';

        // Creamos un objeto rápido con las cantidades actuales del DOM
        let cantidadesActuales = {};
        document.querySelectorAll('.cantidad-carrito').forEach(input => {
            let id = input.name.match(/\d+/)[0]; // Extrae el ID del name="cantidad[ID]"
            cantidadesActuales[id] = parseInt(input.value, 10) || 0;
        });

        if (typeof CONFIG_OFERTAS !== 'undefined' && Array.isArray(CONFIG_OFERTAS)) {
            CONFIG_OFERTAS.forEach(oferta => {
                let maxAplicacionesPack = Infinity;
                let precioBasePack = 0;
                let cumpleOferta = true;

                oferta.lineas.forEach(linea => {
                    let idProd = linea.idProd;
                    let cantReq = parseInt(linea.cantidad);
                    
                    let inputProducto = document.querySelector(`.cantidad-carrito[name="cantidad[${idProd}]"]`);
                    let precioUnidad = inputProducto ? parseFloat(inputProducto.dataset.precio) : 0;

                    // USAMOS cantidadesActuales en lugar de itemsCarrito
                    if (!cantidadesActuales[idProd] || cantidadesActuales[idProd] < cantReq) {
                        cumpleOferta = false;
                    } else {
                        let veces = Math.floor(cantidadesActuales[idProd] / cantReq);
                        if (veces < maxAplicacionesPack) maxAplicacionesPack = veces;
                    }
                    precioBasePack += (precioUnidad * cantReq);
                });

                if (cumpleOferta && maxAplicacionesPack > 0 && maxAplicacionesPack !== Infinity) {
                    let ahorroPorPack = precioBasePack * (parseFloat(oferta.descuento) / 100);
                    let ahorroTotalOferta = Math.round(ahorroPorPack * maxAplicacionesPack * 100) / 100;
                    
                    descuentoTotal += ahorroTotalOferta;
                    htmlOfertas += `<li>Promoción: <strong>${oferta.nombre}</strong> (-${ahorroTotalOferta.toFixed(2)} EUR)</li>`;
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
    }

    document.querySelectorAll('.cantidad-carrito').forEach(function (input) {
        input.addEventListener('input', recalcularTotales);
    });

    // Ejecución inicial para calcular totales al cargar la página
    recalcularTotales();
})();