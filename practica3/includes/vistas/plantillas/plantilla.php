<?php 
    $funcionesJS = $funcionesJS ?? '';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <title><?= $tituloPagina ?></title>
        <link rel="stylesheet" href="<?= RUTA_CSS.'layout.css' ?>">
        <link rel="stylesheet" href="<?= RUTA_CSS.'componentes.css' ?>">
        <link rel="stylesheet" href="<?= RUTA_CSS.'formulario.css' ?>">
        <link rel="icon" type="image/x-icon" href="<?= RUTA_IMGS.'ui/favicon.ico' ?>">
    </head>
    <body>
        <div class="contenedor">
            <?php require(RAIZ_APP.'/vistas/comun/cabecera.php');?>
                
            <main>
                <?php require(RAIZ_APP.'/vistas/comun/sideBarIzq.php');?>
                <article>
                    <div class="div-responsive">
                        <?= $contenidoPrincipal ?>
                    </div>
                </article>
                <?php require(RAIZ_APP.'/vistas/comun/sideBarDer.php');?>
            </main>

            <?php require(RAIZ_APP.'/vistas/comun/pie.php');?>
        </div>
        <?= $funcionesJS ?>
    </body>
</html>
