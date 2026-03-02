<?php
require_once __DIR__ . '/conexion.php';

function pedidos_listar(): array {
  global $conn;
  $sql = "SELECT id, numeroPedido, estado, tipo, fecha, cliente, cocinero, total FROM pedidos ORDER BY numeroPedido ASC";
  $res = mysqli_query($conn, $sql);
  if (!$res) return [];

  $out = [];
  while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
  return $out;
}
