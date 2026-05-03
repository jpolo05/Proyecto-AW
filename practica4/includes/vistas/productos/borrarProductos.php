<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)
Auth::verificarAcceso('Gerente'); //Solo permite entrar a usuarios con rol Gerente
$csrfToken = Auth::getCsrfToken(); //Obtiene un token CSRF (seguridad)

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0); //Intenta obtener el id del producto (de GET o de POST)
$producto = $id > 0 ? Producto::buscaPorId($id) : null; //Si el id es mayor que 0, busca el producto en la base de datos, si no lo deja null

if (!$producto) { //Si no encuentra producto
    header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Producto+no+encontrado'); //Redirige a la lista de productos (con un mensaje)
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Comprueba si la pagina se esta cargando por un envio de formulario (POST)
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) { //Comprueba que el token sea correcto
        $msg = rawurlencode('Token CSRF inválido');
    } else {
        $ok = Producto::desofertar($id); //Llama a desofertar
        $msg = $ok ? 'Producto+retirado+de+la+oferta' : 'No+se+pudo+retirar+el+producto'; //Mensaje segun resultado
    }
    header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg='.$msg); //Redirige
    exit;
}

$tituloPagina = 'Retirar producto de la oferta';

//Convierte datos antes de meterlos en HTML (seguridad)
$idMostrado = (int)$producto['id'];
$nombre = htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');

//Prepara URLs
$action = htmlspecialchars(RUTA_APP.'includes/vistas/productos/borrarProductos.php', ENT_QUOTES, 'UTF-8'); //Formulario vuelve a este archivo
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/productos/listarProductos.php', ENT_QUOTES, 'UTF-8'); //Cancelar vuelve al listado

//HTML contenido principal (que vera el usuario)
//No borra el producto, solo lo marca como no ofertado
$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Retirar producto</h1>
</div>

<div class="info-categoria">
    <div class="mensaje-alerta">
        <p>Esta acción no eliminará el producto de la base de datos, pero se marcará como 'no ofertado'.</p>
    </div>
    
    <p><strong>ID:</strong> {$idMostrado}</p>
    <p><strong>Nombre:</strong> {$nombre}</p>
    <p><strong>Descripción:</strong> {$descripcion}</p>

</div> <form method="POST" action="$action">
    <input type="hidden" name="csrfToken" value="$csrfToken">
    <input type="hidden" name="id" value="{$idMostrado}">
    
    <div class="buttons-estandar">
        <button type="submit" class="button-delete">Confirmar Retirada</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>
EOS;

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun


