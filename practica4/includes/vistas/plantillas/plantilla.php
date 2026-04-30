<?php 
    //$funcionesJS es una variable donde una pagina puede meter JS extra que solo necesite ella
    $funcionesJS = $funcionesJS ?? ''; //Si $funcionesJS no existe inicializa vacia
?>

<!DOCTYPE html>
<html lang="es">
    <!-- Configuracion -->
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no"> <!-- Responsive -->
        <title><?= $tituloPagina ?></title>

        <!-- Carga de las CSS -->
        <link rel="stylesheet" href="<?= RUTA_CSS.'layout.css' ?>">
        <link rel="stylesheet" href="<?= RUTA_CSS.'componentes.css' ?>">
        <link rel="stylesheet" href="<?= RUTA_CSS.'formulario.css' ?>">

        <!-- Carga el icono pequeño que aparece en la pestaña del navegador -->
        <link rel="icon" type="image/x-icon" href="<?= RUTA_IMGS.'ui/favicon.ico' ?>"> 
    </head>
    <!-- Contenido visible -->
    <body>
        <!-- Estructura general -->
        <div class="contenedor">
            <?php require(RAIZ_APP.'/vistas/comun/cabecera.php');?> <!-- Incluye cabecera comun -->
            <!-- Contenido principal -->   
            <main>
                <?php require(RAIZ_APP.'/vistas/comun/sidebarIzq.php');?> <!-- Incluye sidebarIzq -->
                <article>
                    <div class="div-responsive">
                        <?= $contenidoPrincipal ?> <!-- Esta variable la define cada pagina concreta --> 
                    </div>
                </article>
                <?php require(RAIZ_APP.'/vistas/comun/sidebarDer.php');?> <!-- Incluye sidebarDer -->
            </main>

            <?php require(RAIZ_APP.'/vistas/comun/pie.php');?> <!-- Incluye pie comun -->
        </div>
        
        <?= $funcionesJS ?><!-- Permite que una pagina concreta añada JS propio -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> <!-- Carga la libreria jQuery -->
        <script src="<?= RUTA_JS?>/registro.js"></script> <!-- Carga el archivo registro.js -->
    </body>
</html>
