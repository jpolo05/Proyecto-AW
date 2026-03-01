<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../mysql/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php'); // creo que a login está bien
    exit;
}

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!$idUsuario){
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$nombre   = trim($_POST['nombre'] ?? '');
$apellidos= trim($_POST['apellidos'] ?? '');
$email    = trim($_POST['email'] ?? '');
$pass1    = $_POST['password'] ?? '';
$pass2    = $_POST['password_confirm'] ?? '';
$imagen   = $_POST['imagen'] ?? null;

// Esta pagina solo la puede ver clientes o admin también?
// hay que buscar la forma en la que el gerente puede cambiar esto
$rol = $_POST['cliente'] ?? 'Cliente';

if ($pass1 !== $pass2) {
    header('Location: actualizarUsuarios.php?error=register&err=Contrasenas%20distintas');
}

// Hash de contraseña
$hash = password_hash($pass1, PASSWORD_DEFAULT);

// UPDATE
$sql = "UPDATE usuarios 
        SET user = ?, nombre = ?, apellidos = ?, email = ?, contrasena = ?, rol = ?, imagen =?
        WHERE id = ?
        ";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    header('Location: actualizarUsuarios.php');
    exit;
}

mysqli_stmt_bind_param($stmt, "sssssssi", $username, $nombre, $apellidos, $email, $hash, $rol, $imagen, $idUsuario);

if (!mysqli_stmt_execute($stmt)) {
    // Duplicado de user/email (por las restricciones UNIQUE)
    header('Location: actualizarUsuarios.php?error=register&err=email%20utilizado');
    exit;
}

mysqli_stmt_close($stmt);

// OK -> ir a login
header('Location: login.php?correcto=true');
exit;