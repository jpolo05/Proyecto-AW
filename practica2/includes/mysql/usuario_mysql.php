<?php
require_once __DIR__ . '/conexion.php';

function usuarios_listar(): array {
    global $conn;

    $sql = "SELECT user, email, nombre, rol FROM usuarios ORDER BY user";
    $res = mysqli_query($conn, $sql);

    if (!$res) return [];
    $out = [];
    while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
    
    mysqli_free_result($res);
    return $out;
}

function usuarios_crear($email, $nombre, $apellidos, $password, $imagen): bool {
    global $conn;

    $sql = "INSERT INTO usuarios (email, nombre, apellidos, contrasena, imagen) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssss", $email, $nombre, $apellidos, $password, $imagen);
        $resultado = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
        return $resultado;
    }
    return false;
}

function usuarios_borrar($email): bool {
    global $conn;

    $sql = "DELETE FROM usuarios WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        $resultado = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
        return $resultado;
    }
    return false;
}

function usuarios_editar($email, $nombre, $apellidos, $rol, $imagen): bool {
    global $conn;

    $sql = "UPDATE usuarios SET nombre = ?, apellidos = ?, rol = ?, imagen = ? WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssss", $nombre, $apellidos, $rol, $imagen, $email);
        $resultado = mysqli_stmt_execute($stmt);
        
        mysqli_stmt_close($stmt);
        return $resultado;
    }
    return false;
}