(function () {
    //Obtiene la configuracion de ofertas desde el input oculto
    function obtenerOfertasConfig() {
        var input = document.getElementById('config-ofertas-json'); //Input con el JSON de ofertas
        if (!input) { //Si no existe, no hay ofertas
            return [];
        }

        try {
            var parsed = JSON.parse(input.value || '[]'); //Convierte el texto JSON a objeto
            return Array.isArray(parsed) ? parsed : []; //Solo acepta arrays
        } catch (e) {
            return []; //Si el JSON falla, devuelve array vacio
        }
    }

    var ofertasConfig = obtenerOfertasConfig(); //Guarda las ofertas configuradas

    //Recalcula los BistroCoins seleccionados en recompensas
    function recalcularCoinsRecompensas() {
        var totalCoins = 0; //Acumula coins totales

        document.querySelectorAll('.cantidad-recompensa').forEach(function (input) { //Recorre inputs de recompensas
            var cantidad = parseInt(input.value, 10); //Cantidad elegida
            var costeCoins = parseInt(input.dataset.coins || '0', 10); //Coste por unidad

            if (!Number.isFinite(cantidad) || cantidad < 0) { //Evita cantidades invalidas
                cantidad = 0;
            }
            if (!Number.isFinite(costeCoins) || costeCoins < 0) { //Evita costes invalidos
                costeCoins = 0;
            }

            totalCoins += cantidad * costeCoins; //Suma coste de esta recompensa
        });

        var nodoCoinsSeleccionados = document.getElementById('coinsSeleccionados'); //Nodo donde se muestra el total
        if (nodoCoinsSeleccionados) { //Si existe, lo actualiza
            nodoCoinsSeleccionados.textContent = String(totalCoins);
        }
    }

    //Recalcula totales, subtotales y ofertas aplicadas
    function recalcularTotales() {
        var total = 0; //Subtotal sin descuentos
        document.querySelectorAll('.cantidad-carrito').forEach(function (input) { //Recorre productos normales
            var cantidad = parseInt(input.value, 10); //Cantidad del producto
            var precio = parseFloat(input.dataset.precio || '0'); //Precio unitario

            if (!Number.isFinite(cantidad) || cantidad < 0) { //Evita cantidades invalidas
                cantidad = 0;
            }
            if (!Number.isFinite(precio) || precio < 0) { //Evita precios invalidos
                precio = 0;
            }

            var subtotal = cantidad * precio; //Subtotal de la linea
            total += subtotal; //Suma al total

            var celda = input.closest('tr').querySelector('.subtotal-linea'); //Celda del subtotal
            if (celda) { //Si existe, actualiza el texto
                celda.textContent = subtotal.toFixed(2) + ' EUR';
            }
        });

        var descuentoTotal = 0.0; //Descuento acumulado
        var htmlOfertas = ''; //HTML de ofertas aplicadas
        var cantidadesActuales = {}; //Mapa idProducto -> cantidad
        var ofertasSeleccionadas = []; //Ofertas marcadas por el usuario

        document.querySelectorAll('.cantidad-carrito').forEach(function (input) { //Construye mapa de cantidades
            var match = input.name.match(/\d+/); //Saca el id del name cantidad[id]
            if (!match) { //Si no encuentra id, salta
                return;
            }
            cantidadesActuales[match[0]] = parseInt(input.value, 10) || 0; //Guarda cantidad
        });

        document.querySelectorAll('.oferta-disponible:checked').forEach(function (input) { //Recoge ofertas seleccionadas
            ofertasSeleccionadas.push(parseInt(input.value, 10));
        });

        if (Array.isArray(ofertasConfig)) { //Comprueba que ofertasConfig sea array
            ofertasConfig.forEach(function (oferta) { //Recorre cada oferta
                if (!ofertasSeleccionadas.includes(parseInt(oferta.id, 10))) { //Si no esta seleccionada, salta
                    return;
                }

                var maxAplicacionesPack = Infinity; //Veces que se puede aplicar la oferta
                var precioBasePack = 0; //Precio base del pack
                var cumpleOferta = true; //Indica si cumple requisitos
                var lineasOferta = Array.isArray(oferta.lineas) ? oferta.lineas : []; //Lineas de la oferta

                if (lineasOferta.length === 0) { //Si no hay lineas, no se aplica
                    return;
                }

                lineasOferta.forEach(function (linea) { //Recorre requisitos de la oferta
                    var idProd = linea.idProd; //Producto requerido
                    var cantReq = parseInt(linea.cantidad, 10); //Cantidad requerida
                    var inputProducto = document.querySelector('.cantidad-carrito[name="cantidad[' + idProd + ']"]');
                    var precioUnidad = inputProducto ? parseFloat(inputProducto.dataset.precio) : 0; //Precio del producto

                    if (!cantidadesActuales[idProd] || cantidadesActuales[idProd] < cantReq) { //Si no cumple cantidad
                        cumpleOferta = false;
                    } else {
                        var veces = Math.floor(cantidadesActuales[idProd] / cantReq); //Veces posibles para esta linea
                        if (veces < maxAplicacionesPack) { //Se queda con el minimo
                            maxAplicacionesPack = veces;
                        }
                    }
                    precioBasePack += (precioUnidad * cantReq); //Suma precio de esta linea del pack
                });

                if (cumpleOferta && maxAplicacionesPack > 0 && maxAplicacionesPack !== Infinity) { //Si la oferta aplica
                    var ahorroPorPack = precioBasePack * (parseFloat(oferta.descuento) / 100); //Ahorro por pack
                    var ahorroTotalOferta = Math.round(ahorroPorPack * maxAplicacionesPack * 100) / 100; //Ahorro redondeado

                    descuentoTotal += ahorroTotalOferta; //Suma descuento total
                    htmlOfertas += '<li>Promoción: <strong>' + oferta.nombre + '</strong> (-' + ahorroTotalOferta.toFixed(2) + ' EUR)</li>'; //Añade oferta al resumen
                }
            });
        }

        var nodoTotal = document.getElementById('totalCarrito'); //Nodo del subtotal
        if (nodoTotal) { //Actualiza subtotal
            nodoTotal.textContent = total.toFixed(2);
        }

        var nodoDescuento = document.getElementById('totalCarritoDescuento'); //Nodo del total con descuento
        if (nodoDescuento) { //Actualiza total con descuento
            nodoDescuento.textContent = (total - descuentoTotal).toFixed(2);
        }

        var nodoOfertas = document.getElementById('listaOfertasAplicadas'); //Lista de ofertas aplicadas
        if (nodoOfertas) { //Actualiza lista
            nodoOfertas.innerHTML = htmlOfertas;
        }

        recalcularCoinsRecompensas(); //Actualiza tambien los BistroCoins
    }

    document.querySelectorAll('.cantidad-carrito').forEach(function (input) { //Escucha cambios en productos
        input.addEventListener('input', recalcularTotales);
    });

    document.querySelectorAll('.oferta-disponible').forEach(function (input) { //Escucha cambios en ofertas
        input.addEventListener('change', recalcularTotales);
    });

    document.querySelectorAll('.cantidad-recompensa').forEach(function (input) { //Escucha cambios en recompensas
        input.addEventListener('input', recalcularCoinsRecompensas);
        input.addEventListener('change', recalcularCoinsRecompensas);
    });

    recalcularTotales(); //Calcula al cargar la pagina
    recalcularCoinsRecompensas(); //Calcula coins al cargar
})();

