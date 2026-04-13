<?php
namespace es\ucm\fdi\aw\usuarios;
use es\ucm\fdi\aw\Formulario;

class FormularioActualizaLineaPedido extends Formulario
{
    private $numPedido;
    private $idProd;

    public function __construct($numPedido, $idProd)
    {
        parent::__construct('formActualizaLinea_' . $numPedido . '_' . $idProd, [
            'urlRedireccion' => RUTA_APP . "includes/vistas/pedidos/visualizarPedido.php?numeroPedido=$numPedido&accion=cocinar"
        ]);
        $this->numPedido = (int)$numPedido;
        $this->idProd = (int)$idProd;
    }

    protected function generaCamposFormulario(&$datos)
    {
        return <<<EOF
        <input type="hidden" name="numPedido" value="{$this->numPedido}">
        <input type="hidden" name="idProd" value="{$this->idProd}">
        <button type="submit" class="button-estandar">Listo</button>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $rol = $_SESSION['rol'] ?? '';

        if (!in_array($rol, ['Cocinero', 'Gerente'], true)) {
            $this->errores[] = "No tienes permisos para actualizar lineas de pedido.";
            return;
        }

        $ok = Pedido::actualizarEstadoLinea($this->numPedido, $this->idProd);
        if (!$ok) {
            $this->errores[] = "No se pudo actualizar la linea del pedido.";
        }
    }
}
