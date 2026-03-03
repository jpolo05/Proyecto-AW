<?php
require_once __DIR__ . '/conexion.php';

function productos_listar(): array {
  global $conn;
  $sql = "SELECT id, nombre, descripcion, precio_base FROM productos ORDER BY nombre";
  $res = mysqli_query($conn, $sql);
  if (!$res) return [];

  $out = [];
  while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
  return $out;
}

function producto_nombre($id): string { 
    global $conn;
    $sql = "SELECT nombre FROM productos WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    return $fila['nombre'] ?? "";
}