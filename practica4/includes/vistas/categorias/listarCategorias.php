<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Categoria; //Usa la clase Categoria

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Cliente'); //Solo permite entrar a usuarios con al menos el rol Cliente
$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente'); //Comprueba si es Gerente

$cats = Categoria::listar(); //Llama a listar (devuelve un array)
$tituloPagina = 'Listado de categorías';

//Empieza a crear la tabla HTML
$tablaCategorias = '
    <table class="tabla-carta-centro">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>';

foreach ($cats as $c) { //Recorre cada categoria obtenida de la BD (cada $c representa una categoria)

    ////Convierte datos antes de meterlos en HTML (seguridad)
    $id = (int)$c['id'];
    $nombre = htmlspecialchars($c['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
    $descripcion = htmlspecialchars($c['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');

    //Crea enlace para ver el detalle de la categoria
    $urlVer = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/visualizarCategorias.php?id='.$id, ENT_QUOTES, 'UTF-8');
    $acciones = "<a href=\"$urlVer\" class='link-usuario'>Ver</a>";

    if ($esGerente) {
        //Añade un enlace para editar esa categoria
        $urlActualizar = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/actualizarCategorias.php?id='.$id, ENT_QUOTES, 'UTF-8');
        $acciones .= " <span class='separador-tabla'> / </span> ";
        $acciones .= " <a href=\"$urlActualizar\" class='link-usuario'>Actualizar</a>";
        //Añade un enlace para eliminar la categoria
        $acciones .= " <span class='separador-tabla'> / </span> ";
        $urlEliminar = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/borrarCategorias.php?id='.$id, ENT_QUOTES, 'UTF-8');
        $acciones .= " <a href=\"$urlEliminar\" class='link-usuario js-confirmar-accion' data-confirm='¿Confirma que desea eliminar esta categoría?'>Eliminar</a>";
    }

    //Añade 1 fila a la tabla
    $tablaCategorias .= "
    <tr>
        <td>$id</td>
        <td>$nombre</td>
        <td>$descripcion</td>
        <td>$acciones</td>
    </tr>";
}
$tablaCategorias .= '</table>'; //Cierra la tabla HTML

//Prepara botones inferiores segun rol
if ($esGerente) { //Accesible
    $urlCrear = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/crearCategorias.php', ENT_QUOTES, 'UTF-8');
    $rutaPanelGerente = htmlspecialchars(RUTA_APP.'includes/vistas/paneles/gerente.php', ENT_QUOTES, 'UTF-8');
    $aux = '';
} else { //No accesible
    $urlCrear = '#';
    $rutaPanelGerente = '#';
    $aux = 'none';
}

$botonPanel = $esGerente ? '<a href="'.$rutaPanelGerente.'" class="button-estandar">Volver al Panel</a>' : ''; //Boton volver al panel se crea solo si es Gerente

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
    <div class="seccion-titulo">
        <h1>Categorías</h1>
    </div>
    $tablaCategorias
    <div class="buttons-estandar">
        $botonPanel
        <a href="$urlCrear" class="button-estandar $aux">Crear categoría</a>
    </div>
EOS;

$funcionesJS = "<script src='".RUTA_JS."confirmaciones.js'></script>"; //Carga el archivo JS confirmaciones.js

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
