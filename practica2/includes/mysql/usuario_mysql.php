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

function usuarios_crear_editar($user, $email, $nombre, $apellidos, $contrasena, $rol, $imagen): bool {
    global $conn;

    if($contrasena === null) {
        $sql = "UPDATE usuarios SET email = ?, nombre = ?, apellidos = ?, rol = ?, imagen = ? WHERE user = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssss", $email, $nombre, $apellidos, $rol, $imagen, $user);
        }
        else{
            return false;
        } 
    }
    else{
        $sql = "INSERT INTO usuarios (user, email, nombre, apellidos, contrasena, rol, imagen) 
            VALUES (?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
                user = VALUES(user),
                email = VALUES(email),
                nombre = VALUES(nombre), 
                apellidos = VALUES(apellidos), 
                contrasena = VALUES(contrasena),
                rol = VALUES(rol), 
                imagen = VALUES(imagen)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssss", $user, $email, $nombre, $apellidos, $contrasena, $rol, $imagen);
        }
        else{
            return false;
        }
    }
    $resultado = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $resultado;
}

function usuarios_actualiza_rol($user, $rol): bool {
    global $conn;

    $sql = "UPDATE usuarios SET rol = ? WHERE user = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $rol, $user);
        $resultado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $resultado;
    }
    return false;
}

function usuarios_borrar($user): bool {
    global $conn;

    $sql = "DELETE FROM usuarios WHERE user = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $user);
        $resultado = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
        return $resultado;
    }
    return false;
}