<?php
use es\ucm\fdi\aw\usuarios\Auth; //Usa la clase Auth
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto
use es\ucm\fdi\aw\usuarios\Recompensa; //Usa la clase Recompensa
use es\ucm\fdi\aw\usuarios\Usuario; //Usa la clase Usuario

require_once __DIR__.'/../../config.php'; //Carga configuracion
Auth::verificarAcceso('Cliente'); //Permite acceso a usuarios logueados
$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente'); //Comprueba si es gerente

//Escapa texto para mostrar en HTML
function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$tituloPagina = 'Recompensas'; //Titulo de la pagina
$rutaPanelGerente = RUTA_APP.'includes/vistas/paneles/gerente.php'; //Ruta al panel gerente
$urlVolverCarta = RUTA_APP.'includes/vistas/productos/listarProductos.php'; //Ruta para volver a carta

if ($esGerente) { //Vista de gestion para gerente
    $recompensas = Recompensa::listar(); //Lista todas las recompensas

    $tablaRecompensas = '
        <table>
            <tr>
                <th>Producto</th>
                <th>BistroCoins</th>
                <th>Acciones</th>
            </tr>'; //Inicio de tabla

    foreach ($recompensas as $r) { //Recorre recompensas
        $id = (int)($r['id'] ?? 0); //Id de recompensa
        $idProducto = (int)($r['id_producto'] ?? 0); //Id de producto
        $bistroCoins = h((string)($r['bistroCoins'] ?? '0')); //Coste seguro
        $nombreProducto = h(Producto::nombre($idProducto)); //Nombre del producto seguro

        $urlVer = 'visualizarRecompensa.php?id='.urlencode((string)$id); //URL ver
        $urlEditar = 'actualizarRecompensa.php?id='.urlencode((string)$id); //URL editar
        $urlBorrar = 'borrarRecompensa.php?id='.urlencode((string)$id); //URL borrar

        $producto = "<a href='{$urlVer}' class='link-usuario'>{$nombreProducto}</a>"; //Enlace al detalle
        $acciones = "
            <a href='{$urlVer}' class='link-usuario'>Ver</a>
            |
            <a href='{$urlEditar}' class='link-usuario'>Editar</a>
            |
            <a href='{$urlBorrar}' class='link-usuario'>Borrar</a>
        "; //Acciones CRUD

        $tablaRecompensas .= "
            <tr>
                <td>{$producto}</td>
                <td>{$bistroCoins} BC</td>
                <td>{$acciones}</td>
            </tr>
        "; //Añade fila
    }
    $tablaRecompensas .= '</table>'; //Cierra tabla

    $contenidoPrincipal = <<<EOS
<div class="contenedor-gestion">
    <div class="header-admin">
        <h2 class="seccion-titulo">Gestión de Recompensas</h2>
    </div>

    $tablaRecompensas
    <div class="buttons-estandar">
        <a href="$rutaPanelGerente" class="button-estandar">Volver al Panel</a>
        <a href="crearRecompensa.php" class="button-estandar">Crear recompensa</a>
    </div>
</div>
EOS; //HTML para gerente
} else { //Vista para cliente
    $recompensasDisponibles = Recompensa::listarConProducto(true); //Lista recompensas disponibles
    $usuarioSesion = Usuario::buscaUsuario((string)($_SESSION['user'] ?? '')); //Busca usuario de sesion
    $bistroCoinsCliente = $usuarioSesion ? (int)$usuarioSesion->getBistroCoins() : 0; //Coins del cliente
    $tablaRecompensas = '<p class="ofertas-texto-centrado">No hay recompensas disponibles actualmente.</p>'; //Texto por defecto

    if (!empty($recompensasDisponibles)) { //Si hay recompensas
        $filas = ''; //Filas de tabla
        foreach ($recompensasDisponibles as $r) { //Recorre recompensas
            $nombre = h((string)($r['nombre_producto'] ?? '')); //Nombre seguro
            $descripcion = h((string)($r['descripcion_producto'] ?? '')); //Descripcion segura
            $coins = (int)($r['bistroCoins'] ?? 0); //Coste en coins
            $estado = $bistroCoinsCliente >= $coins ? 'Aplicable' : 'No aplicable'; //Indica si puede usarla

            $filas .= "
            <tr>
                <td>{$nombre}</td>
                <td>{$descripcion}</td>
                <td>{$coins} BC</td>
                <td>{$estado}</td>
            </tr>"; //Añade fila
        }

        $tablaRecompensas = "
        <table class='tabla-carta-centro'>
            <tr>
                <th>Producto</th>
                <th>Descripción</th>
                <th>Coste</th>
                <th>Estado</th>
            </tr>
            {$filas}
        </table>"; //Tabla de recompensas
    }

    $contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Recompensas disponibles</h1>
</div>
$tablaRecompensas
<div class="buttons-estandar">
    <a href="$urlVolverCarta" class="button-estandar">Volver a la carta</a>
</div>
EOS; //HTML para cliente
}

require __DIR__.'/../plantillas/plantilla.php'; //Carga plantilla
