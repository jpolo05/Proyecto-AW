<?php include RAIZ_APP . '/vistas/comun/cabecera.php'; ?>
        
<div style="display:flex; min-height: 80vh;">
    <?php include RAIZ_APP . '/vistas/comun/sideBarIzq.php'; ?>

    <main style="flex-grow: 1;">
        <?= $contenidoPrincipal ?>
    </main>

    <?php include RAIZ_APP . '/vistas/comun/sideBarDer.php'; ?>
</div>

<?php include RAIZ_APP . '/vistas/comun/pie.php'; ?>