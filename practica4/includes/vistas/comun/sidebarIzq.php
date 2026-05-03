<?php require_once __DIR__.'/../../config.php'; ?> <!-- Carga config.php (1 vez) -->

<nav class="menu-lateral"> <!-- Crea un menu de navegacion lateral -->
	<h2>Navegación</h2>
	<ul>
		<li><a href="<?= RUTA_APP ?>index.php">Inicio</a></li> <!-- Enlace a la portada -->
		<?php if (($_SESSION['rol'] ?? '') === 'Gerente') : ?>
			<li><a href="<?= RUTA_APP ?>includes/vistas/paneles/gerente.php">Panel gerente</a></li> <!-- Muestra solo si es Gerente -->
		<?php endif; ?>
		<?php if (($_SESSION['rol'] ?? '') === 'Cocinero') : ?>
			<li><a href="<?= RUTA_APP ?>includes/vistas/paneles/cocinero.php">Panel cocinero</a></li> <!-- Muestra solo si es Cocinero -->
		<?php endif; ?>
		<?php if (($_SESSION['rol'] ?? '') === 'Camarero') : ?>
			<li><a href="<?= RUTA_APP ?>includes/vistas/paneles/camarero.php">Panel camarero</a></li> <!-- Muestra solo si es Camarero -->
		<?php endif; ?>
		<!-- Enlaces que muestra a todos -->
		<li><a href="<?= RUTA_APP ?>includes/vistas/productos/listarProductos.php">Carta</a></li>
		<li><a href="<?= RUTA_APP ?>includes/vistas/pedidos/carrito.php">Mi carrito</a></li>
		<li><a href="<?= RUTA_APP ?>includes/vistas/usuarios/visualizarUsuarios.php">Mi perfil</a></li>
	</ul>
</nav>

