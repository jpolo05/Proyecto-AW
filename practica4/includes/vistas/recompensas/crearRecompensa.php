<?php
use es\ucm\fdi\aw\usuarios\Auth;
use es\ucm\fdi\aw\usuarios\Producto;
use es\ucm\fdi\aw\usuarios\Recompensa;

require_once __DIR__.'/../../config.php';
Auth::verificarAcceso('Gerente');

$error = '';
$csrfToken = Auth::getCsrfToken();
$idProducto = (int)($_POST['id_producto'] ?? 0);
$bistroCoins = (int)($_POST['bistroCoins'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validaCsrfToken($_POST['csrfToken'] ?? null)) {
        $error = 'Token CSRF inválido.';
    }

    if ($error === '' && ($idProducto <= 0 || $bistroCoins <= 0)) {
        $error = 'Revisa los datos del formulario.';
    } elseif ($error === '') {
        $ok = Recompensa::crear($idProducto, $bistroCoins);
        if ($ok) {
            header('Location: '.RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php?msg=Recompensa+creada');
            exit;
        }

        $error = 'No se pudo crear la recompensa.';
    }
}

$productos = Producto::listarNombres();
$tituloPagina = 'Crear recompensa';
$errorHtml = $error !== '' ? '<p><strong>'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</strong></p>' : '';
$action = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/crearRecompensa.php', ENT_QUOTES, 'UTF-8');
$urlCancelar = htmlspecialchars(RUTA_APP.'includes/vistas/recompensas/listarRecompensas.php', ENT_QUOTES, 'UTF-8');

$opcionesProductos = '<option value="0">Selecciona un producto</option>';
foreach ($productos as $producto) {
    $id = (int)($producto['id'] ?? 0);
    $nombre = htmlspecialchars((string)($producto['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
    $selected = $id === $idProducto ? 'selected' : '';
    $opcionesProductos .= "<option value=\"{$id}\" {$selected}>{$nombre}</option>";
}

$bistroCoinsSafe = htmlspecialchars((string)$bistroCoins, ENT_QUOTES, 'UTF-8');

$contenidoPrincipal = <<<EOS
<div class="seccion-titulo">
    <h1>Crear recompensa</h1>
</div>

<form method="POST" action="$action" class="form-estandar">
    <div class="info-categoria">
        $errorHtml

        <input type="hidden" name="csrfToken" value="$csrfToken">

        <div class="campo-form">
            <label for="id_producto"><strong>Producto:</strong></label>
            <select id="id_producto" name="id_producto" required>
                $opcionesProductos
            </select>
        </div>

        <div class="campo-form">
            <label for="bistroCoins"><strong>BistroCoins:</strong></label>
            <input type="number" id="bistroCoins" name="bistroCoins" min="1" step="1" value="$bistroCoinsSafe" required>
        </div>
    </div>

    <div class="buttons-estandar">
        <button type="submit" class="button-estandar">Guardar</button>
        <a href="$urlCancelar" class="button-estandar">Cancelar</a>
    </div>
</form>
EOS;

require __DIR__.'/../plantillas/plantilla.php';
