<?php
use es\ucm\fdi\aw\usuarios\Categoria;
use es\ucm\fdi\aw\usuarios\Producto;

require_once __DIR__.'/../../config.php';

$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente');
$msg = $_GET['msg'] ?? '';

if ($esGerente) {
    $prods = Producto::listar();
    $tituloPagina = 'Listado productos';

    $tablaProductos = '
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripcion</th>
                <th>Categoria</th>
                <th>Precio base</th>
                <th>IVA (%)</th>
                <th>Precio final</th>
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
        $precioFinal = number_format(((float)($p['precio_base'] ?? 0)) * (1 + ($iva / 100)), 2, '.', '');
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
        $urlVisualizar = RUTA_APP."includes/vistas/productos/visualizarProductos.php?id={$id}";

        $tablaProductos .= "
        <tr>
            <td>{$id}</td>
            <td>{$nombre}</td>
            <td>{$descripcion}</td>
            <td>{$categoria}</td>
            <td>{$precioBase}</td>
            <td>{$iva}</td>
            <td>{$precioFinal}</td>
            <td>{$disponible}</td>
            <td>{$ofertado}</td>
            <td>{$imgHtml}</td>
            <td>
                <a href='{$urlVisualizar}' class='button-estandar'>Visualizar</a>
                <a href='{$urlEditar}' class='button-estandar'>Actualizar</a>
                <a href='{$urlBorrar}' class='button-estandar'>Borrar</a>
            </td>
        </tr>";
    }
    $tablaProductos .= '</table>';

    $urlCrear = RUTA_APP.'includes/vistas/productos/crearProductos.php';
    $mensajeHtml = $msg !== '' ? '<p><strong>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';

    $contenidoPrincipal = <<<EOS
        <h2>Productos</h2>
        $mensajeHtml
        <p><a href="$urlCrear" class="button-estandar">Crear producto</a></p>
        $tablaProductos
    EOS;
} else {
    $idCategoria = (int)($_GET['id_categoria'] ?? 0);

    if ($idCategoria <= 0) {
        $cats = Categoria::listar();
        $tituloPagina = 'Carta';

        $tablaCategorias = '
            <table>
                <tr>
                    <th>Categoria</th>
                    <th>Descripcion</th>
                    <th>Accion</th>
                </tr>';

        foreach ($cats as $c) {
            $idCat = (int)($c['id'] ?? 0);
            $nombreCat = htmlspecialchars($c['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
            $descripcionCat = htmlspecialchars($c['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
            $urlCategoria = RUTA_APP.'includes/vistas/productos/listarProductos.php?id_categoria='.$idCat;

            $tablaCategorias .= '
            <tr>
                <td>'.$nombreCat.'</td>
                <td>'.$descripcionCat.'</td>
                <td><a href="'.$urlCategoria.'" class="button-estandar">Ver productos</a></td>
            </tr>';
        }
        $tablaCategorias .= '</table>';
        $mensajeHtml = $msg !== '' ? '<p><strong>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';

        $contenidoPrincipal = <<<EOS
            <h2>Carta</h2>
            $mensajeHtml
            $tablaCategorias
        EOS;
    } else {
        $categoria = Categoria::buscaPorId($idCategoria);
        if (!$categoria) {
            header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg=Categoria+no+encontrada');
            exit;
        }

        $prods = Producto::listarPorCategoria($idCategoria, true);
        $nombreCategoria = htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
        $tituloPagina = 'Carta - '.$nombreCategoria;

        $tablaCarta = '
            <table>
                <tr>
                    <th>Nombre</th>
                    <th>Descripcion</th>
                    <th>Imagen</th>
                    <th>Precio</th>
                    <th>Accion</th>
                </tr>';

        foreach ($prods as $p) {
            $id = (int)$p['id'];
            $nombre = htmlspecialchars($p['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
            $descripcion = htmlspecialchars($p['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
            $imagenRaw = $p['imagen'] ?? '';
            $imagen = htmlspecialchars($imagenRaw, ENT_QUOTES, 'UTF-8');
            $precioBase = (float)($p['precio_base'] ?? 0);
            $iva = (int)($p['iva'] ?? 0);
            $precioFinal = $precioBase + ($precioBase * $iva / 100);
            $urlVisualizar = RUTA_APP."includes/vistas/productos/visualizarProductos.php?id={$id}&id_categoria={$idCategoria}";
            $imgHtml = '-';
            if ($imagenRaw !== '') {
                $src = preg_match('/^https?:\/\//', $imagenRaw) ? $imagen : RUTA_APP.ltrim($imagenRaw, '/');
                $imgHtml = "<img src='{$src}' alt='Imagen producto' width='70' height='70'>";
            }

            $tablaCarta .= '
            <tr>
                <td>'.$nombre.'</td>
                <td>'.$descripcion.'</td>
                <td>'.$imgHtml.'</td>
                <td>'.number_format($precioFinal, 2, '.', '').' EUR</td>
                <td><a href="'.$urlVisualizar.'" class="button-estandar">Ver</a></td>
            </tr>';
        }
        $tablaCarta .= '</table>';

        $urlVolverCategorias = RUTA_APP.'includes/vistas/productos/listarProductos.php';
        $urlCrearPedido = RUTA_APP.'includes/vistas/pedidos/crearPedido.php';
        $mensajeHtml = $msg !== '' ? '<p><strong>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';
        $contenidoPrincipal = <<<EOS
            <h2>Carta - $nombreCategoria</h2>
            $mensajeHtml
            <p>
                <a href="$urlVolverCategorias" class="button-estandar">Volver a categorias</a>
                <a href="$urlCrearPedido" class="button-estandar">Crear pedido</a>
            </p>
            $tablaCarta
        EOS;
    }
}

require __DIR__.'/../plantillas/plantilla.php';
