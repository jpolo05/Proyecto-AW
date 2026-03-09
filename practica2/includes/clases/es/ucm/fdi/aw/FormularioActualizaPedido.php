<?php
namespace es\ucm\fdi\aw;

class FormularioActualizaPedido extends Formulario
{
    private $numeroPedido;
    private $nuevoEstado;

    public function __construct($numeroPedido, $nuevoEstado)
    {
        parent::__construct('formActualizaPedido', [
            'urlRedireccion' => RUTA_APP . 'includes/vistas/paneles/cocinero.php'
        ]);
        $this->numeroPedido = $numeroPedido;
        $this->nuevoEstado = ($nuevoEstado) ? $nuevoEstado : null;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $txt = ($this->nuevoEstado === 'Cocinando') ? 'Tomar Pedido' : 'Finalizar Pedido';
        return <<<EOF
        <input type="hidden" name="numeroPedido" value="{$this->numeroPedido}" />
        <input type="hidden" name="nuevoEstado" value="{$this->nuevoEstado}" />
        <button type="submit" class="button-estandar">$txt</button>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $num = $datos['numeroPedido'] ?? null;

        if (!$num) {
            $this->errores[] = "El número de pedido no es válido.";
            return;
        }

        $exito = Pedido::actualizarEstado($num, $this->nuevoEstado);

        if (!$exito) {
            $this->errores[] = "Error al actualizar el estado del pedido en la base de datos.";
        }
    }
}