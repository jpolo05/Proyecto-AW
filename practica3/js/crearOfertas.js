(function () {
    var contenedor = document.getElementById('contenedor-lineas');
    var botonAgregar = document.querySelector('.js-agregar-linea');
    var inputPrecioFinal = document.getElementById('precioDescuento');
    var inputDescuento = document.getElementById('inputDescuento');
    var nodoPrecioTotal = document.getElementById('precioTotal');
    var nodoPorcentaje = document.getElementById('porcentajeMostrado');
    var botonCancelar = document.querySelector('.js-cancelar-oferta');

    if (!contenedor || !botonAgregar || !inputPrecioFinal || !inputDescuento || !nodoPrecioTotal || !nodoPorcentaje) {
        return;
    }

    var productosGlobal = [];

    function cargarProductos() {
        var bruto = botonAgregar.dataset.productos || '[]';
        try {
            var parsed = JSON.parse(bruto);
            if (Array.isArray(parsed)) {
                productosGlobal = parsed;
            }
        } catch (e) {
            productosGlobal = [];
        }
    }

    function crearSelectProducto(idSeleccionado) {
        var select = document.createElement('select');
        select.name = 'productos[]';
        select.required = true;

        var vacia = document.createElement('option');
        vacia.value = '';
        vacia.textContent = 'Selecciona un producto...';
        select.appendChild(vacia);

        productosGlobal.forEach(function (producto) {
            var option = document.createElement('option');
            option.value = String(producto.id);
            option.textContent = producto.nombre;
            if (idSeleccionado !== null && String(producto.id) === String(idSeleccionado)) {
                option.selected = true;
            }
            select.appendChild(option);
        });

        return select;
    }

    function crearLinea(idSeleccionado, cantidadInicial) {
        var linea = document.createElement('div');

        var select = crearSelectProducto(idSeleccionado);
        var inputCantidad = document.createElement('input');
        inputCantidad.type = 'number';
        inputCantidad.name = 'cantidades[]';
        inputCantidad.min = '1';
        inputCantidad.value = String(cantidadInicial || 1);

        var botonEliminar = document.createElement('button');
        botonEliminar.type = 'button';
        botonEliminar.className = 'js-eliminar-linea';
        botonEliminar.textContent = 'Eliminar';

        linea.appendChild(select);
        linea.appendChild(inputCantidad);
        linea.appendChild(botonEliminar);

        return linea;
    }

    function recalcularPrecios() {
        var selects = document.getElementsByName('productos[]');
        var cantidades = document.getElementsByName('cantidades[]');
        var totalBase = 0;

        selects.forEach(function (select, index) {
            var idSeleccionado = select.value;
            var cantidad = parseInt(cantidades[index].value, 10);
            if (!Number.isFinite(cantidad) || cantidad < 0) {
                cantidad = 0;
            }

            var producto = productosGlobal.find(function (p) {
                return String(p.id) === String(idSeleccionado);
            });

            if (producto) {
                var precioBase = parseFloat(producto.precio_base || 0);
                var iva = parseFloat(producto.iva || 0);
                totalBase += (precioBase * cantidad) + (precioBase * cantidad * iva / 100);
            }
        });

        nodoPrecioTotal.innerText = totalBase.toFixed(2);
        recalcularDescuento();
    }

    function recalcularDescuento() {
        var totalBase = parseFloat(nodoPrecioTotal.innerText || '0');
        var precioFinal = parseFloat(inputPrecioFinal.value);
        var porcentaje = 0.0;

        if (totalBase > 0 && Number.isFinite(precioFinal)) {
            porcentaje = (1 - (precioFinal / totalBase)) * 100;
            if (porcentaje < 0) {
                porcentaje = 0;
            }
        }

        nodoPorcentaje.innerText = porcentaje.toFixed(2);
        inputDescuento.value = porcentaje.toFixed(2);
    }

    function inicializarPrecioFinalEdicion() {
        var totalBase = parseFloat(nodoPrecioTotal.innerText || '0');
        var descuento = parseFloat(inputDescuento.value || '0');

        if (totalBase > 0 && Number.isFinite(descuento) && inputPrecioFinal.value === '') {
            var precioFinal = totalBase * (1 - (descuento / 100));
            inputPrecioFinal.value = precioFinal.toFixed(2);
            recalcularDescuento();
        }
    }

    botonAgregar.addEventListener('click', function () {
        contenedor.appendChild(crearLinea(null, 1));
        recalcularPrecios();
    });

    contenedor.addEventListener('click', function (event) {
        var target = event.target;
        if (!target.classList.contains('js-eliminar-linea')) {
            return;
        }

        var linea = target.parentElement;
        if (linea) {
            linea.remove();
            recalcularPrecios();
        }
    });

    contenedor.addEventListener('change', function (event) {
        var target = event.target;
        if (target.name === 'productos[]' || target.name === 'cantidades[]') {
            recalcularPrecios();
        }
    });

    inputPrecioFinal.addEventListener('input', recalcularDescuento);

    if (botonCancelar) {
        botonCancelar.addEventListener('click', function () {
            var url = botonCancelar.dataset.url || '';
            if (url !== '') {
                window.location.href = url;
            }
        });
    }

    cargarProductos();
    recalcularPrecios();
    inicializarPrecioFinalEdicion();

    if (document.getElementsByName('productos[]').length === 0) {
        contenedor.appendChild(crearLinea(null, 1));
        recalcularPrecios();
    }
})();
