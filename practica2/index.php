<?php

require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Portada';

$contenidoPrincipal = <<<EOS
<div>
    <h2 align="center">Descripción de Bistro FDI</h2>
    <hr style="width: 74%;">
    <p align="center">
        Bistro FDI es una aplicación web que permite a los clientes consultar la carta
        de productos, realizar pedidos y consultar el estado de los mismos de forma sencilla. [cite: 6]
    </p>
    <br>
    <table>
        <tr>
            <td align="center">
                <img src="img/uploads/personas.jpg" width="200" alt="Personas comiendo"><br>
                Personas comiendo
            </td>
            <td align="center">
                <img src="img/uploads/tienda.jpg" width="200" alt="Nuestro local"><br>
                Nuestro local
            </td>
            <td align="center">
                <img src="img/uploads/producto.jpg" width="200" alt="Nuestros productos"><br>
                Nuestros productos
            </td>
        </tr>
    </table>
    <br>
    <p align="center">
        <a href="carta.php"><button>Ver carta</button></a>
    </p>
    <p align="center">
        <a href="carta.php"><button>¡ORDENA AHORA!</button></a>
    </p>
    <br>
    <p align="center">
        <img src="img/uploads/facebook.png" width="40" alt="Facebook"> &nbsp;&nbsp;&nbsp;
        <img src="img/uploads/x.png" width="40" alt="X"> &nbsp;&nbsp;&nbsp;
        <img src="img/uploads/instagram.png" width="40" alt="Instagram">
    </p>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';