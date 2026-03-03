<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../mysql/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: '.RUTA_APP.'error.php?error=Login-Faltan%20datos');
    exit;
}

// Buscar usuario (por nombre de usuario)
$sql = "SELECT user, email, nombre, apellidos, contrasena, rol, imagen
        FROM usuarios
        WHERE user = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    header('Location: '.RUTA_APP.'error.php?error=Login-Error%20sql');
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);

mysqli_stmt_close($stmt);

if (!$user) {
    header('Location: '.RUTA_APP.'error.php?error=Login-Usuario%20o%20contrase%C3%B1a%20incorrectos');
    exit;
}

$ok = password_verify($password, $user['contrasena']) || hash_equals($user['contrasena'], $password);

if (!$ok) {
    header('Location: '.RUTA_APP.'error.php?error=Login-Usuario%20o%20contrase%C3%B1a%20incorrectos');
    exit;
}

// Guardar sesión
$_SESSION['user']      = $user['user'];
$_SESSION['rol']       = $user['rol'];
$_SESSION['nombre']    = $user['nombre'];
$_SESSION['apellidos'] = $user['apellidos'];
$_SESSION['email']     = $user['email'];
$_SESSION['imagen']    = $user['imagen'] ?? null;
$_SESSION['login']     = true;


switch ($user['rol']) {
    case 'Gerente':
        header('Location: '.RUTA_APP.'admin.php');
        exit;
    case 'Cocinero':
        header('Location: '.RUTA_APP.'cocinero.php');
        exit;
    case 'Camarero':
        header('Location: '.RUTA_APP.'camarero.php');
        exit;
    default:
        header('Location: '.RUTA_APP.'index.php');
        exit;
}