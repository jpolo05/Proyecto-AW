<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
use es\ucm\fdi\aw\usuarios\Usuario; //Usa la clase Usuario
use es\ucm\fdi\aw\usuarios\FormularioActualizaRol; //Usa la clase FormularioActualizaRol
Auth::verificarAcceso('Gerente'); //Solo permite entrar a usuarios con rol Gerente

$users = Usuario::listar(); //Llama a listar (devuelve un array)

$tituloPagina = 'Listado de usuarios';
$editando = $_GET['user'] ?? null; //Recoge el usuario que se esta editando

//Empieza a crear la tabla HTML
$tablaUsuarios = '
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>';

foreach ($users as $u) { //Recorre cada usuario obtenido de la BD
    //Recoge datos sin escapar
    $userRaw = (string)$u['user'];
    $nombreRaw = (string)$u['nombre'];
    $emailRaw = (string)$u['email'];
    $rolActualRaw = (string)$u['rol'];

    //Convierte datos antes de meterlos en HTML (seguridad)
    $user = htmlspecialchars($userRaw, ENT_QUOTES, 'UTF-8');
    $nombre = htmlspecialchars($nombreRaw, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($emailRaw, ENT_QUOTES, 'UTF-8');
    $rolActual = htmlspecialchars($rolActualRaw, ENT_QUOTES, 'UTF-8');

    $tablaUsuarios .= '<tr>'; //Empieza fila del usuario
    $tablaUsuarios .= "<td>$user</td>";
    $tablaUsuarios .= "<td>$nombre</td>";
    $tablaUsuarios .= "<td>$email</td>";

    if ($editando === $userRaw) { //Si se esta editando este usuario
        $formRol = new FormularioActualizaRol($userRaw, $rolActualRaw); //Crea formulario para cambiar rol
        $htmlFormRol = $formRol->gestiona(); //Llamada a gestiona()
        $tablaUsuarios .= "<td colspan='2'>$htmlFormRol <a href='listarUsuarios.php' class='button-estandar'>Cancelar</a></td>";
    } else {
        $tablaUsuarios .= "<td>$rolActual</td>";
        $tablaUsuarios .= "<td><a href='listarUsuarios.php?user=".urlencode($userRaw)."' class='button-estandar'>Editar</a></td>"; //Enlace para activar modo edicion
    }

    $tablaUsuarios .= '</tr>'; //Cierra fila del usuario
}

$tablaUsuarios .= '</table>'; //Cierra la tabla HTML

$rutaPanelGerente = RUTA_APP.'includes/vistas/paneles/gerente.php'; //URL para volver al panel
//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
<div class="contenedor-gestion">
    <div class="header-admin">
        <h2 class="seccion-titulo">Gestión de Usuarios</h2>
    </div>
    
    $tablaUsuarios
    
    <div class="buttons-estandar">
        <a href="$rutaPanelGerente" class="button-estandar">Volver al Panel</a>
    </div>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
