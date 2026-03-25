let productosGlobal = [];

function agregarLinea(listaProductos) {
    productosGlobal = listaProductos;
    const contenedor = document.getElementById('contenedor-lineas');
    const nuevaLinea = document.createElement('div');

    let selectHtml = `<select name="productos[]" required onchange="recalcularPrecios()">
                        <option value="">Selecciona un producto...</option>`;
    
    listaProductos.forEach(producto => {
        selectHtml += `<option value="${producto.id}">${producto.nombre}</option>`;
    });
    selectHtml += `</select>`;

    nuevaLinea.innerHTML = `
        ${selectHtml}
        <input type="number" name="cantidades[]" min="1" value="1" onchange="recalcularPrecios()">
        <button type="button" onclick="this.parentElement.remove(); recalcularPrecios();">Eliminar</button>
    `;

    contenedor.appendChild(nuevaLinea);
    recalcularPrecios();
}

function recalcularPrecios() {
    const selects = document.getElementsByName('productos[]');
    const cantidades = document.getElementsByName('cantidades[]');
    let totalBase = 0;

    selects.forEach((select, index) => {
        const idSeleccionado = select.value;
        const cantidad = parseInt(cantidades[index].value) || 0;
        
        const producto = productosGlobal.find(p => p.id == idSeleccionado);
        if (producto) {
            totalBase += producto.precio_base * cantidad + (producto.precio_base * cantidad * producto.iva / 100);
        }
    });

    document.getElementById('precioTotal').innerText = totalBase.toFixed(2);
    recalcularDescuento();
}

function recalcularDescuento() {
    const totalBase = parseFloat(document.getElementById('precioTotal').innerText);
    const precioFinal = parseFloat(document.getElementById('precioDescuento').value);
    
    let porcentaje = 0;
    if (totalBase > 0 && !isNaN(precioFinal)) {
        porcentaje = Math.round((1 - (precioFinal / totalBase)) * 100);
        porcentaje = porcentaje < 0 ? 0 : porcentaje;
    }

    document.getElementById('porcentajeMostrado').innerText = porcentaje;
    document.getElementById('inputDescuento').value = porcentaje;
}