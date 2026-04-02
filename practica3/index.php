<?php
require_once __DIR__.'/includes/config.php';

$tituloPagina = 'Portada';

$botonGerente = '';
if (($_SESSION['rol'] ?? '') === 'Gerente') {
    $rutaPanelGerente = RUTA_APP.'includes/vistas/paneles/gerente.php';
    $botonGerente = '<div class="center"><a href="'.$rutaPanelGerente.'"><button class="button-estandar">Panel gerente</button></a></div><br>';
}

$rutaImgs = RUTA_IMGS . 'ui/'; // Ruta a la carpeta de interfaz

$contenidoPrincipal = <<<EOS
<section class="banner-hero"> 
    <img src="{$rutaImgs}tienda.jpg" alt="Banner principal"> 
    <div class="banner-capa-oscura">
        <div class="banner-texto">
            <h1>Bienvenido a Bistro FDI</h1>
            <p>Tu experiencia culinaria comienza aquí</p>
        </div>
    </div>
</section>

<div class="contenido-interior">
    <div class="contenedor-tarjetas">
        
        <article class="tarjeta">
            <img src="{$rutaImgs}personas_comiendo.jpg" alt="Experiencia">
            <div class="info">
                <h3>Experiencia</h3>
                <p>Para los amantes de la buena mesa.</p>
            </div>
        </article>

        <a href="includes/vistas/productos/listarProductos.php" class="enlace-tarjeta">
            <article class="tarjeta">
                <img src="{$rutaImgs}platillos.jpg" alt="Productos">
                <div class="info">
                    <h3>Productos</h3>
                    <p>Los mejores platillos de la región. </p>
                    <p><strong>Pulsa aquí para ver la carta.</strong></p>
                </div>
            </article>
        </a>
        
        <article class="tarjeta">
            <img src="{$rutaImgs}tienda.jpg" alt="Local">
            <div class="info">
                <h3>Nuestro local</h3>
                <p>Realiza tus pedidos en tiempo real.</p>
            </div>
        </article>
    </div>
</div>

$botonGerente
<div class="boton-ordenar">
<a href="includes/vistas/pedidos/crearPedido.php" class="button-estandar">ORDENA AHORA</a>
</div>

<div class="redes-sociales">
    <img src="img/ui/facebook.png" alt="Facebook">
    <img src="img/ui/x.png" alt="X">
    <img src="img/ui/instagram.png" alt="Instagram">
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';
