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
<section class="banner-hero" style="background-image: url('{$rutaImgs}tienda.jpg');">
    <div class="banner-capa-oscura">
        <div class="banner-texto">
            <h1>Bienvenido a Bistro FDI</h1>
            <p>Tu experiencia culinaria comienza aquí</p>
        </div>
    </div>
</section>

<div class="contenido-interior">
    <div class="seccion-grid-bistro">
        
        <div class="columna-izq">
            <article class="card-bistro horizontal">
                <img src="{$rutaImgs}personas.jpg" alt="Experiencia">
                <div class="card-contenido">
                    <h3>Experiencia </h3>
                    <p>Para los amantes de la buena mesa.</p>
                </div>
            </article>

            <article class="card-bistro horizontal">
                <img src="{$rutaImgs}producto.jpg" alt="Nuestros productos">
                <div class="card-contenido">
                    <h3>Nuestros productos</h3>
                    <p>Los mejores platillos de la región, solo en Bistro FDI.</p>
                </div>
            </article>
        </div>

        <div class="columna-der">
            <article class="card-bistro card-verde vertical">
                <img src="{$rutaImgs}tienda.jpg" alt="Nuestro local">
                <div class="card-contenido">
                    <h3>Nuestro local</h3>
                    <p>Consulta nuestra carta, realiza pedidos y sigue su estado en tiempo real.</p>
                    <a href="construccion.php" class="btn-dorado">VER CARTA</a>
                </div>
            </article>
        </div>

    </div>
</div>
EOS;

require __DIR__.'/includes/vistas/plantillas/plantilla.php';
