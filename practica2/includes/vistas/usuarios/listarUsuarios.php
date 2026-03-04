<?php
require_once __DIR__.'/../../auth.php';
verificarAcceso('Gerente');

require_once __DIR__ . '/../../mysql/usuario_mysql.php';
$users = usuarios_listar();

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

$rolesDisponibles = ['Cliente', 'Cocinero', 'Camarero', 'Gerente'];

foreach ($users as $u) {
    $user = $u['user'];
    $nombre =  $u['nombre'];
    $email = $u['email'];
    $rolActual = $u['rol'];

    $tablaUsuarios .= "<tr>";
    $tablaUsuarios .= "<td>$user</td>";
    $tablaUsuarios .= "<td>$nombre</td>";
    $tablaUsuarios .= "<td>$email</td>";

    if ($editando === $user) {
        $select = "<form action='procesarActualizacion.php?rol=$user' method='POST'>
                    <input type='hidden' name='user' value='$user' />
                    <select name='nuevoRol'>";
        
        foreach ($rolesDisponibles as $r) {
            $selected = ($r === $rolActual) ? 'selected' : '';
            $select .= "<option value='$r' $selected>$r</option>";
        }
        
        $select .= "</select>";
        $tablaUsuarios .= "<td>$select</td>
                           <td>
                            <button type='submit'>Guardar</button>
                            <a href='listarUsuarios.php'>Cancelar</a>
                           </td>";
        $tablaUsuarios .= "</form>";
    } else {
        $tablaUsuarios .= "<td>" . $rolActual . "</td>";
        $tablaUsuarios .= "<td><a href='listarUsuarios.php?user=$user'><button class='button-estandar'>Editar</button></a></td>";
    }

    $tablaUsuarios .= "</tr>";
}

$tablaUsuarios .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>Gestión de Usuarios</h1>
    $tablaUsuarios
EOS;

require __DIR__.'/../plantillas/plantilla.php';