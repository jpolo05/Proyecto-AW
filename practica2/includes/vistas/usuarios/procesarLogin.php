<?php

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../mysql/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: login.php?error=login&err=Faltan%20datos');
    exit;
}

// Buscar usuario (por nombre de usuario)
$sql = "SELECT user, email, nombre, apellidos, contrasena, rol, imagen
        FROM usuarios
        WHERE user = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    header('Location: login.php?error=login&err=Error%20consulta');
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);

mysqli_stmt_close($stmt);

if (!$user) {
    header('Location: login.php?error=login&err=Usuario%20o%20contrase%C3%B1a%20incorrectos');
    exit;
}

// Verificación de contraseña:
// 1) Si está hasheada (password_hash), usamos password_verify
// 2) Si aún hay usuarios antiguos en texto plano, lo aceptamos también
$ok = password_verify($password, $user['contrasena']) || hash_equals($user['contrasena'], $password);

if (!$ok) {
    header('Location: login.php?error=login&err=Usuario%20o%20contrase%C3%B1a%20incorrectos');
    exit;
}

// Guardar sesión
$_SESSION['user']      = $user['user'];
$_SESSION['rol']       = $user['rol'];
$_SESSION['nombre']    = $user['nombre'];
$_SESSION['apellidos'] = $user['apellidos'];
$_SESSION['imagen']    = $user['imagen'] ?? null;
$_SESSION['login']     = true;

// Redirigir a inicio
header('Location: ../../../index.php');
exit;