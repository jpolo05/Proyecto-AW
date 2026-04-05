<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Oferta;
use es\ucm\fdi\aw\usuarios\Producto;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$oferta = $id > 0 ? Oferta::buscaPorId($id) : null;

if (!$oferta) {
    header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+no+encontrada');
    exit;
}

$productos = Producto::listarNombres();
$error = '';
$csrfToken = Auth::getCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF invalido.';
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $comienzo = trim($_POST['comienzo'] ?? '');
    $fin = trim($_POST['fin'] ?? '');
    $productosElegidos = $_POST['productos'] ?? [];
    $cantidadesElegidas = $_POST['cantidades'] ?? [];
    $descuento = (float)($_POST['descuento'] ?? 0.00);

    if ($error === '' && ($nombre === '' || $descripcion === '')) {
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Oferta::actualizar(
            $id,
            $nombre,
            $descripcion,
            $comienzo !== '' ? $comienzo : null,
            $fin !== '' ? $fin : null,
            $descuento,
            $productosElegidos,
            $cantidadesElegidas
        );

        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/ofertas/listarOfertas.php?msg=Oferta+actualizada');
            exit;
        }

        $error = 'No se pudo actualizar la oferta.';
    }
}
