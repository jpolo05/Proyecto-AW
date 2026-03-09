<?php
namespace es\ucm\fdi\aw;

class FormularioActualizaLineaPedido extends Formulario
{
    private $numPedido;
    private $idProd;

    public function __construct($numPedido, $idProd)
    {
        // Redireccionamos a la misma página para ver el cambio reflejado
        parent::__construct('formActualizaLinea_' . $numPedido . '_' . $idProd, [
            'urlRedireccion' => RUTA_APP . "includes/vistas/pedidos/verPedido.php?numeroPedido=$numPedido&accion=cocinar"
        ]);
        $this->numPedido = $numPedido;
        $this->idProd = $idProd;
    }

    protected function generaCamposFormulario(&$datos)
    {
        return <<<EOF
        <input type="hidden" name="numPedido" value="{$this->numPedido}" />
        <input type="hidden" name="idProd" value="{$this->idProd}" />
        <button type="submit" class="button-estandar">Listo</button>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        Pedido::actualizarEstadoLinea($this->numPedido, $this->idProd);
    }
}