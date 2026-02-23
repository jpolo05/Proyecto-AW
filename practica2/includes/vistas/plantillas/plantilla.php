<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>
</head>
<body>
    <div id="contenedor">
        <?php
            require(RAIZ_APP.'/vistas/comun/cabecera.php');
            require(RAIZ_APP.'/vistas/comun/sidebarIzq.php');
        ?>
        <main>
            <article>
                <?= $contenidoPrincipal ?>
            </article>
        </main>
        <?php
            require(RAIZ_APP.'/vistas/comun/sidebarDer.php');
            require(RAIZ_APP.'/vistas/comun/pie.php');
        ?>
    </div>
</body>
</html>
