<?php
require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Portada';

$botonGerente = '';
if (($_SESSION['rol'] ?? '') === 'Gerente') {
    $rutaPanelGerente = RUTA_APP.'includes/vistas/paneles/gerente.php';
    $botonGerente = '<div class="center"><a href="'.$rutaPanelGerente.'"><button class="button-estandar">Panel gerente</button></a></div><br>';
}

$contenidoPrincipal = <<<EOS
<div>
    <h2 class="titulo">Descripcion de Bistro FDI</h2>
    <hr>
    <p class="desc">
        Bistro FDI es una aplicacion web que permite a los clientes consultar la carta
        de productos, realizar pedidos y consultar el estado de los mismos de forma sencilla.
    </p>
    <br>
    <div class="center separation">
        <div>
            <img src="img/ui/personas.jpg" width="200" alt="Personas comiendo"><br>
            Personas comiendo
        </div>
        <div>
            <img src="img/ui/tienda.jpg" width="200" alt="Nuestro local"><br>
            Nuestro local
        </div>
        <div>
            <img src="img/ui/producto.jpg" width="200" alt="Nuestros productos"><br>
            Nuestros productos
        </div>
    </div>
    <br>
    <div class="center">
        <a href="includes/vistas/productos/listarProductos.php"><button class="button-estandar">Ver carta</button></a>
    </div>
    <br>
    $botonGerente
    <div class="center">
        <a href="includes/vistas/productos/listarProductos.php"><button class="button-estandar">ORDENA AHORA</button></a>
    </div>
    <br>
    <div class="center">
        <img src="img/ui/facebook.png" width="40" alt="Facebook"> &nbsp;&nbsp;&nbsp;
        <img src="img/ui/x.png" width="40" alt="X"> &nbsp;&nbsp;&nbsp;
        <img src="img/ui/instagram.png" width="40" alt="Instagram">
    </div>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';
