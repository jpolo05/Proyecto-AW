<?php

require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Portada';

$contenidoPrincipal = <<<EOS
<div  align="center">
    <h2>Descripción de Bistro FDI</h2>
    <hr style="width: 74%;">
    <p>
        Bistro FDI es una aplicación web que permite a los clientes consultar la carta
        de productos, realizar pedidos y consultar el estado de los mismos de forma sencilla. [cite: 6]
    </p>
    <br>
    <table>
        <tr>
            <td>
                <img src="img/personas.jpg" width="200" alt="Personas comiendo"><br>
                Personas comiendo
            </td>
            <td>
                <img src="img/tienda.jpg" width="200" alt="Nuestro local"><br>
                Nuestro local
            </td>
            <td>
                <img src="img/producto.jpg" width="200" alt="Nuestros productos"><br>
                Nuestros productos
            </td>
        </tr>
    </table>
    <br>
    <p>
        <a href="carta.php"><button>Ver carta</button></a>
    </p>
    <p>
        <a href="carta.php"><button>¡ORDENA AHORA!</button></a>
    </p>
    <br>
    <p>
        <img src="img/facebook.png" width="40" alt="Facebook"> &nbsp;&nbsp;&nbsp;
        <img src="img/x.png" width="40" alt="X"> &nbsp;&nbsp;&nbsp;
        <img src="img/instagram.png" width="40" alt="Instagram">
    </p>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';