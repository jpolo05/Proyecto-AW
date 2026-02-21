<?php
require_once __DIR__ . '/../../mysql/categoria_mysql.php';
$cats = categorias_listar();
?>
<h1>Categorías</h1>

<table border="1" cellpadding="6">
  <tr><th>ID</th><th>Nombre</th><th>Descripción</th></tr>
  <?php foreach ($cats as $c): ?>
    <tr>
      <td><?= (int)$c['id'] ?></td>
      <td><?= htmlspecialchars($c['nombre']) ?></td>
      <td><?= htmlspecialchars($c['descripcion']) ?></td>
    </tr>
  <?php endforeach; ?>
</table>

