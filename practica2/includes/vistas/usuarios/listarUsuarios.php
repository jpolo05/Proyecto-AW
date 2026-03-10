<?php
use es\ucm\fdi\aw\Auth;
require_once __DIR__.'/../../config.php';
use es\ucm\fdi\aw\Usuario;
use es\ucm\fdi\aw\FormularioActualizaRol;
Auth::verificarAcceso('Gerente');

$users = Usuario::listar();

$tituloPagina = 'Listado Usuarios';
$editando = $_GET['user'] ?? null;

$tablaUsuarios = '
    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>';

foreach ($users as $u) {
    $userRaw = (string)$u['user'];
    $nombreRaw = (string)$u['nombre'];
    $emailRaw = (string)$u['email'];
    $rolActualRaw = (string)$u['rol'];

    $user = htmlspecialchars($userRaw, ENT_QUOTES, 'UTF-8');
    $nombre = htmlspecialchars($nombreRaw, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($emailRaw, ENT_QUOTES, 'UTF-8');
    $rolActual = htmlspecialchars($rolActualRaw, ENT_QUOTES, 'UTF-8');

    $tablaUsuarios .= '<tr>';
    $tablaUsuarios .= "<td>$user</td>";
    $tablaUsuarios .= "<td>$nombre</td>";
    $tablaUsuarios .= "<td>$email</td>";

    if ($editando === $userRaw) {
        $formRol = new FormularioActualizaRol($userRaw, $rolActualRaw);
        $htmlFormRol = $formRol->gestiona();
        $tablaUsuarios .= "<td colspan='2'>$htmlFormRol <a href='listarUsuarios.php'>Cancelar</a></td>";
    } else {
        $tablaUsuarios .= "<td>$rolActual</td>";
        $tablaUsuarios .= "<td><a href='listarUsuarios.php?user=".urlencode($userRaw)."'><button class='button-estandar'>Editar</button></a></td>";
    }

    $tablaUsuarios .= '</tr>';
}

$tablaUsuarios .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>Gestión de Usuarios</h1>
    $tablaUsuarios
EOS;

require __DIR__.'/../plantillas/plantilla.php';





