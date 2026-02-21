<?php
require_once __DIR__ . '/conexion.php';

function categorias_listar(): array {
  global $conn;
  $sql = "SELECT id, nombre, descripcion, imagen FROM categorias ORDER BY nombre";
  $res = mysqli_query($conn, $sql);
  if (!$res) return [];

  $out = [];
  while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
  return $out;
}