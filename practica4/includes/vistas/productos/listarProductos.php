<?php
use es\ucm\fdi\aw\usuarios\Categoria; //Usa la clase Categoria
use es\ucm\fdi\aw\usuarios\Producto; //Usa la clase Producto

require_once __DIR__.'/../../config.php'; //Carga config.php (1 sola vez)

$esGerente = (($_SESSION['rol'] ?? '') === 'Gerente'); //Comprueba si es Gerente
$msg = $_GET['msg'] ?? ''; //Recoge mensaje de la URL

if ($esGerente) { //Si es gerente lista todos los productos
    $prods = Producto::listar(); //Llama a listar (devuelve un array)
    $tituloPagina = 'Listado de productos';
    $rutaPanelGerente = RUTA_APP.'includes/vistas/paneles/gerente.php'; //URL para volver al panel

    //Empieza a crear la tabla HTML
    $tablaProductos = '
        <table class="tabla-carta-centro">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Precio base</th>
                <th>IVA (%)</th>
                <th>Precio final</th>
                <th>Disponible</th>
                <th>Ofertado</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>';

    foreach ($prods as $p) { //Recorre cada producto obtenido de la BD
        //Recoge datos
        $id = (int)$p['id'];
        $nombre = htmlspecialchars($p['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars($p['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
        $categoria = htmlspecialchars($p['categoria'] ?? '-', ENT_QUOTES, 'UTF-8');
        $precioBase = number_format((float)($p['precio_base'] ?? 0), 2, '.', '');
        $iva = (int)($p['iva'] ?? 0);
        $precioFinal = number_format(((float)($p['precio_base'] ?? 0)) * (1 + ($iva / 100)), 2, '.', ''); //Calcula y formatea precio con IVA
        $disponible = ((int)($p['disponible'] ?? 0) === 1) ? 'Si' : 'No'; //Convierte disponible a texto
        $ofertado = ((int)($p['ofertado'] ?? 0) === 1) ? 'Si' : 'No'; //Convierte ofertado a texto
        $imagenRaw = $p['imagen'] ?? ''; //Ruta original de la imagen
        $imagen = htmlspecialchars($imagenRaw, ENT_QUOTES, 'UTF-8'); //Prepara ruta para HTML

        $imgHtml = '-'; //Por defecto no muestra imagen
        if ($imagenRaw !== '') { //Si hay imagen
            $src = preg_match('/^https?:\/\//', $imagenRaw) ? $imagen : RUTA_APP.ltrim($imagenRaw, '/'); //Usa URL externa o ruta local
            $imgHtml = "<img src='{$src}' alt='Imagen producto' width='60' height='60'>"; //Crea el HTML de la imagen
        }

        //Prepara URLs
        $urlEditar = RUTA_APP."includes/vistas/productos/actualizarProductos.php?id={$id}";
        $urlBorrar = RUTA_APP."includes/vistas/productos/borrarProductos.php?id={$id}";
        $urlVisualizar = RUTA_APP."includes/vistas/productos/visualizarProductos.php?id={$id}";

        //Añade 1 fila a la tabla
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
    $tablaProductos .= '</table>'; //Cierra la tabla HTML

    $urlCrear = RUTA_APP.'includes/vistas/productos/crearProductos.php'; //URL para crear producto
    $mensajeHtml = $msg !== '' ? '<p><strong>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</strong></p>' : ''; //Prepara mensaje si llega por URL

    //HTML contenido principal (que vera el usuario)
    $contenidoPrincipal = <<<EOS
        <div class="seccion-titulo">
            <h1>Productos</h1>
        </div>
        $mensajeHtml
        $tablaProductos
        <div class="buttons-estandar">
            <a href="$rutaPanelGerente" class="button-estandar">Volver al Panel</a>
            <a href="$urlCrear" class="button-estandar">Crear producto</a>
        </div>
    EOS;
} else { //Si no es gerente muestra la carta
    $idCategoria = (int)($_GET['id_categoria'] ?? 0); //Recoge categoria desde la URL

    if ($idCategoria <= 0) { //Si no se ha elegido categoria muestra categorias
        $cats = Categoria::listar(); //Lista categorias
        $tituloPagina = 'Carta';
        $urlVerOfertas = RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?solo=activas&origen=carta'; //URL para ver ofertas activas desde carta
        $urlVerRecompensas = RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?origen=carta'; //URL para ver recompensas desde carta

        //Empieza a crear la tabla HTML
        $tablaCategorias = '
            <table class="tabla-carta-centro">
                <tr>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Acción</th>
                </tr>';

        foreach ($cats as $c) { //Recorre categorias
            //Recoge datos
            $idCat = (int)($c['id'] ?? 0);
            $nombreCat = htmlspecialchars($c['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
            $descripcionCat = htmlspecialchars($c['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
            $urlCategoria = RUTA_APP.'includes/vistas/productos/listarProductos.php?id_categoria='.$idCat; //URL para ver productos de esa categoria

            //Añade 1 fila a la tabla
            $tablaCategorias .= '
            <tr>
                <td>'.$nombreCat.'</td>
                <td>'.$descripcionCat.'</td>
                <td><a href="'.$urlCategoria.'" class="button-estandar">Ver productos</a></td>
            </tr>';
        }
        $tablaCategorias .= '</table>'; //Cierra la tabla HTML
        $mensajeHtml = $msg !== '' ? '<p><strong>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</strong></p>' : ''; //Prepara mensaje si llega por URL

        //HTML contenido principal (que vera el usuario)
        $contenidoPrincipal = <<<EOS
        <div class="seccion-titulo">
            <h2>Carta</h2>
        </div>
            <div class="buttons-estandar">
                <a href="$urlVerOfertas" class="button-estandar">Ver ofertas</a>
                <a href="$urlVerRecompensas" class="button-estandar">Ver recompensas</a>
            </div>
            $mensajeHtml
            $tablaCategorias
        EOS;
    } else {
        $categoria = Categoria::buscaPorId($idCategoria); //Busca la categoria en la base de datos
        if (!$categoria) { //Si no encuentra categoria
            header('Location: '.RUTA_APP.'includes/vistas/productos/listarProductos.php?msg='.rawurlencode('Categoría no encontrada'));
            exit;
        }

        $prods = Producto::listarPorCategoria($idCategoria, true); //Lista productos ofertados de la categoria
        $nombreCategoria = htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); //Nombre de categoria para mostrar
        $tituloPagina = 'Carta - '.$nombreCategoria; //Titulo con la categoria actual

        //Empieza a crear la tabla HTML
        $tablaCarta = '
            <table class="tabla-carta-centro">
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Imagen</th>
                    <th>Precio</th>
                    <th>Acción</th>
                </tr>';

        foreach ($prods as $p) { //Recorre productos de la categoria
            //Recoge datos
            $id = (int)$p['id'];
            $nombre = htmlspecialchars($p['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
            $descripcion = htmlspecialchars($p['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
            $imagenRaw = $p['imagen'] ?? ''; //Ruta original de la imagen
            $imagen = htmlspecialchars($imagenRaw, ENT_QUOTES, 'UTF-8'); //Prepara ruta para HTML
            $precioBase = (float)($p['precio_base'] ?? 0);
            $iva = (int)($p['iva'] ?? 0);
            $precioFinal = $precioBase + ($precioBase * $iva / 100); //Calcula precio con IVA
            $urlVisualizar = RUTA_APP."includes/vistas/productos/visualizarProductos.php?id={$id}&id_categoria={$idCategoria}"; //Mantiene la categoria para poder volver
            $imgHtml = '-'; //Por defecto no muestra imagen
            if ($imagenRaw !== '') { //Si hay imagen
                $src = preg_match('/^https?:\/\//', $imagenRaw) ? $imagen : RUTA_APP.ltrim($imagenRaw, '/'); //Usa URL externa o ruta local
                $imgHtml = "<img src='{$src}' alt='Imagen producto' class='img-producto-lista'>"; //Crea el HTML de la imagen
            }

            //Añade 1 fila a la tabla
            $tablaCarta .= '
            <tr>
                <td>'.$nombre.'</td>
                <td>'.$descripcion.'</td>
                <td>'.$imgHtml.'</td>
                <td>'.number_format($precioFinal, 2, '.', '').' EUR</td>
                <td><a href="'.$urlVisualizar.'" class="button-estandar">Ver</a></td>
            </tr>';
        }
        $tablaCarta .= '</table>'; //Cierra la tabla HTML

        //Prepara URLs
        $urlVolverCategorias = RUTA_APP.'includes/vistas/productos/listarProductos.php'; //URL para volver a categorias
        $urlCrearPedido = RUTA_APP.'includes/vistas/pedidos/crearPedido.php'; //URL para crear pedido
        $urlVerOfertas = RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?solo=activas&origen=carta'; //URL para ver ofertas activas desde carta
        $urlVerRecompensas = RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?origen=carta'; //URL para ver recompensas desde carta
        $mensajeHtml = $msg !== '' ? '<p><strong>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</strong></p>' : ''; //Prepara mensaje si llega por URL
        //HTML contenido principal (que vera el usuario)
        $contenidoPrincipal = <<<EOS
            <div class="seccion-titulo">
                <h2>Carta - $nombreCategoria</h2>
            </div>
            <div class="buttons-estandar">
                <a href="$urlVerOfertas" class="button-estandar">Ver ofertas</a>
                <a href="$urlVerRecompensas" class="button-estandar">Ver recompensas</a>
            </div>
            $mensajeHtml
            $tablaCarta

            <div class="buttons-estandar">
                <a href="$urlVolverCategorias" class="button-estandar">Volver</a>
                <a href="$urlCrearPedido" class="button-estandar">Crear pedido</a>
            </div>
        EOS;
    }
}

require __DIR__.'/../plantillas/plantilla.php'; //Carga la plantilla comun
