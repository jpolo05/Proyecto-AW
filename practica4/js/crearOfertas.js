(function () {
    var contenedor = document.getElementById('contenedor-lineas'); //Contenedor de lineas de productos
    var botonAgregar = document.querySelector('.js-agregar-linea'); //Boton para añadir producto
    var inputPrecioFinal = document.getElementById('precioDescuento'); //Precio final introducido
    var inputDescuento = document.getElementById('inputDescuento'); //Input oculto con descuento
    var nodoPrecioTotal = document.getElementById('precioTotal'); //Nodo del precio base total
    var nodoPorcentaje = document.getElementById('porcentajeMostrado'); //Nodo del porcentaje mostrado
    var botonCancelar = document.querySelector('.js-cancelar-oferta'); //Boton cancelar

    if (!contenedor || !botonAgregar || !inputPrecioFinal || !inputDescuento || !nodoPrecioTotal || !nodoPorcentaje) { //Si falta algo, no hace nada
        return;
    }

    var productosGlobal = []; //Productos disponibles para las lineas

    //Carga productos desde data-productos
    function cargarProductos() {
        var bruto = botonAgregar.dataset.productos || '[]'; //JSON en atributo data
        try {
            var parsed = JSON.parse(bruto); //Convierte JSON a array
            if (Array.isArray(parsed)) { //Solo acepta arrays
                productosGlobal = parsed;
            }
        } catch (e) {
            productosGlobal = []; //Si falla, no carga productos
        }
    }

    //Crea el select de productos
    function crearSelectProducto(idSeleccionado) {
        var select = document.createElement('select'); //Select de producto
        select.name = 'productos[]';
        select.required = true;

        var vacia = document.createElement('option'); //Opcion por defecto
        vacia.value = '';
        vacia.textContent = 'Selecciona un producto...';
        select.appendChild(vacia);

        productosGlobal.forEach(function (producto) { //Recorre productos disponibles
            var option = document.createElement('option'); //Crea opcion
            option.value = String(producto.id);
            option.textContent = producto.nombre;
            if (idSeleccionado !== null && String(producto.id) === String(idSeleccionado)) { //Si es el producto actual
                option.selected = true; //Marca selected
            }
            select.appendChild(option);
        });

        return select;
    }

    //Crea una linea de producto y cantidad
    function crearLinea(idSeleccionado, cantidadInicial) {
        var linea = document.createElement('div'); //Contenedor de la linea

        var select = crearSelectProducto(idSeleccionado); //Select de producto
        var inputCantidad = document.createElement('input'); //Input de cantidad
        inputCantidad.type = 'number';
        inputCantidad.name = 'cantidades[]';
        inputCantidad.min = '1';
        inputCantidad.value = String(cantidadInicial || 1);

        var botonEliminar = document.createElement('button'); //Boton para eliminar linea
        botonEliminar.type = 'button';
        botonEliminar.className = 'js-eliminar-linea';
        botonEliminar.textContent = 'Eliminar';

        linea.appendChild(select);
        linea.appendChild(inputCantidad);
        linea.appendChild(botonEliminar);

        return linea;
    }

    //Recalcula precio total de los productos incluidos
    function recalcularPrecios() {
        var selects = document.getElementsByName('productos[]'); //Selects de productos
        var cantidades = document.getElementsByName('cantidades[]'); //Inputs de cantidades
        var totalBase = 0; //Total sin descuento

        selects.forEach(function (select, index) { //Recorre lineas
            var idSeleccionado = select.value; //Producto elegido
            var cantidad = parseInt(cantidades[index].value, 10); //Cantidad elegida
            if (!Number.isFinite(cantidad) || cantidad < 0) { //Evita cantidades invalidas
                cantidad = 0;
            }

            var producto = productosGlobal.find(function (p) { //Busca producto elegido
                return String(p.id) === String(idSeleccionado);
            });

            if (producto) { //Si existe producto
                var precioBase = parseFloat(producto.precio_base || 0); //Precio base
                var iva = parseFloat(producto.iva || 0); //IVA
                totalBase += (precioBase * cantidad) + (precioBase * cantidad * iva / 100); //Suma precio con IVA
            }
        });

        nodoPrecioTotal.innerText = totalBase.toFixed(2); //Muestra total base
        recalcularDescuento(); //Actualiza descuento
    }

    //Recalcula el porcentaje de descuento
    function recalcularDescuento() {
        var totalBase = parseFloat(nodoPrecioTotal.innerText || '0'); //Total sin descuento
        var precioFinal = parseFloat(inputPrecioFinal.value); //Precio final escrito
        var porcentaje = 0.0; //Descuento calculado

        if (totalBase > 0 && Number.isFinite(precioFinal)) { //Solo calcula si hay datos validos
            porcentaje = (1 - (precioFinal / totalBase)) * 100; //Calcula porcentaje
            if (porcentaje < 0) { //No permite descuento negativo
                porcentaje = 0;
            }
        }

        nodoPorcentaje.innerText = porcentaje.toFixed(2); //Muestra porcentaje
        inputDescuento.value = porcentaje.toFixed(2); //Guarda porcentaje para enviar
    }

    //En edicion rellena el precio final a partir del descuento guardado
    function inicializarPrecioFinalEdicion() {
        var totalBase = parseFloat(nodoPrecioTotal.innerText || '0'); //Total calculado
        var descuento = parseFloat(inputDescuento.value || '0'); //Descuento actual

        if (totalBase > 0 && Number.isFinite(descuento) && inputPrecioFinal.value === '') { //Si se puede inicializar
            var precioFinal = totalBase * (1 - (descuento / 100)); //Calcula precio con descuento
            inputPrecioFinal.value = precioFinal.toFixed(2);
            recalcularDescuento();
        }
    }

    botonAgregar.addEventListener('click', function () { //Al pulsar añadir producto
        contenedor.appendChild(crearLinea(null, 1)); //Añade linea vacia
        recalcularPrecios();
    });

    contenedor.addEventListener('click', function (event) { //Escucha clicks dentro del contenedor
        var target = event.target; //Elemento pulsado
        if (!target.classList.contains('js-eliminar-linea')) { //Solo actua en boton eliminar
            return;
        }

        var linea = target.parentElement; //Linea del boton
        if (linea) { //Si existe, la elimina
            linea.remove();
            recalcularPrecios();
        }
    });

    contenedor.addEventListener('change', function (event) { //Escucha cambios en selects o cantidades
        var target = event.target; //Elemento cambiado
        if (target.name === 'productos[]' || target.name === 'cantidades[]') { //Si cambia una linea
            recalcularPrecios();
        }
    });

    inputPrecioFinal.addEventListener('input', recalcularDescuento); //Recalcula al escribir precio final

    if (botonCancelar) { //Si existe boton cancelar
        botonCancelar.addEventListener('click', function () { //Redirige al cancelar
            var url = botonCancelar.dataset.url || ''; //URL del data-url
            if (url !== '') { //Si hay URL
                window.location.href = url;
            }
        });
    }

    cargarProductos(); //Carga productos al iniciar
    recalcularPrecios(); //Calcula precios al cargar
    inicializarPrecioFinalEdicion(); //Rellena precio final si procede

    if (document.getElementsByName('productos[]').length === 0) { //Si no hay lineas, crea una
        contenedor.appendChild(crearLinea(null, 1));
        recalcularPrecios();
    }
})();
