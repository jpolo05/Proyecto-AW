<?php
namespace es\ucm\fdi\aw;

class FormularioBorrarPedido extends Formulario
{
    private $numeroPedido;

    public function __construct($numeroPedido)
    {
        parent::__construct('formBorrarPedido', ['urlRedireccion' => 'pedidosUsuario.php']);
        $this->numeroPedido = $numeroPedido;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores, 'error');

        return <<<EOF
        $htmlErroresGlobales
        <input type="hidden" name="numeroPedido" value="{$this->numeroPedido}"/>
        <p>¿Estas seguro de que quieres eliminar este pedido?</p>
        <div>
            <button type="submit" name="borrar" class="button-estandar">Si</button>
            <a href="pedidosUsuario.php"><button type="button" class="button-estandar">No</button></a>
        </div>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $num = $datos['numeroPedido'] ?? 0;
        
        if (!$num) {
            $this->errores[] = 'Número de pedido no válido.';
            return;
        }

        $exito = Pedido::borrar($num);
        if (!$exito) {
            $this->errores[] = 'No se pudo borrar el pedido de la base de datos.';
        }
    }
}
