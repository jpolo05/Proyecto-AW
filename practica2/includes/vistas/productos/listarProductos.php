<?php
use es\ucm\fdi\aw\Producto;

require_once __DIR__.'/../../config.php';

$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente');
$msg = $_GET['msg'] ?? '';

if ($esGerente) {
    $prods = Producto::listar();
    $tituloPagina = 'Listado productos';

    $tablaProductos = '
        <table border="1" cellpadding="6">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripcion</th>
                <th>Categoria</th>
                <th>Precio base</th>
                <th>IVA (%)</th>
                <th>Disponible</th>
                <th>Ofertado</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>';

    foreach ($prods as $p) {
        $id = (int)$p['id'];
        $nombre = htmlspecialchars($p['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars($p['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
        $categoria = htmlspecialchars($p['categoria'] ?? '-', ENT_QUOTES, 'UTF-8');
        $precioBase = number_format((float)($p['precio_base'] ?? 0), 2, '.', '');
        $iva = (int)($p['iva'] ?? 0);
        $disponible = ((int)($p['disponible'] ?? 0) === 1) ? 'Si' : 'No';
        $ofertado = ((int)($p['ofertado'] ?? 0) === 1) ? 'Si' : 'No';
        $imagenRaw = $p['imagen'] ?? '';
        $imagen = htmlspecialchars($imagenRaw, ENT_QUOTES, 'UTF-8');

        $imgHtml = '-';
        if ($imagenRaw !== '') {
            $src = preg_match('/^https?:\/\//', $imagenRaw) ? $imagen : RUTA_APP.ltrim($imagenRaw, '/');
            $imgHtml = "<img src='{$src}' alt='Imagen producto' width='60' height='60'>";
        }

        $urlEditar = RUTA_APP."includes/vistas/productos/actualizarProductos.php?id={$id}";
        $urlBorrar = RUTA_APP."includes/vistas/productos/borrarProductos.php?id={$id}";

        $tablaProductos .= "
        <tr>
            <td>{$id}</td>
            <td>{$nombre}</td>
            <td>{$descripcion}</td>
            <td>{$categoria}</td>
            <td>{$precioBase}</td>
            <td>{$iva}</td>
            <td>{$disponible}</td>
            <td>{$ofertado}</td>
            <td>{$imgHtml}</td>
            <td>
                <a href='{$urlEditar}'><button>Actualizar</button></a>
                <a href='{$urlBorrar}'><button>Borrar</button></a>
            </td>
        </tr>";
    }
    $tablaProductos .= '</table>';

    $urlCrear = RUTA_APP.'includes/vistas/productos/crearProductos.php';
    $mensajeHtml = $msg !== '' ? '<p><strong>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';

    $contenidoPrincipal = <<<EOS
        <h1>Productos</h1>
        $mensajeHtml
        <p><a href="$urlCrear"><button>Crear producto</button></a></p>
        $tablaProductos
    EOS;
} else {
    $prods = Producto::listar(true);
    $tituloPagina = 'Carta';

    $tablaCarta = '
        <table border="1" cellpadding="6">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripcion</th>
                <th>Precio base</th>
                <th>IVA (%)</th>
                <th>Precio final</th>
            </tr>';

    foreach ($prods as $p) {
        $id = (int)$p['id'];
        $nombre = htmlspecialchars($p['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars($p['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
        $precioBase = (float)($p['precio_base'] ?? 0);
        $iva = (int)($p['iva'] ?? 0);
        $precioFinal = $precioBase + ($precioBase * $iva / 100);

        $tablaCarta .= '
        <tr>
            <td>'.$id.'</td>
            <td>'.$nombre.'</td>
            <td>'.$descripcion.'</td>
            <td>'.number_format($precioBase, 2, '.', '').'</td>
            <td>'.$iva.'</td>
            <td>'.number_format($precioFinal, 2, '.', '').'</td>
        </tr>';
    }
    $tablaCarta .= '</table>';

    $contenidoPrincipal = <<<EOS
        <h1>Carta</h1>
        $tablaCarta
    EOS;
}

require __DIR__.'/../plantillas/plantilla.php';
