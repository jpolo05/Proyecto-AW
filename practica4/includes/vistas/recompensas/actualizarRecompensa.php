<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Producto;
use es\ucm\fdi\aw\usuarios\Recompensa;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$id = (int)($_GET['id'] ?? 0);
$recompensa = $id > 0 ? Recompensa::buscaPorId($id) : null;

if (!$recompensa) {
    header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg=Recompensa+no+encontrada');
    exit;
}

$error = '';
$csrfToken = Auth::getCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF invalido.';
    }

    $idProducto = (int)($_POST['id_producto'] ?? 0);
    $bistroCoins = (int)($_POST['bistroCoins'] ?? 0);

    if ($error === '' && ($idProducto <= 0 || $bistroCoins <= 0)) {
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Recompensa::actualizar($id, $idProducto, $bistroCoins);
        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg=Recompensa+actualizada');
            exit;
        }

        $error = 'No se pudo actualizar la recompensa.';
    }
}

$productos = Producto::listarNombres();
$tituloPagina = 'Actualizar recompensa';
$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';
$action = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/actualizarRecompensa.php?id='.urlencode((string)$id), ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php', ENT_QUOTES, 'UTF-8');

$idProductoActual = (int)($recompensa['id_producto'] ?? 0);
$bistroCoinsActual = (int)($recompensa['bistroCoins'] ?? 0);

$opcionesProductos = '<option value="0">Selecciona un producto</option>';
