<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../mysql/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$nombre   = trim($_POST['nombre'] ?? '');
$apellidos= trim($_POST['apellidos'] ?? '');
$email    = trim($_POST['email'] ?? '');
$pass1    = $_POST['password'] ?? '';
$pass2    = $_POST['password_confirm'] ?? '';
$imagen   = $_POST['imagen'] ?? null;

// En registro normal, el rol debe ser cliente
$rol = 'cliente';

if ($pass1 !== $pass2) {
    header('Location: registro.php?error=register&err=Contrasenas%20distintas');
}

// Hash de contraseña
$hash = password_hash($pass1, PASSWORD_DEFAULT);

// Insert
$sql = "INSERT INTO usuarios (user, email, nombre, apellidos, contrasena, rol, imagen)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    header('Location: registro.php');
    exit;
}

mysqli_stmt_bind_param($stmt, "sssssss", $username, $email, $nombre, $apellidos, $hash, $rol, $imagen);

if (!mysqli_stmt_execute($stmt)) {
    // Duplicado de user/email (por las restricciones UNIQUE)
    header('Location: registro.php?error=register&err=email%20utilizado');
    exit;
}

mysqli_stmt_close($stmt);

// OK -> ir a login
header('Location: login.php?correcto=true');
exit;