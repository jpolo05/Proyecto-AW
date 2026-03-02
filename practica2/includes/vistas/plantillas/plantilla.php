<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>
</head>
<body>
    <div id="contenedor" style="display: flex; flex-direction: column; min-height: 100vh;">
        <?php require(RAIZ_APP.'/vistas/comun/cabecera.php');?>
            
        <main style="display: flex; flex: 1;">
            <?php require(RAIZ_APP.'/vistas/comun/sidebarIzq.php');?>
            <article style="flex: 1; justify-content: center; align-items: center;">
                <?= $contenidoPrincipal ?>
            </article>
            <?php require(RAIZ_APP.'/vistas/comun/sidebarDer.php');?>
        </main>

        <?php require(RAIZ_APP.'/vistas/comun/pie.php');?>
    </div>
</body>
</html>
