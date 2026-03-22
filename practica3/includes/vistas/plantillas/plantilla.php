<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>
    <link rel="stylesheet" href="<?= RUTA_CSS.'layout.css' ?>">
    <link rel="stylesheet" href="<?= RUTA_CSS.'componentes.css' ?>">
    <link rel="stylesheet" href="<?= RUTA_CSS.'formulario.css' ?>">
</head>
<body>
    <div class="contenedor">
        <?php require(RAIZ_APP.'/vistas/comun/cabecera.php');?>
            
        <main>
            <?php require(RAIZ_APP.'/vistas/comun/sideBarIzq.php');?>
            <article>
                <?= $contenidoPrincipal ?>
            </article>
            <?php require(RAIZ_APP.'/vistas/comun/sideBarDer.php');?>
        </main>

        <?php require(RAIZ_APP.'/vistas/comun/pie.php');?>
    </div>
</body>
</html>
