<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/producto_mysql.php';

function pedidos_listar(): array {
  global $conn;
  $sql = "SELECT id, numeroPedido, estado, tipo, fecha, cliente, cocinero, imagenCocinero, total FROM pedidos ORDER BY numeroPedido ASC";
  $res = mysqli_query($conn, $sql);
  if (!$res) return [];

  $out = [];
  while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
  return $out;
}

function pedido_listar($numeroPedido): array {
  global $conn;
  $sql = "SELECT numeroPedido, idProducto, cantidad, subtotal FROM linea_pedido WHERE numeroPedido = ?";
  
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $numeroPedido);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);

  $out = [];
  while ($row = mysqli_fetch_assoc($res)) {
      $row['idProducto'] = producto_nombre($row['idProducto']);
      $out[] = $row;
  }
  return $out;
}

function pedidos_actualizarEstado($numeroPedido, $nuevoEstado, $cocinero): bool {
  global $conn;
  $sql = "UPDATE pedidos SET estado = ?, cocinero = ? WHERE numeroPedido = ?";
  
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ssi", $nuevoEstado, $cocinero, $numeroPedido);
  return mysqli_stmt_execute($stmt);
}