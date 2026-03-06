<?php
require_once __DIR__.'/../../config.php';
\es\ucm\fdi\aw\Auth::verificarAcceso('Gerente');

$users = \es\ucm\fdi\aw\Usuario::listar();

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
    $user = $u['user'];
    $nombre = $u['nombre'];
    $email = $u['email'];
    $rolActual = $u['rol'];

    $tablaUsuarios .= '<tr>';
    $tablaUsuarios .= "<td>$user</td>";
    $tablaUsuarios .= "<td>$nombre</td>";
    $tablaUsuarios .= "<td>$email</td>";

    if ($editando === $user) {
        $formRol = new \es\ucm\fdi\aw\FormularioActualizaRol($user, $rolActual);
        $htmlFormRol = $formRol->gestiona();
        $tablaUsuarios .= "<td colspan='2'>$htmlFormRol <a href='listarUsuarios.php'>Cancelar</a></td>";
    } else {
        $tablaUsuarios .= "<td>$rolActual</td>";
        $tablaUsuarios .= "<td><a href='listarUsuarios.php?user=$user'><button class='button-estandar'>Editar</button></a></td>";
    }

    $tablaUsuarios .= '</tr>';
}

$tablaUsuarios .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>GestiÃ³n de Usuarios</h1>
    $tablaUsuarios
EOS;

require __DIR__.'/../plantillas/plantilla.php';



