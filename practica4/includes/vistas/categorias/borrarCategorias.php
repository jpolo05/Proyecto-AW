<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Categoria; //Usa la clase Categoria

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Gerente'); //Solo permite entrar a usuarios con rol Gerente
$csrfToken = Auth::getCsrfToken(); //Obtiene un token CSFR (seguridad)

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0); //Intenta obtener el id de la categoria (de GET o de POST)
$categoria = $id > 0 ? Categoria::buscaPorId($id) : null; //Si el id es mayor que 0, busca la categoria en la base de datos, si no lo deja null

if (!$categoria) { //Si no encuentra categoria
    header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg='.rawurlencode('Categoría no encontrada')); //Redirige a la lista de categorias (con un mensaje)
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Comprueba si la página se está cargando por un envío de formulario (POST)
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) { //Comprueba que el token sea correcto
        $msg = rawurlencode('Token CSRF inválido');
    } else {
        $ok = Categoria::borrar($id); //Llama a borrar
        $msg = $ok ? rawurlencode('Categoría borrada') : rawurlencode('No se pudo borrar la categoría'); //Mensaje segun resultado
    }
    header('Location: '.RUTA_APP.'includes/vistas/categorias/listarCategorias.php?msg='.$msg); //Redirige
    exit;
}

$tituloPagina = 'Borrar categoría';

//Convierte datos antes de meterlos en HTML (seguridad)
$idMostrado = (int)$categoria['id'];
$nombre = htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($categoria['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');

//Prepara URLs
$action = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/borrarCategorias.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/categorias/listarCategorias.php', ENT_QUOTES, 'UTF-8');

//HTML contenido principal (que vera el usuario)
$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Borrar categoría</h1>
</div>

<div class="info-categoria">
    <div class="mensaje-alerta">
        <p>¿Estás seguro de que deseas eliminar esta categoría?</p>
    </div>
    
    <p><strong>ID:</strong> {$idMostrado}</p>
    <p><strong>Nombre:</strong> {$nombre}</p>
    <p><strong>Descripción:</strong> {$descripcion}</p>
</div>

<form method="POST" action="$action">
    <input type="hidden" name="csrfToken" value="$csrfToken">
    <input type="hidden" name="id" value="{$idMostrado}">
    
    <div class="buttons-estandar">
        <button type="submit" class="button-delete">Confirmar borrado</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
