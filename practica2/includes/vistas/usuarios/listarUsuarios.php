<?php

require_once __DIR__ . '/../../mysql/usuario_mysql.php';
$users = usuarios_listar();

$tituloPagina = 'Listado Usuarios';

$tablaUsuarios = '
    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
        </tr>';

foreach ($users as $u) {
    $id = (int)$u['id'];
    $nombre = htmlspecialchars($u['nombre']);
    $email = htmlspecialchars($u['email']);
    $rol = htmlspecialchars($u['rol']);

    $tablaUsuarios .= "
    <tr>
        <td>$id</td>
        <td>$nombre</td>
        <td>$email</td>
        <td>$rol</td>
    </tr>";
}
$tablaUsuarios .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>Usuarios</h1>
    $tablaUsuarios
EOS;

require __DIR__.'/../plantillas/plantilla.php';


