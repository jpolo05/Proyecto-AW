<?php
require_once __DIR__ . '/includes/config.php';
include __DIR__ . '/includes/vistas/comun/cabecera.php';
?>

<?php 
include __DIR__ . '/includes/vistas/comun/sideBarIzq.php';
?>


<h2 align = "center">Descripción de Bistro FDI</h2>

<hr>

<p align = "center">
Bistro FDI es una aplicación web que permite a los clientes consultar la carta
de productos, realizar pedidos y consultar el estado de los mismos de forma sencilla.
</p>

<br>

<table>
<tr>

<td align="center">
<img src="img/personas.jpg" width="200"><br>
Personas comiendo
</td>

<td align="center">
<img src="img/tienda.jpg" width="200"><br>
Nuestro local
</td>

<td align="center">
<img src="img/producto.jpg" width="200"><br>
Nuestros productos
</td>

</tr>
</table>

<br><br>

<p align="center">
<a href="index.php?pagina=carta">
<button>Ver carta</button>
</a>
</p>

<br>

<p align="center">
<a href="index.php?pagina=carta">
<button>¡ORDENA AHORA!</button>
</a>
</p>

<br><br>

<p align="center">

<img src="img/facebook.png" width="40">

&nbsp;&nbsp;&nbsp;

<img src="img/x.png" width="40">

&nbsp;&nbsp;&nbsp;

<img src="img/instagram.png" width="40">

</p>


<?php include __DIR__ . '/includes/vistas/comun/sideBarDer.php'; ?>

<?php include __DIR__ . '/includes/vistas/comun/pie.php'; ?>